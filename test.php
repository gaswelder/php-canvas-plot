<?php

require 'src/init.php';

use gaswelder\plot\F;
use gaswelder\plot\Stats;

function read_csv($path)
{
	$data = [];
	$f = fopen($path, 'rb');
	$header = fgetcsv($f);
	while (true) {
		$row = fgetcsv($f);
		if (!$row) break;
		$data[] = array_combine($header, $row);
	}
	fclose($f);
	return $data;
}

function zip($X, $Y)
{
	$xy = [];
	foreach ($X as $i => $x) {
		$xy[] = [$x, $Y[$i]];
	}
	return $xy;
}

function get_data($path)
{
	$lines = array_filter(array_map('trim', file($path)));
	$data = array();
	foreach ($lines as $line) {
		$data[] = explode("\t", $line);
	}

	$n = count($data[0]) - 1;

	$series = array();
	for ($i = 0; $i < $n; $i++) {
		$series[$i] = array();
		foreach ($data as $row) {
			$series[$i][] = array($row[0], $row[$i + 1]);
		}
	}

	return $series;
}

foreach (glob("images/*.png") as $path) {
	unlink($path);
}

function test($name, $func)
{
	error_log($name);
	$png = $func();
	file_put_contents("images/$name.png", $png);
}

test('functions', function () {
	return F::XYPlot(
		F::Func(Stats::gauss(0, 0.2), -6, 6),
		F::Func(Stats::gauss(0, 1), -6, 6),
		F::Func(Stats::gauss(0, 5), -6, 6),
		F::Func(Stats::gauss(-2, .5), -6, 6)->color('red')->pointSize(4)
	)->grid()->title('Normal Distribution')->render();
});

test('histograms', function () {
	$n = 1000;
	$data = [];
	for ($i = 0; $i < $n; $i++) {
		$data[] = rand(0, 100);
	}
	return F::XYPlot(F::Hist($data, 10))->title("$n rand calls")->render();
});

test('linear-regression', function () {
	$data = read_csv('test/honda-civic.csv');
	$x = [];
	$y = [];
	foreach ($data as $item) {
		$x[] = $item['year'];
		$y[] = $item['mileage'];
	}
	$xy = zip($x, $y);
	$fit = Stats::lm($x, $y);

	return F::XYPlot(
		F::XYSeries($xy)->color('blue')->pointSize(10),
		F::dataline($fit['intercept'], $fit['slope'], 2010, 2016),
		F::label("$fit[slope] / year", [2015, 170000])
	)
		->grid()
		->xtitle('Year')
		->ytitle('Mileage')
		->render();
});

test('lots-of-measurements', function () {
	$data = array_filter(array_map(function ($line) {
		$k = sscanf($line, "%d %d %f %f", $depth, $age, $deltaD, $deg);
		return $k == 4 ? [$age, $deg] : null;
	}, file('test/deuterium.txt')));
	return F::XYPlot(F::XYSeries($data)->pointSize(4))->grid()->render();
});

// test('out', function () {
// 	$mean = 0.6;
// 	$variance = 0.01;
// 	$data = [];
// 	for ($i = 0; $i < 500; $i++) {
// 		$u = mt_rand() / mt_getrandmax();
// 		$v = mt_rand() / mt_getrandmax();
// 		$bm = sqrt(-2 * log($u)) * cos(2 * M_PI * $v);
// 		$data[] = $bm * sqrt($variance) + $mean;
// 	}
// 	return F::XYPlot(
// 		F::Hist($data)->columnColor('red'),
// 		F::xline(0.6)
// 	)->fullBorder()->ticLength(-10)->subticLength(-5)->xtitle('talent')->ytitle('Number of Individuals')->render();
// });

test('series', function () {
	$series = get_data('test/data');
	return F::XYPlot(
		F::XYSeries($series[0])->color('red'),
		F::XYSeries($series[1])->color('green'),
		F::XYSeries($series[2])->color('blue')
	)->grid(true)->title('Sample Plot')->render();
});

test('polmon', function () {
	$xy = [
		['2024-01-17', 20.3],
		['2024-01-18', 19.3],
		['2024-01-19', 19.0],
		['2024-01-22', 18.5],
		['2024-01-23', 17.6],
		['2024-01-24', 17.2],
		['2024-01-25', 16.7],
		['2024-01-28', 16.4],
		['2024-01-29', 15.3],
		['2024-01-30', 15.0],
		['2024-01-31', 14.6],
		['2024-01-31', 14.6],
		['2024-02-02', 13.7],
		['2024-02-04', 13.4],
		['2024-02-05', 13.2],
		['2024-02-06', 13.2],
		['2024-02-07', 12.3],
		['2024-02-08', 11.8],
		['2024-02-11', 10.8],
		['2024-02-12', 10.7],
		['2024-02-13', 10.0],
		['2024-02-14', 9.8],
		['2024-02-18', 8.9],
		['2024-02-18', 8.4],
		['2024-02-19', 8.0],
		['2024-02-20', 7.6],
		['2024-02-22', 7.2],
		['2024-02-24', 6.4],
		['2024-02-25', 5.5],
		['2024-02-26', 5.0],
		['2024-02-27', 4.7],
		['2024-02-28', 3.9],
		['2024-02-29', 3.1],
		['2024-03-01', 1.9],
		['2024-03-02', 0.0]
	];
	return F::XYPlot(F::XYSeries($xy))->title("Burndown vs date")->render();
});

$doc = file_get_contents('README.md');
$p1 = strpos($doc, '<!-- output -->');
$p2 = strpos($doc, '<!-- /output -->');

$output = implode("\n", array_map(
	function ($path) {
		return "<img width=\"400\" src=\"$path\"/>\n";
	},
	glob("images/*.png")
));

$doc2 = substr($doc, 0, $p1 + strlen('<!-- output -->'))
	. "\n" . $output . substr($doc, $p2);

file_put_contents('README.md', $doc2);
