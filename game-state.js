(() => {
  'use strict';
  const SC = window.SC = {};
  SC.COLS=7; SC.ROWS=7; SC.CELL_COUNT=49; SC.STORAGE_KEY='sweetCascadeDeluxeState.v2'; SC.UI_KEY='sweetCascadeUi.v1';
  SC.BET_LEVELS=[10,20,50,100,200,500,1000,2000]; SC.MULT_STEPS=[2,3,5,10,25,50,100]; SC.PAYOUT_SCALE=2.35; SC.MAX_WIN_X=25000;
  SC.serverMode=!!window.SC_BOOT;
  SC.SYMBOLS={
    redBear:{name:'Малиновый мишка',weight:17,color:'#ff355f',pay:[0,0,0,0,0,.55,.75,1,1.35,1.75,2.3,3.1,4.3,5.8,7.6,10]},
    purpleBear:{name:'Черничный мишка',weight:16,color:'#8124df',pay:[0,0,0,0,0,.48,.66,.9,1.2,1.6,2.1,2.85,3.8,5,6.7,8.5]},
    greenStar:{name:'Лаймовая звезда',weight:19,color:'#22db44',pay:[0,0,0,0,0,.34,.5,.7,.92,1.2,1.6,2.1,2.8,3.7,4.8,6.2]},
    orangeHeart:{name:'Апельсиновое сердце',weight:18,color:'#ff8a16',pay:[0,0,0,0,0,.30,.44,.62,.82,1.08,1.42,1.88,2.48,3.25,4.25,5.5]},
    pinkDrop:{name:'Клубничная капля',weight:20,color:'#ff47b4',pay:[0,0,0,0,0,.24,.36,.5,.68,.9,1.18,1.55,2.05,2.7,3.5,4.5]},
    violetBean:{name:'Виноградная фасоль',weight:20,color:'#9a2de2',pay:[0,0,0,0,0,.22,.32,.46,.62,.82,1.08,1.42,1.86,2.42,3.15,4.1]},
    scatter:{name:'Бонусный автомат',weight:1.15,color:'#ffcc39',scatter:true}
  };
  SC.SYMBOL_KEYS=Object.keys(SC.SYMBOLS); SC.PAY_KEYS=SC.SYMBOL_KEYS.filter(k=>!SC.SYMBOLS[k].scatter);
  SC.defaultState=()=>({credits:10000,betIndex:3,sound:true,turbo:false,freeSpins:0,lastWin:0,totalSpins:0,stormCharge:0});
  SC.loadState=()=>{
    if(SC.serverMode){let ui={};try{ui=JSON.parse(localStorage.getItem(SC.UI_KEY)||'{}')||{};}catch{}const b=window.SC_BOOT;return {...SC.defaultState(),...ui,credits:Number(b.balance||0),freeSpins:Number(b.freeSpins||0),stormCharge:Number(b.stormCharge||0)};}
    try{return {...SC.defaultState(),...(JSON.parse(localStorage.getItem(SC.STORAGE_KEY)||'null')||{})};}catch{return SC.defaultState();}
  };
  SC.state=SC.loadState();
  const bootMap=SC.serverMode?window.SC_BOOT.multiplierMap:null;
  SC.multiplierMap=Array.isArray(bootMap)&&bootMap.length===SC.CELL_COUNT?bootMap:(Array.isArray(SC.state.multiplierMap)&&SC.state.multiplierMap.length===SC.CELL_COUNT?SC.state.multiplierMap:Array(SC.CELL_COUNT).fill(0)); delete SC.state.multiplierMap;
  SC.grid=[]; SC.busy=false; SC.autoRemaining=0; SC.audioCtx=null;
  SC.saveState=()=>{if(SC.serverMode){localStorage.setItem(SC.UI_KEY,JSON.stringify({betIndex:SC.state.betIndex,sound:SC.state.sound,turbo:SC.state.turbo}));return;}localStorage.setItem(SC.STORAGE_KEY,JSON.stringify({...SC.state,multiplierMap:SC.multiplierMap}));};
  SC.rub=v=>new Intl.NumberFormat('ru-RU',{style:'currency',currency:'RUB',maximumFractionDigits:v%1?2:0}).format(v);
  SC.bet=()=>SC.BET_LEVELS[SC.state.betIndex];
  SC.delay=ms=>new Promise(r=>setTimeout(r,SC.state.turbo?Math.max(35,ms*.34):ms));
  SC.weightedSymbol=()=>{const total=SC.SYMBOL_KEYS.reduce((s,k)=>s+SC.SYMBOLS[k].weight,0);let r=Math.random()*total;for(const k of SC.SYMBOL_KEYS){r-=SC.SYMBOLS[k].weight;if(r<=0)return k;}return SC.PAY_KEYS[0];};
  SC.randomGrid=()=>Array.from({length:SC.CELL_COUNT},SC.weightedSymbol);
  SC.$=s=>document.querySelector(s);
  SC.els={grid:SC.$('#grid'),credits:SC.$('#creditsValue'),bet:SC.$('#betValue'),free:SC.$('#freeSpinsValue'),last:SC.$('#lastWinValue'),message:SC.$('#messageStrip'),cascade:SC.$('#cascadeLabel'),round:SC.$('#roundLabel'),spin:SC.$('#spinBtn'),spinCaption:SC.$('#spinCaption'),buyBonus:SC.$('#buyBonusBtn'),buySuper:SC.$('#buySuperBtn'),bonusPrice:SC.$('#bonusPrice'),superPrice:SC.$('#superPrice'),auto:SC.$('#autoBtn'),turbo:SC.$('#turboBtn'),sound:SC.$('#soundBtn'),soundIcon:SC.$('#soundIcon'),overlay:SC.$('#winOverlay'),modal:SC.$('#paytableModal')};
})();
