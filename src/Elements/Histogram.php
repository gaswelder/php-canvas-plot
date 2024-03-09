<?php

namespace gaswelder\plot;

class Histogram extends Element
{
	private $bins;
	private $columnColor = Defaults::Color;

	/**
	 * @param array $series List of numbers
	 * @param int $columnsNumber Number of columns
	 */
	static function make($series, $columnsNumber = null)
	{
		if (!$columnsNumber) {
			$columnsNumber = max(ceil(sqrt(count($series))), 4);
		}
		[$min, $max, $tick] = Ticker::chooseBounds(Coordinate::parse(min($series)), Coordinate::parse(max($series)));
		$min = $min->num;
		$max = $max->num;

		$min -= $tick;
		$max += $tick;
		$step = ($max - $min) / $columnsNumber;
		$ranges = [];
		$x = $min;
		while ($x <= $max) {
			$ranges[] = ['min' => $x, 'max' => $x + $step, 'count' => 0];
			$x += $step;
		}
		foreach ($series as $val) {
			$i = floor(($val - $min) / $step);
			$ranges[$i]['count']++;
		}
		$n = count($ranges);
		while ($n > 0 && $ranges[$n - 1]['count'] == 0) {
			array_pop($ranges);
			$n--;
		}

		$hist = new Histogram;
		$hist->bins = $ranges;
		return $hist;
	}

	function columnColor($color)
	{
		$this->columnColor = $color;
		return $this;
	}

	function render()
	{
		$style = ['stroke-color' => $this->columnColor];
		return array_map(function ($bin) use ($style) {
			$p1 = [$bin['min'], 0];
			$p2 = [$bin['max'], $bin['count']];
			return new Rect($p1, $p2, $style);
		}, $this->bins);
	}
}
