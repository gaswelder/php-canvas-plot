<?php

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../init.php";

use gaswelder\plot\XYPlot;
use gaswelder\plot\XYSeries;

function get_data($text)
{
    $table = [];
    $lines = array_filter(array_map('trim', explode("\n", $text)));
    $x = 0;
    foreach ($lines as $line) {
        if ($line[0] == "#") {
            continue;
        }
        $cols = preg_split('/\s+/', $line);
        $table[] = array_merge([$x++], $cols);
    }
    $n = count($table[0]) - 1;
    $series = [];
    for ($i = 0; $i < $n; $i++) {
        $series[$i] = [];
        foreach ($table as $row) {
            $series[$i][] = array($row[0], $row[$i + 1]);
        }
    }
    return $series;
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
    $s = new XYSeries($xy);
    $s->color($color);
    $series[] = $s;
}

$png = XYPlot::make($series)->grid()->title('Sample plot')->render();

$title = time();
$path = "$title.png";
file_put_contents($path, $png);
echo "written $path\n";
