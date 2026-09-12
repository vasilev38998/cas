<?php
declare(strict_types=1);

function game_catalog(): array {
    return [
        'sweet-cascade' => [
            'title'=>'Сладкий каскад','subtitle'=>'Кластеры 6+ • каскады • множители','route'=>'game.php','theme'=>'candy','icon'=>'🍬','accent'=>'linear-gradient(135deg,#7b42df,#ef4cb8 55%,#4acbf3)','cols'=>7,'rows'=>7,'volatility'=>'Высокая',
        ],
        'fruit-fiesta' => [
            'title'=>'Фруктовая фиеста','subtitle'=>'8 линий • липкие вайлды • высокая волатильность','route'=>'play.php?game=fruit-fiesta','theme'=>'fruit','icon'=>'🍒','accent'=>'linear-gradient(135deg,#f05a28,#ffcf3e 50%,#72df59)','cols'=>5,'rows'=>4,'volatility'=>'Высокая',
            'symbols'=>['cherry'=>['icon'=>'C','name'=>'Вишня'],'lemon'=>['icon'=>'L','name'=>'Лимон'],'grape'=>['icon'=>'G','name'=>'Виноград'],'bell'=>['icon'=>'B','name'=>'Колокол'],'seven'=>['icon'=>'7','name'=>'Семёрка'],'diamond'=>['icon'=>'D','name'=>'Бриллиант'],'wild'=>['icon'=>'W','name'=>'Вайлд'],'scatter'=>['icon'=>'S','name'=>'Бонус']],
            'rules'=>'8 линий слева направо. Вайлд и scatter редкие, поэтому пустые вращения встречаются заметно чаще, чем раньше. Во фриспинах выпавшие вайлды становятся липкими и сохраняются до конца серии.'
        ],
        'temple-ways' => [
            'title'=>'Храм тысячи путей','subtitle'=>'Полные пути 6/6 • каскады • растущая сила','route'=>'play.php?game=temple-ways','theme'=>'temple','icon'=>'🗿','accent'=>'linear-gradient(135deg,#0f5d52,#d39a32 55%,#7a3f1f)','cols'=>6,'rows'=>5,'volatility'=>'Очень высокая',
            'symbols'=>['mask'=>['icon'=>'M','name'=>'Ритуальная маска'],'idol'=>['icon'=>'I','name'=>'Каменный идол'],'bird'=>['icon'=>'B','name'=>'Храмовая птица'],'snake'=>['icon'=>'S','name'=>'Змея'],'gem'=>['icon'=>'G','name'=>'Зелёный самоцвет'],'torch'=>['icon'=>'T','name'=>'Факел'],'wild'=>['icon'=>'W','name'=>'Солнце-вайлд'],'scatter'=>['icon'=>'A','name'=>'Амфора-бонус']],
            'rules'=>'Выигрышный путь должен пройти через все 6 барабанов слева направо и содержать достаточно плотное совпадение. Это сильно уменьшает число мелких выигрышей. После победы запускается каскад, а сила храма растёт; во фриспинах множитель переносится между вращениями.'
        ],
        'jungle-hold' => [
            'title'=>'Джунгли: Золотой тотем','subtitle'=>'6 линий • редкие монеты • hold-and-respin','route'=>'play.php?game=jungle-hold','theme'=>'jungle','icon'=>'🪙','accent'=>'linear-gradient(135deg,#124f35,#d7a62d 55%,#7b3f19)','cols'=>5,'rows'=>3,'volatility'=>'Очень высокая',
            'symbols'=>['parrot'=>['icon'=>'P','name'=>'Попугай'],'tiger'=>['icon'=>'T','name'=>'Тигр'],'leaf'=>['icon'=>'L','name'=>'Лист'],'drum'=>['icon'=>'D','name'=>'Барабан'],'gem'=>['icon'=>'G','name'=>'Камень джунглей'],'wild'=>['icon'=>'W','name'=>'Тотем-вайлд'],'coin'=>['icon'=>'C','name'=>'Золотая монета']],
            'rules'=>'6 линий и редкий wild. 6+ золотых монет запускают 3 респина: монеты фиксируются, новая монета возвращает счётчик к трём. Сам бонус редкий, зато длинная серия монет может дать заметный результат.'
        ],
        'crystal-clusters' => [
            'title'=>'Кристальный реактор','subtitle'=>'8×8 • кластеры 7+ • бомбы • цепные взрывы','route'=>'play.php?game=crystal-clusters','theme'=>'crystal','icon'=>'💠','accent'=>'linear-gradient(135deg,#16486f,#744ed8 48%,#e94cb7)','cols'=>8,'rows'=>8,'volatility'=>'Высокая',
            'symbols'=>['ruby'=>['icon'=>'R','name'=>'Рубин'],'emerald'=>['icon'=>'E','name'=>'Изумруд'],'sapphire'=>['icon'=>'S','name'=>'Сапфир'],'amethyst'=>['icon'=>'A','name'=>'Аметист'],'sunstone'=>['icon'=>'U','name'=>'Солнечный камень'],'bomb'=>['icon'=>'B','name'=>'Кристальная бомба'],'scatter'=>['icon'=>'X','name'=>'Реактор-бонус']],
            'rules'=>'Выигрывают кластеры только из 7+ одинаковых кристаллов. Бомбы редкие: если они соприкасаются с выигрышем, взрывают область 3×3 и могут продолжить каскад. 4+ символа реактора дают фриспины с повышенной частотой бомб.'
        ],
        'sun-scroll' => [
            'title'=>'Свиток солнца','subtitle'=>'6 линий • редкий бонус • расширяющийся символ','route'=>'play.php?game=sun-scroll','theme'=>'desert','icon'=>'📜','accent'=>'linear-gradient(135deg,#c87825,#f1c55c 52%,#7f3e32)','cols'=>5,'rows'=>3,'volatility'=>'Очень высокая',
            'symbols'=>['falcon'=>['icon'=>'F','name'=>'Сокол'],'scarab'=>['icon'=>'S','name'=>'Скарабей'],'lotus'=>['icon'=>'L','name'=>'Лотос'],'ankh'=>['icon'=>'A','name'=>'Анкх'],'crown'=>['icon'=>'C','name'=>'Корона'],'wild'=>['icon'=>'W','name'=>'Солнце-вайлд'],'scatter'=>['icon'=>'R','name'=>'Свиток-бонус']],
            'rules'=>'6 линий, редкие wild и scatter. 3+ свитка запускают фриспины и выбирают один обычный символ. Во время бонуса выбранный символ расширяется на весь барабан перед подсчётом линий.'
        ],
        'neon-rush' => [
            'title'=>'Неоновый разгон','subtitle'=>'5/5 барабанов • плотность 10+ • tumble • напряжение','route'=>'play.php?game=neon-rush','theme'=>'neon','icon'=>'⚡','accent'=>'linear-gradient(135deg,#11164d,#7728d7 45%,#12d9e6)','cols'=>5,'rows'=>5,'volatility'=>'Очень высокая',
            'symbols'=>['cyan'=>['icon'=>'C','name'=>'Циан'],'pink'=>['icon'=>'P','name'=>'Розовый импульс'],'lime'=>['icon'=>'L','name'=>'Лаймовый импульс'],'violet'=>['icon'=>'V','name'=>'Фиолетовый импульс'],'orange'=>['icon'=>'O','name'=>'Оранжевый импульс'],'wild'=>['icon'=>'W','name'=>'Энерго-вайлд'],'scatter'=>['icon'=>'B','name'=>'Батарея-бонус']],
            'rules'=>'Для выигрыша символ должен пройти через все 5 барабанов, а суммарно в полном пути должно быть не менее 10 подходящих символов. После редкого попадания запускается tumble и повышается напряжение. Во фриспинах напряжение сохраняется между вращениями.'
        ],
        'sky-pantheon' => [
            'title'=>'Небесный пантеон','subtitle'=>'6×5 • 10+ в любом месте • tumble • сферы-множители','route'=>'play.php?game=sky-pantheon','theme'=>'pantheon','icon'=>'⚡','accent'=>'linear-gradient(135deg,#3854b8,#7ac8ff 48%,#f0c45d)','cols'=>6,'rows'=>5,'volatility'=>'Очень высокая',
            'symbols'=>['laurel'=>['icon'=>'L','name'=>'Лавровый медальон'],'chalice'=>['icon'=>'C','name'=>'Небесная чаша'],'harp'=>['icon'=>'H','name'=>'Золотая арфа'],'ring'=>['icon'=>'R','name'=>'Кольцо неба'],'wing'=>['icon'=>'W','name'=>'Крыло'],'thunder'=>['icon'=>'T','name'=>'Молния'],'scatter'=>['icon'=>'G','name'=>'Врата-бонус']],
            'rules'=>'Оригинальная небесная tumble-механика: 10+ одинаковых символов платят в любом месте сетки и исчезают. Редкие сферы дают множители ×2–×100 только вместе с выигрышем. 4+ Врат запускают фриспины, где собранные сферы накапливают Небесный заряд между вращениями.'
        ],
    ];
}

function game_config(string $key): ?array {$all=game_catalog();return $all[$key]??null;}
