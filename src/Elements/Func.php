<?php

namespace gaswelder\plot;

class Func extends Element
{
	private $func;
	private $range;
	private $color = Defaults::Color;
	private $pointSize = Defaults::PointSize;

	function __construct($func, $a, $b)
	{
		$this->func = $func;
		$this->range = [$a, $b];
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

	private function calc($x)
	{
		return call_user_func($this->func, $x);
	}

	private function xy()
	{
		list($a, $b) = $this->range;
		$points = range($a, $b, ($b - $a) / 100);

		$xy = [];
		foreach ($points as $x) {
			$xy[] = [$x, $this->calc($x)];
		}
		return $xy;
	}

	function render()
	{
		$xy = $this->xy();
		$elements = [new Line($xy)];
		if ($this->pointSize > 0) {
			$style = [
				'point-size' => $this->pointSize,
			];
			foreach ($xy as $p) {
				$elements[] = (new Point($p, $style))->filled()->color($this->color);
			}
		}
		return $elements;
	}
}
