(function(){
'use strict';
if(!window.ArcadeSymbols)return;
var base=window.ArcadeSymbols,oldRender=base.render;
function W(body,cls){return '<svg class="arc-svg '+(cls||'')+'" viewBox="0 0 100 100" aria-hidden="true" focusable="false">'+body+'</svg>'}
function gem(fill,stroke,pts){return W('<polygon points="'+pts+'" fill="'+fill+'" stroke="'+stroke+'" stroke-width="5" stroke-linejoin="round"/><path d="M31 34L48 22L65 34" fill="none" stroke="#fff" stroke-width="5" opacity=".5" stroke-linecap="round"/>')}
var forge={
 ember:function(){return W('<path d="M18 67L31 29L69 18L84 54L64 84L29 83Z" fill="#ef6328" stroke="#8d2b1d" stroke-width="6"/><path d="M31 58Q45 29 69 27Q58 39 65 53Q48 45 35 69" fill="#ffd462" opacity=".75"/>')},
 hammer:function(){return W('<rect x="42" y="35" width="15" height="54" rx="5" transform="rotate(-34 50 62)" fill="#955b35" stroke="#4d3022" stroke-width="4"/><path d="M22 23L66 12L79 36L35 48Z" fill="#b9c3cc" stroke="#59636c" stroke-width="6"/><path d="M30 26L62 18" stroke="#fff" stroke-width="5" opacity=".45"/>')},
 rune:function(){return W('<path d="M50 10L79 27L82 65L50 90L18 65L21 27Z" fill="#533653" stroke="#d587ff" stroke-width="5"/><path d="M36 70L49 24L65 69M41 51H61" fill="none" stroke="#ffc967" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/>')},
 shield:function(){return W('<path d="M20 18Q50 8 80 18V48Q77 76 50 90Q23 76 20 48Z" fill="#57778a" stroke="#273b47" stroke-width="6"/><path d="M50 20V78M27 43H73" stroke="#d8e7ef" stroke-width="6" opacity=".65"/>')},
 crown:function(){return W('<path d="M17 31L34 49L50 19L66 49L83 31L76 77H24Z" fill="#e7a83c" stroke="#8c551f" stroke-width="5"/><circle cx="36" cy="56" r="5" fill="#6ad7ff"/><circle cx="50" cy="50" r="5" fill="#ff675d"/><circle cx="65" cy="56" r="5" fill="#9a6cff"/>')},
 thunder:function(){return W('<path d="M59 5L20 56H43L35 95L82 41H57Z" fill="#79c7ff" stroke="#2c6ca3" stroke-width="6"/><path d="M52 19L34 48" stroke="#fff" stroke-width="6" opacity=".7"/>')},
 wild:function(){return W('<circle cx="50" cy="50" r="35" fill="#ff8a36" stroke="#ffe17a" stroke-width="5"/><path d="M56 12L30 52H47L42 86L72 45H55Z" fill="#fff5b5" stroke="#d85d23" stroke-width="4"/>')},
 scatter:function(){return W('<path d="M21 77Q23 27 50 15Q77 27 79 77Z" fill="#4e6375" stroke="#9edbff" stroke-width="6"/><path d="M31 77V48Q50 33 69 48V77" fill="#1b2630" stroke="#ffb346" stroke-width="6"/><path d="M50 23L56 37L71 39L60 49L63 64L50 56L37 64L40 49L29 39L44 37Z" fill="#ffe066"/>')}
};
var lunar={
 fox:function(){return W('<path d="M18 35L31 15L42 31Q50 27 58 31L70 15L82 35Q86 62 70 78Q50 93 30 78Q14 62 18 35Z" fill="#d8784a" stroke="#713b31" stroke-width="5"/><path d="M31 57Q50 75 69 57Q62 80 50 84Q38 80 31 57Z" fill="#fff0dc"/><circle cx="37" cy="49" r="4"/><circle cx="63" cy="49" r="4"/>')},
 owl:function(){return W('<path d="M19 36Q24 16 43 25Q50 19 57 25Q76 16 81 36V70Q66 88 50 88Q34 88 19 70Z" fill="#92739f" stroke="#453a58" stroke-width="5"/><circle cx="35" cy="48" r="13" fill="#e9e6cf"/><circle cx="65" cy="48" r="13" fill="#e9e6cf"/><circle cx="35" cy="48" r="5"/><circle cx="65" cy="48" r="5"/><path d="M43 61L50 72L57 61Z" fill="#efc85d"/>')},
 wolf:function(){return W('<path d="M20 27L36 12L42 31Q50 27 58 31L65 12L81 27L76 72Q62 89 50 90Q38 89 24 72Z" fill="#a7b7c9" stroke="#4c6074" stroke-width="5"/><path d="M35 53L44 50M65 53L56 50" stroke="#26333f" stroke-width="6"/><path d="M41 72Q50 78 59 72" fill="none" stroke="#fff" stroke-width="5"/>')},
 stag:function(){return W('<path d="M31 37Q29 18 17 14M31 31Q19 29 14 21M69 37Q71 18 83 14M69 31Q81 29 86 21" stroke="#c8a16a" stroke-width="6" fill="none" stroke-linecap="round"/><path d="M28 37Q50 24 72 37L68 75Q50 91 32 75Z" fill="#b78662" stroke="#66452f" stroke-width="5"/><circle cx="40" cy="52" r="4"/><circle cx="60" cy="52" r="4"/>')},
 lynx:function(){return W('<path d="M22 30L30 11L42 30Q50 26 58 30L70 11L78 30L74 75Q50 91 26 75Z" fill="#c89b6d" stroke="#6f5138" stroke-width="5"/><path d="M29 13L24 4M71 13L76 4" stroke="#423127" stroke-width="5"/><circle cx="38" cy="51" r="4"/><circle cx="62" cy="51" r="4"/><path d="M43 69Q50 75 57 69" fill="none" stroke="#fff" stroke-width="4"/>')},
 wild:function(){return W('<circle cx="50" cy="50" r="36" fill="#cad5ff" stroke="#716ec2" stroke-width="5"/><circle cx="63" cy="40" r="34" fill="#20264f"/><path d="M24 66Q36 78 50 80" stroke="#fff" stroke-width="5" fill="none" opacity=".6"/>')},
 scatter:function(){return W('<path d="M67 16Q43 22 38 49Q35 72 55 85Q31 82 20 63Q7 40 23 20Q42 0 67 16Z" fill="#f1e9ff" stroke="#7772c4" stroke-width="5"/><circle cx="70" cy="29" r="5" fill="#fff8ba"/><circle cx="77" cy="54" r="4" fill="#fff8ba"/>')}
};
var clockwork={
 copper:function(){return gem('#c47b45','#6d4025','50,8 83,36 68,86 32,86 17,36')},
 sapphire:function(){return gem('#4daaf2','#205e9a','50,8 80,27 79,70 50,91 21,70 20,27')},
 emerald:function(){return gem('#4bc88b','#206c52','50,7 84,42 67,87 33,87 16,42')},
 ruby:function(){return gem('#d95b65','#7e2634','50,8 84,42 50,91 16,42')},
 clock:function(){return W('<circle cx="50" cy="50" r="35" fill="#d5c6a7" stroke="#765b3f" stroke-width="6"/><circle cx="50" cy="50" r="27" fill="#27282c"/><path d="M50 50V28M50 50L67 60" stroke="#f3d278" stroke-width="6" stroke-linecap="round"/><circle cx="50" cy="50" r="5" fill="#f3d278"/>')},
 gear:function(){return W('<path d="M44 8H56L60 21Q66 23 71 27L84 22L91 33L81 44Q83 50 81 56L91 67L84 78L71 73Q66 77 60 79L56 92H44L40 79Q34 77 29 73L16 78L9 67L19 56Q17 50 19 44L9 33L16 22L29 27Q34 23 40 21Z" fill="#c68a42" stroke="#654523" stroke-width="5"/><circle cx="50" cy="50" r="17" fill="#26272b" stroke="#edd18b" stroke-width="5"/>')},
 scatter:function(){return W('<circle cx="50" cy="50" r="36" fill="#25333b" stroke="#73d7d2" stroke-width="6"/><circle cx="50" cy="50" r="23" fill="#6c4d35" stroke="#d7a35e" stroke-width="5"/><path d="M50 25L58 43L78 45L63 58L67 78L50 68L33 78L37 58L22 45L42 43Z" fill="#8ff4e5"/>')}
};
var groups={'forge-tempest':forge,'lunar-beasts':lunar,'clockwork-shift':clockwork};
base.render=function(game,sym){var g=groups[game],fn=g&&g[sym];return typeof fn==='function'?fn():oldRender.call(base,game,sym)};
})();
