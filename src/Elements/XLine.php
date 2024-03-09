<?php

namespace gaswelder\plot;

class XLine extends Element
{
	private $x;

	function __construct($x)
	{
		$this->x = $x;
	}

	function render()
	{
		return [new Line([[$this->x, -INF], [$this->x, INF]])];
	}
}
