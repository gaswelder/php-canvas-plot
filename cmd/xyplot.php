<?php

require_once __DIR__ . "/../src/init.php";

use gaswelder\plot\F;

function get_data($text)
{
    // Assuming the input is two columns: x y.
    $table = [];
    $lines = array_filter(array_map('trim', explode("\n", $text)));
    sort($lines);
    foreach ($lines as $line) {
        if ($line[0] == "#") {
            continue;
        }
        $table[] = preg_split('/\s+/', $line);
    }

    // If this is a single series after all, add index as x.
    if (count($table[0]) == 1) {
        foreach ($table as $i => $row) {
            $table[$i] = [$i, $row[0]];
        }
    }
    return [$table];
}

$blueyellow = ["#115f9a", "#1984c5", "#22a7f0", "#48b5c4", "#76c68f", "#a6d75b", "#c9e52f", "#d0ee11", "#d0f400"];
$orangepurple = ["#ffb400", "#d2980d", "#a57c1b", "#786028", "#363445", "#48446e", "#5e569b", "#776bcd", "#9080ff"];
$series = [];
$text = stream_get_contents(STDIN);

foreach (get_data($text) as $i => $xy) {
    // even numbers iterate through the first palette
    // odd numbers iterate through the second palette
    $color = $i % 2 ?
        $orangepurple[(($i - 1) / 2) % count($orangepurple)] :
        $blueyellow[($i / 2) % count($blueyellow)];
    $series[] = F::XYSeries($xy)->color($color)->lines();
}

$png = F::XYPlot(...$series)->grid()->render();

$title = time();
$path = "$title.png";
file_put_contents($path, $png);
echo "written $path\n";
