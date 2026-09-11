(() => {
  'use strict';
  const S=window.SC, boot=window.SC_BOOT;
  S.stopAuto=()=>{S.autoRemaining=0;S.updateUI();};
  S.toggleInputs=disabled=>[S.$('#betMinus'),S.$('#betPlus'),S.els.buyBonus,S.els.buySuper].forEach(b=>{if(b)b.disabled=disabled;});
  S.fillPaytable=()=>{S.$('#paytableList').innerHTML=S.PAY_KEYS.map(k=>{const x=S.SYMBOLS[k],p=x.pay;return `<div class="pay-row"><div class="pay-icon">${S.svgFor(k)}</div><div><strong>${x.name}</strong><small>5: ${(p[5]*S.PAYOUT_SCALE).toFixed(2)}× • 8: ${(p[8]*S.PAYOUT_SCALE).toFixed(2)}× • 12: ${(p[12]*S.PAYOUT_SCALE).toFixed(2)}× • 15+: ${(p[15]*S.PAYOUT_SCALE).toFixed(2)}× ставки</small></div></div>`;}).join('')+`<div class="pay-row"><div class="pay-icon">${S.svgFor('scatter')}</div><div><strong>Бонусный автомат</strong><small>3 = 8 • 4 = 10 • 5+ = 12 фриспинов</small></div></div>`;};
  S.markServerWins=clusters=>{for(const c of clusters)for(const i of c.cells)S.els.grid.children[i]?.classList.add('win');};
  S.markServerVanish=clusters=>{for(const c of clusters)for(const i of c.cells){const e=S.els.grid.children[i];if(e){e.classList.remove('win');e.classList.add('vanish');}}};
  S.showCascadeAmount=async(step)=>{const el=document.createElement('div');el.className='big-win win-counter';el.innerHTML=`${S.rub(step.win)}<small>ИТОГ КАСКАДА ${step.cascade}</small>`;S.els.overlay.replaceChildren(el);if(step.win>=S.bet()*12)S.confetti(Math.min(80,18+Math.floor(step.win/S.bet()*2)));await S.delay(step.win>=S.bet()*10?820:540);S.els.overlay.innerHTML='';};
  S.decorateServerChanges=changes=>{for(const ch of changes||[]){const cell=S.els.grid.children[ch.pos];if(!cell)continue;cell.classList.add(ch.from?'mult-up':'mult-new');S.particlesFromCell?.(ch.pos,ch.from?13:9);}};
  S.showServerCombo=cascade=>{const e=S.$('#comboPill');if(!e)return;e.classList.remove('show');void e.offsetWidth;e.innerHTML=`КОМБО <small>• каскад ${cascade}</small>`;e.classList.add('show');};
  S.requestSpin=async(mode='normal')=>{
    if(S.busy)return;
    if(S.state.freeSpins>0&&mode!=='normal'){S.setMessage('СНАЧАЛА ЗАВЕРШИТЕ ФРИСПИНЫ');S.sound('error');return;}
    S.busy=true;S.toggleInputs(true);S.els.spin.classList.add('spinning');S.setMessage('СЕРВЕР РАССЧИТЫВАЕТ ВРАЩЕНИЕ…');
    try{
      const res=await fetch('api/game/sweet-cascade/spin.php',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-Token':boot.csrf},credentials:'same-origin',body:JSON.stringify({bet:S.bet(),mode})});
      const data=await res.json().catch(()=>({ok:false,error:'Некорректный ответ сервера.'}));
      if(!res.ok||!data.ok)throw new Error(data.error||'Не удалось выполнить вращение.');
      S.state.credits=Number(data.balance_before)-Number(data.cost);S.state.lastWin=0;if(data.is_free_spin)S.state.freeSpins=Math.max(0,S.state.freeSpins-1);S.updateUI();
      S.multiplierMap=data.initial_multipliers||Array(S.CELL_COUNT).fill(0);
      if(data.storm_active){S.$('.board-frame')?.classList.add('powered');S.flash?.();S.toast?.('⚡ СЛАДКИЙ ШТОРМ АКТИВИРОВАН');}
      await S.animateRoll(data.initial_grid);
      let cumulative=0;
      for(const step of data.steps){
        cumulative+=Number(step.win);S.state.lastWin=cumulative;S.updateUI();S.els.cascade.textContent=`КАСКАД ${step.cascade}`;S.setMessage(`ИТОГ КАСКАДА +${S.rub(Number(step.win))}`);S.showServerCombo(step.cascade);S.markServerWins(step.clusters);S.sound('win',Math.min(step.cascade,7));
        if(step.cascade>=3){S.$('.board-frame')?.classList.add('shake');setTimeout(()=>S.$('.board-frame')?.classList.remove('shake'),380);}
        await S.showCascadeAmount(step);S.markServerVanish(step.clusters);await S.delay(330);S.grid=step.grid_after;S.multiplierMap=step.multipliers_after;S.renderGrid({animate:true});S.decorateServerChanges(step.multiplier_changes);if(step.burst?.length){S.flash?.();S.toast?.(step.cascade>=6?'🌈 МЕГА-БУРСТ: новые усиленные клетки!':'🍭 СЛАДКИЙ ВЗРЫВ: новые множители!');}await S.delay(650);
      }
      if(data.free_spins_awarded>0){S.sound('bonus');S.confetti(90);await S.showBanner(`${data.free_spins_awarded} ФРИСПИНОВ`,'БОНУС АКТИВИРОВАН');}
      S.state.credits=Number(data.balance_after);S.state.lastWin=Number(data.total_win);S.state.freeSpins=Number(data.free_spins);S.state.stormCharge=Number(data.storm_charge);S.grid=data.final_grid;S.multiplierMap=data.final_multipliers;S.renderGrid();S.$('.board-frame')?.classList.remove('powered');S.els.cascade.textContent='ГОТОВО';
      S.setMessage(data.total_win>0?`ВЫИГРЫШ ${S.rub(Number(data.total_win))}`:(S.state.freeSpins>0?'СЛЕДУЮЩИЙ ФРИСПИН':'ЕЩЁ РАЗ!'));S.updateUI();document.querySelectorAll('[data-site-balance]').forEach(e=>e.textContent=S.rub(S.state.credits));
      S.busy=false;S.toggleInputs(false);S.els.spin.classList.remove('spinning');
      if(S.state.freeSpins>0){await S.delay(950);if(!S.busy)S.requestSpin('normal');return;}
      if(S.autoRemaining>0){S.autoRemaining--;S.updateUI();if(S.autoRemaining>0){await S.delay(900);if(!S.busy)S.requestSpin('normal');}else S.stopAuto();}
    }catch(err){S.busy=false;S.toggleInputs(false);S.els.spin.classList.remove('spinning');S.stopAuto();S.sound('error');S.setMessage(err.message||'ОШИБКА СЕРВЕРА');}
  };
  S.activateFeature=superMode=>{const cost=S.bet()*(superMode?250:80);if(!confirm(`${superMode?'Супер-бонус':'Фриспины'} используют ${S.rub(cost)} виртуального баланса. Продолжить?`))return;S.requestSpin(superMode?'buy_super':'buy_bonus');};
  S.$('#betMinus').addEventListener('click',()=>{if(S.busy)return;S.state.betIndex=Math.max(0,S.state.betIndex-1);S.sound('click');S.updateUI();});
  S.$('#betPlus').addEventListener('click',()=>{if(S.busy)return;S.state.betIndex=Math.min(S.BET_LEVELS.length-1,S.state.betIndex+1);S.sound('click');S.updateUI();});
  S.els.spin.addEventListener('click',()=>{S.sound('click');S.requestSpin('normal');});S.els.buyBonus.addEventListener('click',()=>S.activateFeature(false));S.els.buySuper.addEventListener('click',()=>S.activateFeature(true));
  S.els.auto.addEventListener('click',()=>{S.sound('click');if(S.autoRemaining){S.stopAuto();return;}S.autoRemaining=20;S.updateUI();S.requestSpin('normal');});
  S.els.turbo.addEventListener('click',()=>{S.state.turbo=!S.state.turbo;S.sound('click');S.updateUI();});S.els.sound.addEventListener('click',()=>{S.state.sound=!S.state.sound;S.updateUI();if(S.state.sound)S.sound('click');});
  S.$('#resetBtn').addEventListener('click',()=>location.href='account.php');S.$('#paytableBtn').addEventListener('click',()=>{S.fillPaytable();S.els.modal.showModal();S.sound('click');});
  document.addEventListener('keydown',e=>{if(e.code==='Space'&&!e.repeat&&!S.els.modal.open){e.preventDefault();S.requestSpin('normal');}});
  S.grid=S.randomGrid();S.renderGrid({animate:true});S.updateUI();S.setMessage('УДАЧИ!');
})();
