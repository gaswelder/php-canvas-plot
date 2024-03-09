<?php

namespace gaswelder\plot;

class XYSeries extends Element
{
	private $points;
	private $color = 'red';
	private $pointSize = Defaults::PointSize;
	private $lines = false;

	static function make(array $points)
	{
		$xy = new XYSeries;
		$xy->points = array_map(function ($point) {
			$n = count($point);
			if ($n != 2) {
				throw new Exception("expected a 2-value array (point), got $n");
			}
			return [
				Coordinate::parse($point[0]),
				Coordinate::parse($point[1])
			];
		}, $points);;
		return $xy;
	}

	function color($color)
	{
		$this->color = $color;
		return $this;
	}

	function pointSize($size)
	{
		$this->pointSize = $size;
		return $this;
	}

	function withLines()
	{
		$this->lines = true;
	}

	function render()
	{
		$elements = [];

		// Add lines between points
		if ($this->lines) {
			$n = count($this->points);
			for ($i = 0; $i < $n - 1; $i++) {
				$elements[] = ['line', [$this->points[$i], $this->points[$i + 1]]];
			}
		}

		// Add the points on top
		$style = [
			'point-color' => $this->color,
			'point-size' => $this->pointSize,
			'point-type' => 'filled-circle'
		];
		foreach ($this->points as $p) {
			$elements[] = new Point($p, $style);
		}
		return $elements;
	}
}
