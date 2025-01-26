<?php

require_once __DIR__ . "/../src/init.php";

use gaswelder\plot\F;

function get_data($text)
{
    $table = [];
    $lines = array_filter(array_map('trim', explode("\n", $text)));
    foreach ($lines as $line) {
        if ($line[0] == "#") {
            continue;
        }
        $table[] = preg_split('/\s+/', $line);
    }

    $single = count($table[0]) == 1;

    $series = [];
    if ($single) {
        foreach ($table as $i => $row) {
            $x = $i;
            foreach ($row as $j => $col) {
                $series[$j][] = [$x, $col];
            }
        }
    } else {
        foreach ($table as $row) {
            $x = $row[0];
            for ($j = 1; $j < count($row); $j++) {
                $series[$j - 1][] = [$x, $row[$j]];
            }
        }
    }

    return $series;
}

function fail($msg)
{
    fprintf(STDERR, $msg . "\n");
    exit(1);
}

array_shift($argv);
$title = null;
$ytitle = null;
$xtitle = null;

$flags = [
    '-t' => ['val' => &$title, 'desc' => 'plot title'],
    '-x' => ['val' => &$xtitle, 'desc' => 'x title'],
    '-y' => ['val' => &$ytitle, 'desc' => 'y title'],
];

while (count($argv) > 0) {
    $x = array_shift($argv);
    $spec = $flags[$x] ?? null;
    if (!$spec) {
        fail("unknown argument: $x");
    }
    if (array_key_exists('val', $spec)) {
        $val = array_shift($argv);
        if ($val === null) {
            fail("$x flag expects an argument");
        }
        $spec['val'] = $val;
    } else {
        $spec['f'];
    }
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

$plot = F::XYPlot(...$series)->grid();
if ($title !== null) {
    $plot->title($title);
}
if ($xtitle !== null) {
    $plot->xtitle($xtitle);
}
if ($ytitle !== null) {
    $plot->ytitle($ytitle);
}

$name = time();
$path = "$name.png";
file_put_contents($path, $plot->render());
echo "$path\n";
