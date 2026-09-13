<?php
declare(strict_types=1);

function game_catalog(): array {
    return [
        'sweet-cascade' => [
            'title'=>'Сладкий каскад','subtitle'=>'Кластеры 6+ • каскады • множители','route'=>'game.php','theme'=>'candy','icon'=>'🍬','accent'=>'linear-gradient(135deg,#7b42df,#ef4cb8 55%,#4acbf3)','cols'=>7,'rows'=>7,'volatility'=>'Высокая','buy_bonus'=>80,'buy_super'=>250,'tags'=>['Кластеры','Buy Bonus','Каскады'],
        ],
        'fruit-fiesta' => [
            'title'=>'Фруктовая фиеста','subtitle'=>'8 линий • липкие вайлды • высокая волатильность','route'=>'play.php?game=fruit-fiesta','theme'=>'fruit','icon'=>'🍒','accent'=>'linear-gradient(135deg,#f05a28,#ffcf3e 50%,#72df59)','cols'=>5,'rows'=>4,'volatility'=>'Высокая','buy_bonus'=>75,'tags'=>['Линии','Sticky Wild','Buy Bonus'],
            'symbols'=>['cherry'=>['icon'=>'C','name'=>'Вишня'],'lemon'=>['icon'=>'L','name'=>'Лимон'],'grape'=>['icon'=>'G','name'=>'Виноград'],'bell'=>['icon'=>'B','name'=>'Колокол'],'seven'=>['icon'=>'7','name'=>'Семёрка'],'diamond'=>['icon'=>'D','name'=>'Бриллиант'],'wild'=>['icon'=>'W','name'=>'Вайлд'],'scatter'=>['icon'=>'S','name'=>'Бонус']],
            'rules'=>'8 линий слева направо. Вайлд и scatter редкие. Во фриспинах выпавшие вайлды становятся липкими и сохраняются до конца серии.'
        ],
        'temple-ways' => [
            'title'=>'Храм тысячи путей','subtitle'=>'Полные пути 6/6 • каскады • растущая сила','route'=>'play.php?game=temple-ways','theme'=>'temple','icon'=>'🗿','accent'=>'linear-gradient(135deg,#0f5d52,#d39a32 55%,#7a3f1f)','cols'=>6,'rows'=>5,'volatility'=>'Очень высокая','buy_bonus'=>90,'tags'=>['Ways','Каскады','Buy Bonus'],
            'symbols'=>['mask'=>['icon'=>'M','name'=>'Ритуальная маска'],'idol'=>['icon'=>'I','name'=>'Каменный идол'],'bird'=>['icon'=>'B','name'=>'Храмовая птица'],'snake'=>['icon'=>'S','name'=>'Змея'],'gem'=>['icon'=>'G','name'=>'Зелёный самоцвет'],'torch'=>['icon'=>'T','name'=>'Факел'],'wild'=>['icon'=>'W','name'=>'Солнце-вайлд'],'scatter'=>['icon'=>'A','name'=>'Амфора-бонус']],
            'rules'=>'Выигрышный путь должен пройти через все 6 барабанов и иметь достаточную плотность совпадений. После победы запускается каскад и растёт сила; во фриспинах множитель переносится между вращениями.'
        ],
        'jungle-hold' => [
            'title'=>'Джунгли: Золотой тотем','subtitle'=>'6 линий • редкие монеты • hold-and-respin','route'=>'play.php?game=jungle-hold','theme'=>'jungle','icon'=>'🪙','accent'=>'linear-gradient(135deg,#124f35,#d7a62d 55%,#7b3f19)','cols'=>5,'rows'=>3,'volatility'=>'Очень высокая','buy_bonus'=>70,'buy_type'=>'hold','tags'=>['Hold & Respin','Монеты','Buy Bonus'],
            'symbols'=>['parrot'=>['icon'=>'P','name'=>'Попугай'],'tiger'=>['icon'=>'T','name'=>'Тигр'],'leaf'=>['icon'=>'L','name'=>'Лист'],'drum'=>['icon'=>'D','name'=>'Барабан'],'gem'=>['icon'=>'G','name'=>'Камень джунглей'],'wild'=>['icon'=>'W','name'=>'Тотем-вайлд'],'coin'=>['icon'=>'C','name'=>'Золотая монета']],
            'rules'=>'6 линий и редкий wild. 6+ золотых монет запускают 3 респина: монеты фиксируются, новая монета возвращает счётчик к трём. Bonus Buy сразу запускает hold-and-respin.'
        ],
        'crystal-clusters' => [
            'title'=>'Кристальный реактор','subtitle'=>'8×8 • кластеры 7+ • бомбы • цепные взрывы','route'=>'play.php?game=crystal-clusters','theme'=>'crystal','icon'=>'💠','accent'=>'linear-gradient(135deg,#16486f,#744ed8 48%,#e94cb7)','cols'=>8,'rows'=>8,'volatility'=>'Высокая','buy_bonus'=>85,'tags'=>['Кластеры','Бомбы','Buy Bonus'],
            'symbols'=>['ruby'=>['icon'=>'R','name'=>'Рубин'],'emerald'=>['icon'=>'E','name'=>'Изумруд'],'sapphire'=>['icon'=>'S','name'=>'Сапфир'],'amethyst'=>['icon'=>'A','name'=>'Аметист'],'sunstone'=>['icon'=>'U','name'=>'Солнечный камень'],'bomb'=>['icon'=>'B','name'=>'Кристальная бомба'],'scatter'=>['icon'=>'X','name'=>'Реактор-бонус']],
            'rules'=>'Выигрывают кластеры только из 7+ одинаковых кристаллов. Бомбы редкие: если они соприкасаются с выигрышем, взрывают область 3×3 и могут продолжить каскад. 4+ символа реактора дают фриспины.'
        ],
        'sun-scroll' => [
            'title'=>'Свиток солнца','subtitle'=>'6 линий • редкий бонус • расширяющийся символ','route'=>'play.php?game=sun-scroll','theme'=>'desert','icon'=>'📜','accent'=>'linear-gradient(135deg,#c87825,#f1c55c 52%,#7f3e32)','cols'=>5,'rows'=>3,'volatility'=>'Очень высокая','buy_bonus'=>80,'tags'=>['Expanding','Фриспины','Buy Bonus'],
            'symbols'=>['falcon'=>['icon'=>'F','name'=>'Сокол'],'scarab'=>['icon'=>'S','name'=>'Скарабей'],'lotus'=>['icon'=>'L','name'=>'Лотос'],'ankh'=>['icon'=>'A','name'=>'Анкх'],'crown'=>['icon'=>'C','name'=>'Корона'],'wild'=>['icon'=>'W','name'=>'Солнце-вайлд'],'scatter'=>['icon'=>'R','name'=>'Свиток-бонус']],
            'rules'=>'6 линий, редкие wild и scatter. 3+ свитка запускают фриспины и выбирают один обычный символ. Во время бонуса выбранный символ расширяется на весь барабан перед подсчётом линий.'
        ],
        'neon-rush' => [
            'title'=>'Неоновый разгон','subtitle'=>'5/5 барабанов • плотность 9+ • tumble • напряжение','route'=>'play.php?game=neon-rush','theme'=>'neon','icon'=>'⚡','accent'=>'linear-gradient(135deg,#11164d,#7728d7 45%,#12d9e6)','cols'=>5,'rows'=>5,'volatility'=>'Очень высокая','buy_bonus'=>90,'tags'=>['Tumble','Множитель','Buy Bonus'],
            'symbols'=>['cyan'=>['icon'=>'C','name'=>'Циан'],'pink'=>['icon'=>'P','name'=>'Розовый импульс'],'lime'=>['icon'=>'L','name'=>'Лаймовый импульс'],'violet'=>['icon'=>'V','name'=>'Фиолетовый импульс'],'orange'=>['icon'=>'O','name'=>'Оранжевый импульс'],'wild'=>['icon'=>'W','name'=>'Энерго-вайлд'],'scatter'=>['icon'=>'B','name'=>'Батарея-бонус']],
            'rules'=>'Для выигрыша символ должен пройти через все 5 барабанов, а суммарно в полном пути должно быть не менее 9 подходящих символов. После попадания запускается tumble и повышается напряжение.'
        ],
        'sky-pantheon' => [
            'title'=>'Небесный пантеон','subtitle'=>'6×5 • 10+ в любом месте • tumble • сферы-множители','route'=>'play.php?game=sky-pantheon','theme'=>'pantheon','icon'=>'⚡','accent'=>'linear-gradient(135deg,#3854b8,#7ac8ff 48%,#f0c45d)','cols'=>6,'rows'=>5,'volatility'=>'Очень высокая','buy_bonus'=>100,'tags'=>['Scatter Pays','Tumble','Множители'],
            'symbols'=>['laurel'=>['icon'=>'L','name'=>'Лавровый медальон'],'chalice'=>['icon'=>'C','name'=>'Небесная чаша'],'harp'=>['icon'=>'H','name'=>'Золотая арфа'],'ring'=>['icon'=>'R','name'=>'Кольцо неба'],'wing'=>['icon'=>'W','name'=>'Крыло'],'thunder'=>['icon'=>'T','name'=>'Молния'],'scatter'=>['icon'=>'G','name'=>'Врата-бонус']],
            'rules'=>'10+ одинаковых символов платят в любом месте сетки и исчезают. Редкие сферы дают множители ×2–×100 вместе с выигрышем. 4+ Врат запускают фриспины, где сферы накапливают Небесный заряд.'
        ],
        'velvet-curtains' => [
            'title'=>'Бархатные шторки','subtitle'=>'5×4 • закрывающиеся барабаны • расширение • фриспины','route'=>'play.php?game=velvet-curtains','theme'=>'velvet','icon'=>'🎭','accent'=>'linear-gradient(135deg,#4f102c,#b42c63 52%,#e5b96c)','cols'=>5,'rows'=>4,'volatility'=>'Очень высокая','buy_bonus'=>85,'tags'=>['Шторки','Expanding','Buy Bonus'],
            'symbols'=>['rose'=>['icon'=>'R','name'=>'Роза'],'fan'=>['icon'=>'F','name'=>'Веер'],'mask'=>['icon'=>'M','name'=>'Маска'],'crown'=>['icon'=>'C','name'=>'Корона'],'gem'=>['icon'=>'G','name'=>'Рубин сцены'],'wild'=>['icon'=>'W','name'=>'Золотой wild'],'scatter'=>['icon'=>'S','name'=>'Билет-бонус']],
            'rules'=>'Иногда один или два барабана закрываются бархатными шторками. После паузы они открываются полностью одним символом. Во фриспинах двойной занавес активируется на каждом вращении.'
        ],
        'mystery-vault' => [
            'title'=>'Хранилище тайн','subtitle'=>'5×4 • mystery-блоки • единое превращение • фриспины','route'=>'play.php?game=mystery-vault','theme'=>'vault','icon'=>'🔐','accent'=>'linear-gradient(135deg,#132338,#35667c 50%,#d2ad57)','cols'=>5,'rows'=>4,'volatility'=>'Высокая','buy_bonus'=>80,'tags'=>['Mystery','Линии','Buy Bonus'],
            'symbols'=>['key'=>['icon'=>'K','name'=>'Ключ'],'watch'=>['icon'=>'T','name'=>'Часы'],'ring'=>['icon'=>'R','name'=>'Кольцо'],'pearl'=>['icon'=>'P','name'=>'Жемчуг'],'gem'=>['icon'=>'G','name'=>'Сейфовый камень'],'mystery'=>['icon'=>'?','name'=>'Тайный блок'],'wild'=>['icon'=>'W','name'=>'Wild'],'scatter'=>['icon'=>'S','name'=>'Сейф-бонус']],
            'rules'=>'Все mystery-блоки одного вращения одновременно превращаются в один случайный символ. Во фриспинах mystery появляются чаще, поэтому один reveal может полностью изменить поле.'
        ],
        'forge-tempest' => [
            'title'=>'Грозовая кузница','subtitle'=>'6×5 • 8+ в любом месте • каскады • множитель ковки','route'=>'play.php?game=forge-tempest','theme'=>'forge','icon'=>'⚒','accent'=>'linear-gradient(135deg,#3d2020,#d86828 48%,#78bfff)','cols'=>6,'rows'=>5,'volatility'=>'Очень высокая','buy_bonus'=>95,'tags'=>['Scatter Pays','Каскады','Множитель','Buy Bonus'],
            'symbols'=>['ember'=>['icon'=>'E','name'=>'Раскалённый слиток'],'hammer'=>['icon'=>'H','name'=>'Молот'],'rune'=>['icon'=>'R','name'=>'Руна кузницы'],'shield'=>['icon'=>'S','name'=>'Щит'],'crown'=>['icon'=>'C','name'=>'Кованая корона'],'thunder'=>['icon'=>'T','name'=>'Громовой клинок'],'wild'=>['icon'=>'W','name'=>'Искровой wild'],'scatter'=>['icon'=>'B','name'=>'Врата кузницы']],
            'rules'=>'8+ одинаковых символов платят в любом месте поля. После каждого выигрышного каскада множитель ковки растёт. Во фриспинах текущая сила переносится на следующее вращение и может подняться до ×12.'
        ],
        'lunar-beasts' => [
            'title'=>'Лунный зверинец','subtitle'=>'5×4 • 20 линий • полные wild-колонны • фазы луны','route'=>'play.php?game=lunar-beasts','theme'=>'lunar','icon'=>'☾','accent'=>'linear-gradient(135deg,#151b49,#4e4aa8 48%,#d8d6ff)','cols'=>5,'rows'=>4,'volatility'=>'Высокая','buy_bonus'=>85,'tags'=>['Линии','Expanding Wild','Фриспины','Buy Bonus'],
            'symbols'=>['fox'=>['icon'=>'F','name'=>'Лунная лиса'],'owl'=>['icon'=>'O','name'=>'Сова'],'wolf'=>['icon'=>'W','name'=>'Серебряный волк'],'stag'=>['icon'=>'S','name'=>'Олень'],'lynx'=>['icon'=>'L','name'=>'Рысь'],'wild'=>['icon'=>'M','name'=>'Лунный wild'],'scatter'=>['icon'=>'C','name'=>'Полумесяц-бонус']],
            'rules'=>'20 линий. В отдельных вращениях луна превращает целые барабаны в wild-колонны. Во фриспинах фаза луны растёт после каждого вращения и повышает шанс появления полных wild-колонн.'
        ],
        'clockwork-shift' => [
            'title'=>'Механический импульс','subtitle'=>'5×5 • кластеры 6+ • крестовые шестерни • каскады','route'=>'play.php?game=clockwork-shift','theme'=>'clockwork','icon'=>'⚙','accent'=>'linear-gradient(135deg,#24212a,#8b653e 48%,#55c6c3)','cols'=>5,'rows'=>5,'volatility'=>'Очень высокая','buy_bonus'=>90,'tags'=>['Кластеры','Шестерни','Каскады','Buy Bonus'],
            'symbols'=>['copper'=>['icon'=>'C','name'=>'Медный модуль'],'sapphire'=>['icon'=>'S','name'=>'Сапфировый модуль'],'emerald'=>['icon'=>'E','name'=>'Изумрудный модуль'],'ruby'=>['icon'=>'R','name'=>'Рубиновый модуль'],'clock'=>['icon'=>'T','name'=>'Хронометр'],'gear'=>['icon'=>'G','name'=>'Импульсная шестерня'],'scatter'=>['icon'=>'B','name'=>'Сердце механизма']],
            'rules'=>'Кластеры 6+ исчезают и запускают каскад. Шестерня рядом с выигрышем очищает целую строку и колонну крестом. Во фриспинах мощность шестерней накапливается и усиливает их бонус.'
        ],
    ];
}
function game_config(string $key): ?array {$all=game_catalog();return $all[$key]??null;}
