// Demo-only client-side game flow. All credits are fictional and have no cash value.
(() => {
  'use strict'; const S=window.SC; S.demoOnly=true;
  S.toggleInputs=disabled=>[S.$('#betMinus'),S.$('#betPlus'),S.els.buyBonus,S.els.buySuper,S.$('#resetBtn')].forEach(b=>b.disabled=disabled);
  S.stopAuto=()=>{S.autoRemaining=0;S.updateUI();};
  S.spin=async({featureStart=false,superFeature=false}={})=>{
    if(S.busy)return;
    const isFree=S.state.freeSpins>0;
    if(!isFree&&!featureStart&&S.state.credits<S.bet()){S.setMessage('НЕДОСТАТОЧНО ВИРТУАЛЬНЫХ КРЕДИТОВ');S.sound('error');S.stopAuto();return;}
    S.busy=true;S.toggleInputs(true);S.els.spin.classList.add('spinning');
    if(!isFree&&!featureStart)S.state.credits-=S.bet();
    if(isFree)S.state.freeSpins--;
    S.state.totalSpins++;

    if(!isFree&&!featureStart)S.multiplierMap.fill(0);
    if(featureStart&&!superFeature)S.multiplierMap.fill(0);
    if(!isFree&&!featureStart&&S.applyStormIfReady)await S.applyStormIfReady();

    const target=S.randomGrid();
    if(featureStart){
      const n=superFeature?5:4,picks=[...Array(S.CELL_COUNT).keys()].sort(()=>Math.random()-.5).slice(0,n);
      picks.forEach(i=>target[i]='scatter');
      if(superFeature){S.multiplierMap.fill(0);if(S.seedMultipliers)S.seedMultipliers(7,true);else for(let k=0;k<7;k++)S.multiplierMap[Math.floor(Math.random()*S.CELL_COUNT)]=[3,5,10][Math.floor(Math.random()*3)];}
    }

    S.updateUI();
    if(S.animateRoll)await S.animateRoll(target);else{S.grid=target;S.renderGrid({animate:true});await S.delay(650);}
    S.setMessage(isFree?'БЕСПЛАТНОЕ ВРАЩЕНИЕ':'ПРОВЕРЯЕМ КЛАСТЕРЫ…');

    const total=await S.resolveCascades(),scatters=S.countScatter(),awarded=S.awardFreeSpins(scatters);
    if(awarded){S.setMessage(`БОНУС! +${awarded} ФРИСПИНОВ`);S.sound('bonus');S.confetti(90);S.flash?.();await S.showBanner(`${awarded} ФРИСПИНОВ`,'БОНУС АКТИВИРОВАН');}

    S.state.credits+=total;S.state.lastWin=total;
    if(!isFree&&!featureStart&&S.addStormCharge)S.addStormCharge(total,S.lastCascadeCount||0);
    if(S.stormActive&&S.finishStorm)S.finishStorm();
    if(total>0)S.setMessage(`ВЫИГРЫШ ${S.rub(total)}${S.state.freeSpins?` • ФРИСПИНОВ: ${S.state.freeSpins}`:''}`);
    else if(!awarded)S.setMessage(S.state.freeSpins?'СЛЕДУЮЩИЙ ФРИСПИН':'ЕЩЁ РАЗ!');
    if(total>=S.bet()*25)S.sound('big');
    S.els.cascade.textContent='ГОТОВО';S.updateUI();S.els.spin.classList.remove('spinning');S.busy=false;S.toggleInputs(false);

    if(S.state.freeSpins>0){await S.delay(900);if(!S.busy)S.spin();return;}
    if(!S.state.freeSpins&&isFree){S.multiplierMap.fill(0);S.renderGrid();S.setMessage(`ФРИСПИНЫ ЗАВЕРШЕНЫ • ПОСЛЕДНЕЕ ВРАЩЕНИЕ ${S.rub(total)}`);S.updateUI();}
    if(S.autoRemaining>0){S.autoRemaining--;S.updateUI();if(S.autoRemaining>0){await S.delay(850);if(!S.busy)S.spin();}else S.stopAuto();}
  };
  S.activateFeature=async(superMode=false)=>{if(S.busy)return;const demoCost=S.bet()*(superMode?250:80);if(S.state.credits<demoCost){S.setMessage('НЕДОСТАТОЧНО ВИРТУАЛЬНЫХ КРЕДИТОВ ДЛЯ БОНУСА');S.sound('error');return;}if(!confirm(`${superMode?'Супер-бонус':'Фриспины'} используют ${S.rub(demoCost)} виртуальных кредитов. Продолжить?`))return;S.stopAuto();S.state.credits-=demoCost;S.state.lastWin=0;S.updateUI();await S.spin({featureStart:true,superFeature:superMode});};
})();
