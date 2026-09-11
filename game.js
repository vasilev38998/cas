(() => {
  'use strict'; const S=window.SC;
  S.fillPaytable=()=>{S.$('#paytableList').innerHTML=S.PAY_KEYS.map(k=>{const x=S.SYMBOLS[k],p=x.pay;return `<div class="pay-row"><div class="pay-icon">${S.svgFor(k)}</div><div><strong>${x.name}</strong><small>5: ${(p[5]*S.PAYOUT_SCALE).toFixed(2)}× • 8: ${(p[8]*S.PAYOUT_SCALE).toFixed(2)}× • 12: ${(p[12]*S.PAYOUT_SCALE).toFixed(2)}× • 15+: ${(p[15]*S.PAYOUT_SCALE).toFixed(2)}× ставки</small></div></div>`;}).join('')+`<div class="pay-row"><div class="pay-icon">${S.svgFor('scatter')}</div><div><strong>Бонусный автомат</strong><small>3 = 8 • 4 = 10 • 5+ = 12 фриспинов</small></div></div>`;};
  S.$('#betMinus').addEventListener('click',()=>{if(S.busy)return;S.state.betIndex=Math.max(0,S.state.betIndex-1);S.sound('click');S.updateUI();});
  S.$('#betPlus').addEventListener('click',()=>{if(S.busy)return;S.state.betIndex=Math.min(S.BET_LEVELS.length-1,S.state.betIndex+1);S.sound('click');S.updateUI();});
  S.els.spin.addEventListener('click',()=>{S.sound('click');S.spin();});S.els.buyBonus.addEventListener('click',()=>S.activateFeature(false));S.els.buySuper.addEventListener('click',()=>S.activateFeature(true));
  S.els.auto.addEventListener('click',()=>{S.sound('click');if(S.autoRemaining){S.stopAuto();return;}S.autoRemaining=20;S.updateUI();S.spin();});
  S.els.turbo.addEventListener('click',()=>{S.state.turbo=!S.state.turbo;S.sound('click');S.updateUI();});S.els.sound.addEventListener('click',()=>{S.state.sound=!S.state.sound;S.updateUI();if(S.state.sound)S.sound('click');});
  S.$('#resetBtn').addEventListener('click',()=>{if(S.busy)return;if(confirm('Сбросить виртуальный баланс и прогресс демо-игры?')){localStorage.removeItem(S.STORAGE_KEY);S.state=S.defaultState();S.multiplierMap.fill(0);S.grid=S.randomGrid();S.renderGrid({animate:true});S.setMessage('ДЕМО-ИГРА СБРОШЕНА');S.updateUI();}});
  S.$('#paytableBtn').addEventListener('click',()=>{S.fillPaytable();S.els.modal.showModal();S.sound('click');});document.addEventListener('keydown',e=>{if(e.code==='Space'&&!e.repeat&&!S.els.modal.open){e.preventDefault();S.spin();}});
  S.grid=S.randomGrid();S.renderGrid({animate:true});S.updateUI();S.setMessage('УДАЧИ!');
})();
