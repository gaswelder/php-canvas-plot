<?php

require_once __DIR__ . "/../src/init.php";

use gaswelder\plot\F;

function parse_table($text)
{
    $rows = [];
    $n = 0;
    foreach (explode("\n", $text) as $line) {
        $line = trim($line);
        if ($line == "" || $line[0] == "#") {
            continue;
        }
        $cols = preg_split('/\s+/', $line);
        if ($n > 0 && count($cols) != count($rows[$n - 1])) {
            throw new Exception("Inconsistent columns number");
        }
        $rows[] = $cols;
        $n++;
    }
    return $rows;
}

function get_series()
{
    $text = stream_get_contents(STDIN);
    $rows = parse_table($text);

    $subtables = [];
    if (count($rows[0]) == 1) {
        foreach ($rows as $i => $row) {
            $x = $i;
            foreach ($row as $j => $col) {
                $subtables[$j][] = [$x, $col];
            }
        }
    } else {
        foreach ($rows as $row) {
            $x = $row[0];
            for ($j = 1; $j < count($row); $j++) {
                $subtables[$j - 1][] = [$x, $row[$j]];
            }
        }
    }

    $blueyellow = ["#115f9a", "#1984c5", "#22a7f0", "#48b5c4", "#76c68f", "#a6d75b", "#c9e52f", "#d0ee11", "#d0f400"];
    $orangepurple = ["#ffb400", "#d2980d", "#a57c1b", "#786028", "#363445", "#48446e", "#5e569b", "#776bcd", "#9080ff"];
    $series = [];

    foreach ($subtables as $i => $xy) {
        // even numbers iterate through the first palette
        // odd numbers iterate through the second palette
        $color = $i % 2 ?
            $orangepurple[(($i - 1) / 2) % count($orangepurple)] :
            $blueyellow[($i / 2) % count($blueyellow)];
        $xy = F::XYSeries($xy)->color($color);
        $series[] = $xy;
    }
    return $series;
}

function fail($msg)
{
    fprintf(STDERR, $msg . "\n");
    exit(1);
}

function usage($flags)
{
    foreach ($flags as $k => $v) {
        echo "\t", $k, "\t", $v['desc'], "\n";
    }
}

array_shift($argv);
$title = null;
$ytitle = null;
$xtitle = null;
$filepath = null;
$size = "600x340";
$linewidth = 1;

$flags = [
    '-t' => ['val' => &$title, 'desc' => 'plot title'],
    '-x' => ['val' => &$xtitle, 'desc' => 'x title'],
    '-y' => ['val' => &$ytitle, 'desc' => 'y title'],
    '-o' => ['val' => &$filepath, 'desc' => 'output file; if omitted, a random path will be generated and printed'],
    '-s' => ['val' => &$size, 'desc' => 'image size (600x340)'],
    '-l' => ['val' => &$linewidth, 'desc' => 'line width (only 0 or 1 is supported)'],
];

while (count($argv) > 0) {
    $x = array_shift($argv);
    if ($x == '-h') {
        usage($flags);
        exit(1);
    }
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

$series = get_series();
if ($linewidth) {
    foreach ($series as $xy) {
        $xy->lines();
    }
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

$printpath = false;
if (!$filepath) {
    $filepath = time() . ".png";
    $printpath = true;
}

[$w, $h] = array_map('intval', explode('x', $size, 2));

file_put_contents($filepath, $plot->render($w, $h));
if ($printpath) {
    echo "$filepath\n";
}
