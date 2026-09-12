<?php
declare(strict_types=1);

function game_catalog(): array {
    return [
        'sweet-cascade' => [
            'title'=>'Сладкий каскад','subtitle'=>'Кластеры • каскады • множители','route'=>'game.php','theme'=>'candy','icon'=>'🍬','accent'=>'linear-gradient(135deg,#7b42df,#ef4cb8 55%,#4acbf3)','cols'=>7,'rows'=>7,
        ],
        'fruit-fiesta' => [
            'title'=>'Фруктовая фиеста','subtitle'=>'20 линий • дикие символы • липкие вайлды','route'=>'play.php?game=fruit-fiesta','theme'=>'fruit','icon'=>'🍒','accent'=>'linear-gradient(135deg,#f05a28,#ffcf3e 50%,#72df59)','cols'=>5,'rows'=>4,
            'symbols'=>['cherry'=>['icon'=>'🍒','name'=>'Вишня'],'lemon'=>['icon'=>'🍋','name'=>'Лимон'],'grape'=>['icon'=>'🍇','name'=>'Виноград'],'bell'=>['icon'=>'🔔','name'=>'Колокол'],'seven'=>['icon'=>'7️⃣','name'=>'Семёрка'],'diamond'=>['icon'=>'💎','name'=>'Бриллиант'],'wild'=>['icon'=>'⭐','name'=>'Вайлд'],'scatter'=>['icon'=>'🎁','name'=>'Бонус']],
            'rules'=>'Выигрыши считаются по 20 линиям слева направо. Вайлд заменяет обычные символы. 3+ бонуса запускают фриспины; во фриспинах выпавшие вайлды становятся липкими до конца серии.'
        ],
        'temple-ways' => [
            'title'=>'Храм тысячи путей','subtitle'=>'Пути слева • каскады • растущий множитель','route'=>'play.php?game=temple-ways','theme'=>'temple','icon'=>'🗿','accent'=>'linear-gradient(135deg,#0f5d52,#d39a32 55%,#7a3f1f)','cols'=>6,'rows'=>5,
            'symbols'=>['mask'=>['icon'=>'🎭','name'=>'Маска'],'idol'=>['icon'=>'🗿','name'=>'Идол'],'bird'=>['icon'=>'🦜','name'=>'Птица'],'snake'=>['icon'=>'🐍','name'=>'Змея'],'gem'=>['icon'=>'💚','name'=>'Самоцвет'],'torch'=>['icon'=>'🔥','name'=>'Факел'],'wild'=>['icon'=>'🌞','name'=>'Солнце-вайлд'],'scatter'=>['icon'=>'🏺','name'=>'Амфора-бонус']],
            'rules'=>'Одинаковые символы платят на соседних барабанах, начиная с первого. После выигрыша символы исчезают и запускают каскад. Каждый следующий каскад повышает множитель. Во фриспинах множитель переносится между вращениями.'
        ],
        'jungle-hold' => [
            'title'=>'Джунгли: Золотой тотем','subtitle'=>'Классические линии • монеты • hold-and-respin','route'=>'play.php?game=jungle-hold','theme'=>'jungle','icon'=>'🪙','accent'=>'linear-gradient(135deg,#124f35,#d7a62d 55%,#7b3f19)','cols'=>5,'rows'=>3,
            'symbols'=>['parrot'=>['icon'=>'🦜','name'=>'Попугай'],'tiger'=>['icon'=>'🐯','name'=>'Тигр'],'leaf'=>['icon'=>'🌿','name'=>'Лист'],'drum'=>['icon'=>'🪘','name'=>'Барабан'],'gem'=>['icon'=>'💎','name'=>'Камень'],'wild'=>['icon'=>'🗿','name'=>'Тотем-вайлд'],'coin'=>['icon'=>'🪙','name'=>'Золотая монета']],
            'rules'=>'Базовая игра использует линии. 6+ золотых монет запускают 3 респина: монеты фиксируются, новый символ монеты возвращает счётчик респинов к трём. Заполнение всего поля даёт дополнительный бонус.'
        ],
        'crystal-clusters' => [
            'title'=>'Кристальный реактор','subtitle'=>'8×8 • кластеры • бомбы • цепные взрывы','route'=>'play.php?game=crystal-clusters','theme'=>'crystal','icon'=>'💠','accent'=>'linear-gradient(135deg,#16486f,#744ed8 48%,#e94cb7)','cols'=>8,'rows'=>8,
            'symbols'=>['ruby'=>['icon'=>'🔴','name'=>'Рубин'],'emerald'=>['icon'=>'🟢','name'=>'Изумруд'],'sapphire'=>['icon'=>'🔵','name'=>'Сапфир'],'amethyst'=>['icon'=>'🟣','name'=>'Аметист'],'sunstone'=>['icon'=>'🟡','name'=>'Солнечный камень'],'bomb'=>['icon'=>'💣','name'=>'Кристальная бомба'],'scatter'=>['icon'=>'⚛️','name'=>'Реактор-бонус']],
            'rules'=>'Кластеры из 5+ одинаковых кристаллов исчезают. Бомбы рядом с выигрышем взрывают область 3×3 и добавляют бонус к каскаду. 4+ символа реактора дают серию фриспинов с повышенной частотой бомб.'
        ],
        'sun-scroll' => [
            'title'=>'Свиток солнца','subtitle'=>'10 линий • фриспины • расширяющийся символ','route'=>'play.php?game=sun-scroll','theme'=>'desert','icon'=>'📜','accent'=>'linear-gradient(135deg,#c87825,#f1c55c 52%,#7f3e32)','cols'=>5,'rows'=>3,
            'symbols'=>['falcon'=>['icon'=>'🦅','name'=>'Сокол'],'scarab'=>['icon'=>'🪲','name'=>'Скарабей'],'lotus'=>['icon'=>'🪷','name'=>'Лотос'],'ankh'=>['icon'=>'☥','name'=>'Анкх'],'crown'=>['icon'=>'👑','name'=>'Корона'],'wild'=>['icon'=>'☀️','name'=>'Солнце-вайлд'],'scatter'=>['icon'=>'📜','name'=>'Свиток-бонус']],
            'rules'=>'3+ свитка запускают фриспины и выбирают один обычный символ. Во время бонуса, если выбранный символ появляется на барабане, он расширяется на весь барабан перед подсчётом линий.'
        ],
        'neon-rush' => [
            'title'=>'Неоновый разгон','subtitle'=>'5×5 • пути • tumble • напряжение до ×10','route'=>'play.php?game=neon-rush','theme'=>'neon','icon'=>'⚡','accent'=>'linear-gradient(135deg,#11164d,#7728d7 45%,#12d9e6)','cols'=>5,'rows'=>5,
            'symbols'=>['cyan'=>['icon'=>'◆','name'=>'Циан'],'pink'=>['icon'=>'●','name'=>'Розовый импульс'],'lime'=>['icon'=>'▲','name'=>'Лаймовый импульс'],'violet'=>['icon'=>'⬢','name'=>'Фиолетовый импульс'],'orange'=>['icon'=>'■','name'=>'Оранжевый импульс'],'wild'=>['icon'=>'⚡','name'=>'Энерго-вайлд'],'scatter'=>['icon'=>'🔋','name'=>'Батарея-бонус']],
            'rules'=>'Выигрыши идут по путям слева направо. Каждый tumble увеличивает напряжение и множитель. 3+ батареи запускают фриспины, где напряжение не сбрасывается между вращениями и может достигать ×10.'
        ],
    ];
}

function game_config(string $key): ?array {
    $all=game_catalog();
    return $all[$key] ?? null;
}
