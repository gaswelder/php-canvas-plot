<?php

namespace gaswelder\plot;

/**
 * CanvasArea manages translation from data coordinates to canvas pixels.
 */
class CanvasArea
{
	public $margins = [
		'top' => 10,
		'right' => 10,
		'bottom' => 10,
		'left' => 10
	];

	public $debug = false;
	private $started = false;

	function claimSpace($side, $px)
	{
		if ($this->started) {
			throw new Exception("can't claim margins, already started using the translation");
		}
		$this->margins[$side] += $px;
	}

	public $minDataX;
	public $maxDataX;
	public $minDataY;
	public $maxDataY;

	function hspace($settings)
	{
		return $settings['width'] - $this->margins['left'] - $this->margins['right'];
	}

	function vspace($settings)
	{
		return $settings['height'] - $this->margins['top'] - $this->margins['bottom'];
	}

	function x(Coordinate $x, $settings)
	{
		$this->started = true;
		$hspace = $this->hspace($settings);
		$xnorm = ($x->num - $this->minDataX->num) / ($this->maxDataX->num - $this->minDataX->num);
		return $this->margins['left'] + $hspace * $xnorm;
	}

	function y(Coordinate $y, $settings)
	{
		$this->started = true;
		$vspace = $this->vspace($settings);
		$ynorm = ($this->maxDataY->num - $y->num) / ($this->maxDataY->num - $this->minDataY->num);
		return $this->margins['top'] + $vspace * $ynorm;
	}

	function xmax($settings)
	{
		return $this->x($this->maxDataX, $settings);
	}
	function xmin($settings)
	{
		return $this->x($this->minDataX, $settings);
	}
	function ymax($settings)
	{
		return $this->y($this->maxDataY, $settings);
	}
	function ymin($settings)
	{
		return $this->y($this->minDataY, $settings);
	}
}
