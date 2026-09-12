(() => {
'use strict';
const W=(body,cls='')=>`<svg class="arc-svg ${cls}" viewBox="0 0 100 100" aria-hidden="true">${body}</svg>`;
const hi='<path d="M25 23Q38 12 52 18" fill="none" stroke="#fff" stroke-width="6" stroke-linecap="round" opacity=".55"/>';
const gem=(fill,stroke,pts)=>W(`<polygon points="${pts}" fill="${fill}" stroke="${stroke}" stroke-width="5" stroke-linejoin="round"/><path d="M31 34L48 22L65 34" fill="none" stroke="#fff" stroke-width="5" opacity=".55" stroke-linecap="round"/>`,'gem-svg');
const fruit={
 cherry:()=>W('<path d="M48 42Q50 18 68 13M58 31Q73 23 82 28" fill="none" stroke="#4c8d35" stroke-width="6" stroke-linecap="round"/><circle cx="34" cy="60" r="22" fill="#e9274f" stroke="#9b1737" stroke-width="5"/><circle cx="66" cy="62" r="22" fill="#ff405d" stroke="#a31538" stroke-width="5"/><ellipse cx="27" cy="51" rx="8" ry="5" fill="#fff" opacity=".55"/>'),
 lemon:()=>W('<ellipse cx="50" cy="53" rx="34" ry="25" transform="rotate(-18 50 53)" fill="#ffd52e" stroke="#c79512" stroke-width="5"/><path d="M72 29Q84 15 91 28Q81 39 69 38" fill="#63c84e" stroke="#318a31" stroke-width="4"/>'+hi),
 grape:()=>W('<path d="M50 22Q54 12 65 12" stroke="#55a946" stroke-width="6" fill="none" stroke-linecap="round"/><path d="M57 18Q74 12 78 26Q63 31 55 24" fill="#67c653"/><g fill="#7b35d4" stroke="#4b1c91" stroke-width="3"><circle cx="38" cy="39" r="13"/><circle cx="57" cy="39" r="13"/><circle cx="29" cy="56" r="13"/><circle cx="48" cy="57" r="14"/><circle cx="67" cy="57" r="13"/><circle cx="39" cy="74" r="13"/><circle cx="58" cy="74" r="13"/></g>'),
 bell:()=>W('<path d="M24 66Q30 56 31 41Q33 18 50 17Q67 18 69 41Q70 56 76 66Z" fill="#ffc52f" stroke="#bb6e12" stroke-width="5"/><rect x="19" y="63" width="62" height="12" rx="6" fill="#f49b1c" stroke="#aa5c10" stroke-width="4"/><circle cx="50" cy="79" r="8" fill="#cf6d17"/>'+hi),
 seven:()=>W('<path d="M23 20H82L78 36Q57 55 43 86H23Q37 54 57 38H23Z" fill="#ff374d" stroke="#a7152b" stroke-width="5"/><path d="M30 27H69" stroke="#fff" stroke-width="7" opacity=".7" stroke-linecap="round"/>'),
 diamond:()=>gem('#54dcff','#167db6','50,10 84,42 50,90 16,42'),
 wild:()=>W('<path d="M50 8L61 36L91 38L68 57L75 87L50 70L25 87L32 57L9 38L39 36Z" fill="#ffcf33" stroke="#e26d19" stroke-width="5"/><text x="50" y="59" text-anchor="middle" font-size="25" font-weight="1000" fill="#8d2b61">W</text>'),
 scatter:()=>W('<rect x="18" y="37" width="64" height="49" rx="9" fill="#e945ad" stroke="#842578" stroke-width="5"/><rect x="13" y="30" width="74" height="18" rx="8" fill="#ff73c8" stroke="#842578" stroke-width="5"/><path d="M50 29V87M22 58H79" stroke="#ffd64b" stroke-width="9"/><path d="M48 30Q29 26 31 14Q45 8 50 26Q55 8 69 14Q71 26 52 30" fill="#ffd64b" stroke="#d48b1c" stroke-width="4"/>')
};
const temple={
 mask:()=>W('<path d="M20 22Q50 8 80 22L75 70L50 89L25 70Z" fill="#d6524d" stroke="#6f292a" stroke-width="5"/><path d="M29 42L44 36L42 51L28 50ZM71 42L56 36L58 51L72 50Z" fill="#ffe06b"/><path d="M38 68Q50 77 62 68" fill="none" stroke="#fff0b3" stroke-width="5" stroke-linecap="round"/><circle cx="50" cy="24" r="7" fill="#55d3b5"/>'),
 idol:()=>W('<rect x="27" y="13" width="46" height="74" rx="15" fill="#b67e45" stroke="#5e432a" stroke-width="5"/><circle cx="40" cy="42" r="8" fill="#57e2c2"/><circle cx="60" cy="42" r="8" fill="#57e2c2"/><path d="M39 65H61M50 52V63" stroke="#5e432a" stroke-width="6" stroke-linecap="round"/><path d="M31 20L18 30M69 20L82 30" stroke="#d4a865" stroke-width="7" stroke-linecap="round"/>'),
 bird:()=>W('<path d="M18 58Q38 22 72 28Q88 34 72 46Q59 55 47 72Q34 84 18 75Q31 67 36 58Q27 62 18 58Z" fill="#28bfc1" stroke="#126f77" stroke-width="5"/><path d="M51 46Q73 43 82 56Q64 63 48 59" fill="#f1c941" stroke="#9d7420" stroke-width="4"/><circle cx="69" cy="34" r="4" fill="#111"/>'),
 snake:()=>W('<path d="M26 75Q18 54 38 49Q62 44 62 29Q62 17 50 16Q39 16 38 27Q37 38 49 39" fill="none" stroke="#45c96f" stroke-width="15" stroke-linecap="round"/><path d="M25 74Q42 88 59 77Q74 68 83 74" fill="none" stroke="#2a9251" stroke-width="13" stroke-linecap="round"/><circle cx="48" cy="15" r="3" fill="#111"/><path d="M42 12L35 8M42 16L34 20" stroke="#e93e49" stroke-width="3"/>'),
 gem:()=>gem('#43d596','#157b63','50,8 82,30 71,78 50,92 29,78 18,30'),
 torch:()=>W('<path d="M43 46H58L63 87H38Z" fill="#8a4e2a" stroke="#5a2d19" stroke-width="5"/><path d="M50 49Q24 35 38 16Q42 29 50 19Q58 7 63 18Q79 36 50 49Z" fill="#ff9c28" stroke="#dc4d18" stroke-width="5"/><path d="M49 40Q41 31 49 23Q58 30 54 40" fill="#ffe34d"/>'),
 wild:()=>W('<circle cx="50" cy="50" r="27" fill="#ffd44c" stroke="#c36c18" stroke-width="5"/><g stroke="#ffd44c" stroke-width="8" stroke-linecap="round"><path d="M50 7V18M50 82V93M7 50H18M82 50H93M19 19L27 27M73 73L81 81M81 19L73 27M27 73L19 81"/></g><text x="50" y="58" text-anchor="middle" font-size="24" font-weight="1000" fill="#7c3e29">W</text>'),
 scatter:()=>W('<path d="M30 25Q50 12 70 25L75 76Q50 91 25 76Z" fill="#d08a42" stroke="#6a4022" stroke-width="5"/><path d="M30 27Q50 39 70 27M28 59Q50 47 72 59" fill="none" stroke="#64d6c2" stroke-width="7"/><path d="M36 20Q50 6 64 20" fill="none" stroke="#6a4022" stroke-width="6"/>')
};
const jungle={
 parrot:()=>W('<path d="M26 73Q18 47 35 27Q50 12 68 25Q84 37 71 52Q59 64 49 82Z" fill="#ef4352" stroke="#8e2333" stroke-width="5"/><path d="M45 47Q60 41 75 50Q68 68 47 70" fill="#1ec79c" stroke="#147660" stroke-width="4"/><path d="M67 29L87 36L70 43Z" fill="#ffd245" stroke="#a86a18" stroke-width="4"/><circle cx="60" cy="31" r="4" fill="#111"/>'),
 tiger:()=>W('<path d="M20 38L27 18L40 29Q50 24 60 29L73 18L80 38Q86 54 73 75Q50 92 27 75Q14 54 20 38Z" fill="#f0942f" stroke="#8d4b20" stroke-width="5"/><path d="M35 32L41 47M65 32L59 47M50 27V44" stroke="#492719" stroke-width="6"/><circle cx="37" cy="55" r="5"/><circle cx="63" cy="55" r="5"/><path d="M42 70Q50 76 58 70" fill="none" stroke="#fff" stroke-width="5" stroke-linecap="round"/>'),
 leaf:()=>W('<path d="M14 76Q23 19 83 15Q88 72 38 83Q24 85 14 76Z" fill="#39bd62" stroke="#14713c" stroke-width="5"/><path d="M21 76Q50 49 76 26M42 56L33 35M53 47L70 50" fill="none" stroke="#d3ef75" stroke-width="5" stroke-linecap="round"/>'),
 drum:()=>W('<ellipse cx="50" cy="24" rx="30" ry="12" fill="#f0c15a" stroke="#7f4a26" stroke-width="5"/><path d="M20 24L26 77Q50 90 74 77L80 24Q50 39 20 24Z" fill="#ad5c35" stroke="#6e3a27" stroke-width="5"/><path d="M27 42L73 64M73 42L27 64" stroke="#f5d576" stroke-width="6"/><ellipse cx="50" cy="77" rx="24" ry="8" fill="#7f402b"/>'),
 gem:()=>gem('#35dca0','#117b5c','50,9 83,35 67,86 33,86 17,35'),
 wild:()=>W('<path d="M27 13H73L79 79L50 91L21 79Z" fill="#b17a3b" stroke="#5b3b23" stroke-width="5"/><circle cx="38" cy="44" r="7" fill="#ffdb58"/><circle cx="62" cy="44" r="7" fill="#ffdb58"/><path d="M37 66H63M50 55V68" stroke="#5b3b23" stroke-width="6"/><text x="50" y="29" text-anchor="middle" font-size="14" font-weight="1000" fill="#fff2a4">W</text>'),
 coin:()=>W('<circle cx="50" cy="50" r="37" fill="#ffc832" stroke="#a86612" stroke-width="6"/><circle cx="50" cy="50" r="27" fill="none" stroke="#fff09a" stroke-width="4"/><path d="M50 28L57 43L73 45L61 56L64 72L50 64L36 72L39 56L27 45L43 43Z" fill="#e99215"/>')
};
const crystal={
 ruby:()=>gem('#ef405d','#8e1c38','50,8 84,36 70,83 30,83 16,36'),
 emerald:()=>gem('#31d17b','#0b7650','50,7 79,22 87,60 50,91 13,60 21,22'),
 sapphire:()=>gem('#37a9ff','#145d9e','50,8 80,31 70,84 30,84 20,31'),
 amethyst:()=>gem('#a65cf1','#5e249d','50,5 85,35 66,88 34,88 15,35'),
 sunstone:()=>gem('#ffd34b','#b36e14','50,8 86,50 50,91 14,50'),
 bomb:()=>W('<circle cx="48" cy="58" r="28" fill="#34284a" stroke="#12101a" stroke-width="6"/><path d="M58 31Q63 20 73 21Q82 22 83 13" fill="none" stroke="#c9863a" stroke-width="6" stroke-linecap="round"/><path d="M83 13L89 8M83 13L92 15M83 13L80 4" stroke="#ffde50" stroke-width="5" stroke-linecap="round"/><path d="M31 47Q47 34 60 42" fill="none" stroke="#fff" stroke-width="6" opacity=".35"/>'),
 scatter:()=>W('<circle cx="50" cy="50" r="13" fill="#fff"/><g fill="none" stroke-width="5"><ellipse cx="50" cy="50" rx="38" ry="16" stroke="#45d9ff"/><ellipse cx="50" cy="50" rx="38" ry="16" transform="rotate(60 50 50)" stroke="#a967ff"/><ellipse cx="50" cy="50" rx="38" ry="16" transform="rotate(-60 50 50)" stroke="#ff51b4"/></g><circle cx="50" cy="50" r="7" fill="#ffe05a"/>')
};
const desert={
 falcon:()=>W('<path d="M15 61Q34 29 50 19Q66 29 85 61Q66 54 58 44Q61 69 50 86Q39 69 42 44Q34 54 15 61Z" fill="#d29a48" stroke="#774826" stroke-width="5"/><circle cx="50" cy="29" r="6" fill="#fff0b0"/>'),
 scarab:()=>W('<ellipse cx="50" cy="56" rx="23" ry="30" fill="#37b3a0" stroke="#165f59" stroke-width="5"/><circle cx="50" cy="27" r="13" fill="#e3a83e" stroke="#865a22" stroke-width="5"/><path d="M27 43L12 29M73 43L88 29M26 58H10M74 58H90M30 72L17 84M70 72L83 84M50 40V82" stroke="#165f59" stroke-width="5" stroke-linecap="round"/>'),
 lotus:()=>W('<path d="M50 82Q27 61 36 35Q49 42 50 62Q51 42 64 35Q73 61 50 82Z" fill="#ed78be" stroke="#9a356f" stroke-width="4"/><path d="M48 79Q12 70 16 46Q37 48 48 68M52 79Q88 70 84 46Q63 48 52 68" fill="#ffaad6" stroke="#9a356f" stroke-width="4"/>'),
 ankh:()=>W('<ellipse cx="50" cy="28" rx="15" ry="18" fill="none" stroke="#ffd25c" stroke-width="8"/><path d="M50 46V88M28 61H72" stroke="#ffd25c" stroke-width="9" stroke-linecap="round"/><path d="M50 46V88M28 61H72" stroke="#a76621" stroke-width="2" opacity=".5"/>'),
 crown:()=>W('<path d="M18 30L35 47L50 22L65 47L82 30L76 76H24Z" fill="#ffd34d" stroke="#a76817" stroke-width="5"/><circle cx="35" cy="52" r="5" fill="#ef4e62"/><circle cx="50" cy="48" r="5" fill="#38b9df"/><circle cx="65" cy="52" r="5" fill="#8d55d8"/><path d="M28 66H72" stroke="#fff1a1" stroke-width="5"/>'),
 wild:()=>W('<circle cx="50" cy="50" r="28" fill="#ffcc42" stroke="#b86d18" stroke-width="5"/><g stroke="#ffcc42" stroke-width="8" stroke-linecap="round"><path d="M50 6V17M50 83V94M6 50H17M83 50H94M18 18L26 26M74 74L82 82M82 18L74 26M26 74L18 82"/></g><text x="50" y="58" text-anchor="middle" font-size="24" font-weight="1000" fill="#7f4022">W</text>'),
 scatter:()=>W('<path d="M22 20Q50 12 78 20L72 78Q50 89 28 78Z" fill="#efe0ae" stroke="#8b6332" stroke-width="5"/><path d="M33 36H67M32 50H66M31 64H61" stroke="#b47d3d" stroke-width="5" stroke-linecap="round"/><path d="M66 60L82 77" stroke="#e44e42" stroke-width="7"/>')
};
const neon={
 cyan:()=>gem('#2ee9ff','#0c7e9e','50,9 84,50 50,91 16,50'),
 pink:()=>W('<circle cx="50" cy="50" r="31" fill="none" stroke="#ff4dc4" stroke-width="13"/><circle cx="50" cy="50" r="12" fill="#fff" opacity=".8"/>','neon-svg'),
 lime:()=>W('<polygon points="50,10 88,82 12,82" fill="#9dff42" stroke="#4b9f1e" stroke-width="6"/><path d="M50 27L68 67H32Z" fill="#17321e" opacity=".55"/>','neon-svg'),
 violet:()=>gem('#9d5cff','#5120a7','50,8 82,27 82,67 50,92 18,67 18,27'),
 orange:()=>W('<rect x="18" y="18" width="64" height="64" rx="10" fill="#ff9d2e" stroke="#a84f12" stroke-width="6"/><rect x="31" y="31" width="38" height="38" rx="5" fill="#fff1a0" opacity=".36"/>','neon-svg'),
 wild:()=>W('<path d="M57 5L22 55H45L39 95L78 43H55Z" fill="#fff348" stroke="#d57818" stroke-width="6"/><path d="M52 19L34 48" stroke="#fff" stroke-width="5" opacity=".65"/>','neon-svg'),
 scatter:()=>W('<rect x="23" y="18" width="54" height="69" rx="12" fill="#28345f" stroke="#4beaff" stroke-width="6"/><rect x="40" y="8" width="20" height="12" rx="4" fill="#4beaff"/><rect x="32" y="31" width="36" height="43" rx="6" fill="#65f27b"/><path d="M55 35L39 57H50L47 70L63 50H53Z" fill="#fff64f"/>','neon-svg')
};
const pantheon={
 laurel:()=>W('<path d="M50 84Q20 75 17 46Q20 27 34 18M50 84Q80 75 83 46Q80 27 66 18" fill="none" stroke="#5bcf73" stroke-width="7" stroke-linecap="round"/><g fill="#8ce28f"><ellipse cx="26" cy="31" rx="10" ry="5" transform="rotate(-40 26 31)"/><ellipse cx="22" cy="46" rx="10" ry="5" transform="rotate(-20 22 46)"/><ellipse cx="29" cy="62" rx="10" ry="5" transform="rotate(20 29 62)"/><ellipse cx="74" cy="31" rx="10" ry="5" transform="rotate(40 74 31)"/><ellipse cx="78" cy="46" rx="10" ry="5" transform="rotate(20 78 46)"/><ellipse cx="71" cy="62" rx="10" ry="5" transform="rotate(-20 71 62)"/></g><circle cx="50" cy="51" r="16" fill="#ffd75d" stroke="#a76b18" stroke-width="5"/>'),
 chalice:()=>W('<path d="M25 17H75L70 39Q65 60 52 64V78H70V88H30V78H48V64Q35 60 30 39Z" fill="#f6c953" stroke="#9b651d" stroke-width="5"/><path d="M29 26H71" stroke="#fff3ae" stroke-width="5"/><circle cx="50" cy="39" r="7" fill="#7bdcff"/>'),
 harp:()=>W('<path d="M27 14Q73 23 72 76H30Q47 52 27 14Z" fill="none" stroke="#e9b84d" stroke-width="8" stroke-linecap="round"/><path d="M38 28V73M48 31V73M58 35V73" stroke="#fff1b2" stroke-width="3"/><path d="M26 76H78" stroke="#9e6220" stroke-width="8" stroke-linecap="round"/>'),
 ring:()=>W('<ellipse cx="50" cy="60" rx="31" ry="22" fill="none" stroke="#e8b33f" stroke-width="12"/><polygon points="50,11 67,31 58,48 42,48 33,31" fill="#58d9ff" stroke="#187ca3" stroke-width="5"/><path d="M41 25L50 16L59 25" fill="none" stroke="#fff" stroke-width="4" opacity=".6"/>'),
 wing:()=>W('<path d="M15 69Q29 19 78 15Q64 30 50 40Q71 34 85 37Q67 54 45 57Q60 60 70 70Q40 82 15 69Z" fill="#f5f0dc" stroke="#aaa28e" stroke-width="5"/><path d="M25 64Q48 47 72 27" stroke="#d3cbb7" stroke-width="5"/>'),
 thunder:()=>W('<path d="M58 5L19 56H43L35 95L81 42H56Z" fill="#ffd84b" stroke="#b66c18" stroke-width="6"/><path d="M52 18L33 49" stroke="#fff" stroke-width="6" opacity=".65"/>'),
 scatter:()=>W('<path d="M18 80Q25 27 50 15Q75 27 82 80Z" fill="#e9eef7" stroke="#7182a0" stroke-width="5"/><path d="M30 80V50Q50 35 70 50V80M23 80H77" fill="none" stroke="#75c9ff" stroke-width="7"/><circle cx="50" cy="28" r="7" fill="#ffd75b"/>')
};
const groups={'fruit-fiesta':fruit,'temple-ways':temple,'jungle-hold':jungle,'crystal-clusters':crystal,'sun-scroll':desert,'neon-rush':neon,'sky-pantheon':pantheon};
function orb(value){return W(`<circle cx="50" cy="50" r="37" fill="#62c9ff" stroke="#eef9ff" stroke-width="5"/><circle cx="50" cy="50" r="29" fill="#4264d8" stroke="#9ce8ff" stroke-width="3"/><path d="M29 31Q43 16 59 24" fill="none" stroke="#fff" stroke-width="7" opacity=".65" stroke-linecap="round"/><text x="50" y="60" text-anchor="middle" font-size="29" font-weight="1000" fill="#fff">×${value}</text>`,'orb-svg');}
window.ArcadeSymbols={
 render(game,sym){if(/^orb\d+$/.test(sym))return orb(sym.replace('orb',''));const fn=groups[game]?.[sym];return fn?fn():W(`<circle cx="50" cy="50" r="32" fill="#777"/><text x="50" y="58" text-anchor="middle" fill="#fff" font-size="18">?</text>`);},
 label(game,sym,defs){if(/^orb\d+$/.test(sym))return 'Небесный множитель ×'+sym.replace('orb','');return defs?.[sym]?.name||sym;}
};
})();
