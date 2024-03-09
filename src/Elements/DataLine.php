<?php

namespace gaswelder\plot;

class DataLine extends Element
{
	private $a;
	private $b;
	private $minX;
	private $maxX;

	function __construct($a, $b, $minX, $maxX)
	{
		$this->a = $a;
		$this->b = $b;
		$this->minX = $minX;
		$this->maxX = $maxX;
	}

	function render()
	{
		$y1 = $this->a + $this->b * $this->minX;
		$y2 = $this->a + $this->b * $this->maxX;
		return [new Line([[$this->minX, $y1], [$this->maxX, $y2]])];
	}
}
