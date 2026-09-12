(() => {
  'use strict';
  const B=window.ARCADE_BOOT,$=s=>document.querySelector(s),board=$('#arcBoard'),overlay=$('#arcOverlay'),msg=$('#arcMessage'),frame=$('.arc-board-frame');
  const bets=[10,20,50,100,200,500,1000];
  const motion={
    'fruit-fiesta':{pre:7,tick:90,col:135,land:55,anticipate:560,className:'slow-roll',scatter:3},
    'temple-ways':{pre:8,tick:94,col:145,land:62,anticipate:650,className:'slow-roll',scatter:3},
    'jungle-hold':{pre:7,tick:102,col:155,land:64,anticipate:520,className:'slow-roll',scatter:6,special:'coin'},
    'crystal-clusters':{pre:6,tick:65,col:82,land:35,anticipate:420,className:'fast-roll',scatter:4},
    'sun-scroll':{pre:8,tick:100,col:150,land:65,anticipate:720,className:'slow-roll',scatter:3},
    'neon-rush':{pre:6,tick:58,col:76,land:30,anticipate:430,className:'fast-roll',scatter:3},
    'sky-pantheon':{pre:8,tick:88,col:138,land:58,anticipate:700,className:'slow-roll',scatter:4}
  };
  const profile=motion[B.game]||motion['fruit-fiesta'];
  let ui={betIndex:3,turbo:false,sound:true};try{ui={...ui,...(JSON.parse(localStorage.getItem('arcadeUi.'+B.game)||'{}')||{})};}catch{}
  const S={balance:Number(B.balance||0),free:Number(B.freeSpins||0),last:0,busy:false,auto:0,audio:null};
  const keys=Object.keys(B.symbols),rub=v=>new Intl.NumberFormat('ru-RU',{style:'currency',currency:'RUB',maximumFractionDigits:v%1?2:0}).format(v),delay=ms=>new Promise(r=>setTimeout(r,ui.turbo?Math.max(32,ms*.3):ms)),bet=()=>bets[ui.betIndex];
  const save=()=>localStorage.setItem('arcadeUi.'+B.game,JSON.stringify(ui));
  function update(){
    $('#walletValue').textContent=rub(S.balance);document.querySelectorAll('[data-balance]').forEach(e=>e.textContent=rub(S.balance));$('#betValue').textContent=rub(bet());$('#freeValue').textContent=S.free;$('#lastWin').textContent=rub(S.last);$('#modeBadge').textContent=S.free>0?'ФРИСПИНЫ':'ОСНОВНАЯ';$('#modeBadge').classList.toggle('arc-free',S.free>0);$('#turboBtn').classList.toggle('active',ui.turbo);$('#soundBtn').classList.toggle('active',ui.sound);$('#soundBtn').textContent=ui.sound?'🔊 ЗВУК':'🔇 ЗВУК';$('#autoBtn').classList.toggle('active',S.auto>0);$('#autoBtn').textContent=S.auto>0?'СТОП • '+S.auto:'АВТО ×20';save();
  }
  function tone(kind){if(!ui.sound)return;try{S.audio||=new(window.AudioContext||window.webkitAudioContext)();const now=S.audio.currentTime;const palettes={
    'fruit-fiesta':[210,330,520],'temple-ways':[145,260,410],'jungle-hold':[120,220,360],'crystal-clusters':[320,520,760],'sun-scroll':[170,290,460],'neon-rush':[260,620,920],'sky-pantheon':[190,410,780]
  },p=palettes[B.game]||[180,390,620],hit=(f,t,d=.12,g=.035,type='triangle')=>{const o=S.audio.createOscillator(),a=S.audio.createGain();o.type=type;o.frequency.value=f;a.gain.setValueAtTime(.001,now+t);a.gain.linearRampToValueAtTime(g,now+t+.01);a.gain.exponentialRampToValueAtTime(.001,now+t+d);o.connect(a).connect(S.audio.destination);o.start(now+t);o.stop(now+t+d+.03);};if(kind==='roll'){hit(p[0],0,.07,.018);hit(p[1],.05,.07,.015)}if(kind==='land')hit(p[1],0,.07,.024);if(kind==='win'){hit(p[1],0,.13,.035);hit(p[2],.09,.15,.04);hit(p[2]*1.22,.19,.18,.045)}if(kind==='bonus')[0,1,2,3,4].forEach(i=>hit(p[1]+i*80,i*.075,.18,.04));if(kind==='error'){hit(115,0,.18,.04,'sawtooth');hit(82,.12,.18,.03,'sawtooth')}}catch{}}
  function labelFor(sym){return window.ArcadeSymbols?.label(B.game,sym,B.symbols)||(B.symbols[sym]?.name||sym);}
  function cellMarkup(sym){const svg=window.ArcadeSymbols?.render(B.game,sym);const fallback=B.symbols[sym]?.icon||'?';return `<span class="sym" title="${labelFor(sym)}">${svg||fallback}</span>`;}
  function render(grid,opts={}){board.innerHTML='';for(let i=0;i<B.cols*B.rows;i++){const c=Math.floor(i/B.rows),r=i%B.rows,sym=grid[i]||keys[0],el=document.createElement('div');el.className='arc-cell';el.dataset.index=i;el.dataset.sym=sym;el.style.gridColumn=String(c+1);el.style.gridRow=String(r+1);el.innerHTML=cellMarkup(sym);if(opts.sticky?.includes(i))el.classList.add('sticky');board.append(el);}}
  function randomSym(){return keys[Math.floor(Math.random()*keys.length)];}
  async function anticipation(text){msg.textContent=text;frame.classList.add('anticipation');tone('bonus');await delay(profile.anticipate);frame.classList.remove('anticipation');}
  async function reelRoll(target){
    if(!board.children.length)render(Array.from({length:B.cols*B.rows},randomSym));const cells=[...board.children];board.classList.add(profile.className);cells.forEach(e=>e.classList.add('roll'));$('#statusLeft').textContent='ПРОКРУТ';msg.textContent=B.game==='neon-rush'?'СИНХРОНИЗАЦИЯ ИМПУЛЬСОВ…':'БАРАБАНЫ В ДВИЖЕНИИ…';
    for(let k=0;k<profile.pre;k++){for(const e of cells)e.innerHTML=cellMarkup(randomSym());tone('roll');await delay(profile.tick);}
    let specialCount=0,anticipated=false;
    for(let c=0;c<B.cols;c++){
      for(let k=0;k<2;k++){for(let cc=c;cc<B.cols;cc++)for(let r=0;r<B.rows;r++){const i=cc*B.rows+r;cells[i].innerHTML=cellMarkup(randomSym());}await delay(profile.land);}
      for(let r=0;r<B.rows;r++){const i=c*B.rows+r,s=target[i];cells[i].innerHTML=cellMarkup(s);cells[i].classList.remove('roll');cells[i].classList.add('land');cells[i].dataset.sym=s;if(s==='scatter'||(profile.special&&s===profile.special))specialCount++;}
      tone('land');
      if(!anticipated&&c<B.cols-1&&specialCount>=Math.max(2,profile.scatter-1)){anticipated=true;const text=profile.special==='coin'?'ЕЩЁ МОНЕТА ДЛЯ БОНУСА?':'БОНУС СОВСЕМ РЯДОМ…';await anticipation(text);}
      await delay(profile.col);
    }
    render(target);board.classList.remove(profile.className);await delay(190);
  }
  function highlight(pos=[]){for(const i of pos)board.children[i]?.classList.add('win');}
  function stepFx(step){
    if(step.fx==='temple-dust'){frame.classList.add('fx-temple');setTimeout(()=>frame.classList.remove('fx-temple'),600);}
    if(step.fx==='neon-surge'){frame.classList.add('fx-neon');setTimeout(()=>frame.classList.remove('fx-neon'),620);}
    if(step.fx==='sun-expand'){frame.classList.add('fx-sun');setTimeout(()=>frame.classList.remove('fx-sun'),950);}
    if(step.fx==='crystal-blast'){for(const i of step.positions||[])board.children[i]?.classList.add('fx-blast');}
    if(step.fx==='sky-lightning'){frame.classList.add('fx-sky');setTimeout(()=>frame.classList.remove('fx-sky'),680);for(const i of step.multiplier_positions||[])board.children[i]?.classList.add('fx-orb');}
  }
  async function popWin(amount,label){const e=document.createElement('div');e.className='arc-win-pop';e.innerHTML=`${rub(amount)}<small>${label||'ВЫИГРЫШ'}</small>`;overlay.replaceChildren(e);tone('win');await delay(amount>=bet()*10?850:560);overlay.innerHTML='';}
  function toast(text){const e=document.createElement('div');e.className='arc-toast';e.textContent=text;document.body.append(e);setTimeout(()=>e.remove(),2300);}
  async function animateFeature(feature){const box=$('#featureBox');box.innerHTML='';if(!feature||feature.type!=='hold')return;const wrap=document.createElement('div');wrap.className='arc-feature';wrap.innerHTML=`<h3>${feature.title||'БОНУС'}</h3><div class="arc-hold-grid" id="holdGrid"></div><div class="arc-respins" id="respins">РЕСПИНЫ: 3</div>`;box.append(wrap);const g=$('#holdGrid');let prev=new Set();for(const f of feature.frames||[]){g.innerHTML='';const coins=f.coins||{};for(let i=0;i<15;i++){if(coins[i]!=null){const e=document.createElement('div');e.className='arc-coin'+(!prev.has(String(i))?' new':'');e.textContent=(coins[i]/100).toFixed(coins[i]%100?1:0)+'×';g.append(e);}else{const e=document.createElement('div');e.className='arc-empty';g.append(e);}}prev=new Set(Object.keys(coins));$('#respins').textContent='РЕСПИНЫ: '+f.respins;tone(f.new?.length?'win':'land');await delay(660);}if(feature.full)toast('🏆 ПОЛНОЕ ПОЛЕ!');await popWin(Number(feature.win||0),'БОНУС ЗОЛОТОГО ТОТЕМА');}
  async function spin(){
    if(S.busy)return;if(S.free<=0&&S.balance<bet()){msg.textContent='НЕДОСТАТОЧНО ВИРТУАЛЬНЫХ СРЕДСТВ';tone('error');S.auto=0;update();return;}S.busy=true;$('#spinBtn').classList.add('busy');$('#betMinus').disabled=$('#betPlus').disabled=true;msg.textContent='СЕРВЕР РАССЧИТЫВАЕТ ВРАЩЕНИЕ…';$('#featureBox').innerHTML='';
    try{
      const res=await fetch('api/game/arcade-spin.php',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-Token':B.csrf},credentials:'same-origin',body:JSON.stringify({game:B.game,bet:bet()})});const d=await res.json().catch(()=>({ok:false,error:'Некорректный ответ сервера.'}));if(!res.ok||!d.ok)throw new Error(d.error||'Ошибка игры.');
      S.balance=Number(d.balance_before)-Number(d.cost);S.last=0;if(d.is_free_spin)S.free=Math.max(0,S.free-1);update();$('#statusRight').textContent=d.badge||B.title;await reelRoll(d.initial_grid);let totalSteps=0;
      for(const step of d.steps||[]){highlight(step.positions||[]);for(const i of step.multiplier_positions||[])board.children[i]?.classList.add('win');stepFx(step);if(step.multiplier_total)toast('⚡ СОБРАН МНОЖИТЕЛЬ ×'+(step.persistent_multiplier||step.multiplier_total));msg.textContent=(step.label||'ВЫИГРЫШ')+' • +'+rub(Number(step.win||0));$('#statusLeft').textContent=step.extra||step.label||'ВЫИГРЫШ';totalSteps+=Number(step.win||0);S.last=totalSteps;update();await delay(120);await popWin(Number(step.win||0),step.label);await delay(210);render(step.grid_after||d.initial_grid,{sticky:d.sticky||[]});await delay(B.game==='crystal-clusters'?330:470);}
      await animateFeature(d.feature);if(Number(d.free_spins_awarded||0)>0){tone('bonus');toast('🎁 +'+d.free_spins_awarded+' ФРИСПИНОВ');await delay(760);}S.balance=Number(d.balance_after);S.last=Number(d.total_win||0);S.free=Number(d.free_spins||0);render((d.steps?.length?d.steps[d.steps.length-1].grid_after:d.initial_grid),{sticky:d.sticky||[]});$('#statusLeft').textContent='ГОТОВО';$('#statusRight').textContent=d.badge||B.title;msg.textContent=S.last>0?'ИТОГ ВРАЩЕНИЯ '+rub(S.last):(S.free>0?'СЛЕДУЮЩИЙ ФРИСПИН':'ЕЩЁ РАЗ!');update();
      S.busy=false;$('#spinBtn').classList.remove('busy');$('#betMinus').disabled=$('#betPlus').disabled=false;if(S.free>0){await delay(1050);if(!S.busy)spin();return;}if(S.auto>0){S.auto--;update();if(S.auto>0){await delay(930);spin();}}
    }catch(e){S.busy=false;$('#spinBtn').classList.remove('busy');$('#betMinus').disabled=$('#betPlus').disabled=false;S.auto=0;update();tone('error');msg.textContent=e.message||'ОШИБКА СЕРВЕРА';}
  }
  $('#betMinus').onclick=()=>{if(!S.busy){ui.betIndex=Math.max(0,ui.betIndex-1);update();}};$('#betPlus').onclick=()=>{if(!S.busy){ui.betIndex=Math.min(bets.length-1,ui.betIndex+1);update();}};$('#spinBtn').onclick=spin;$('#autoBtn').onclick=()=>{if(S.auto){S.auto=0;update();return;}S.auto=20;update();spin();};$('#turboBtn').onclick=()=>{ui.turbo=!ui.turbo;update();};$('#soundBtn').onclick=()=>{ui.sound=!ui.sound;update();if(ui.sound)tone('land');};document.addEventListener('keydown',e=>{if(e.code==='Space'&&!e.repeat){e.preventDefault();spin();}});
  render(Array.from({length:B.cols*B.rows},randomSym));update();
})();
