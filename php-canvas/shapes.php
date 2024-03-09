<?php

namespace gaswelder;

trait Shapes
{
	/**
	 * Adds an arc to the path. The arc is center at (x, y).
	 *
	 * @param float $x
	 * @param float $y
	 * @param float $radius
	 * @param float $startAngle Radians
	 * @param float $endAngle Radians
	 */
	function arc($x, $y, $radius, $startAngle, $endAngle, $anticlockwise = false)
	{
		$points = $this->arcPoints([$x, $y], $radius, $startAngle, $endAngle, $anticlockwise);
		$points = array_map(function ($pos) {
			return $this->calc($pos[0], $pos[1]);
		}, $points);
		$this->path[] = $points;
	}

	/**
	 * Adds a closed rectangle to the path.
	 *
	 * @param float $x
	 * @param float $y
	 * @param float $width
	 * @param float $height
	 */
	function rect($x, $y, $width, $height)
	{
		$this->moveTo($x, $y);
		$this->lineTo($x + $width, $y);
		$this->lineTo($x + $width, $y + $height);
		$this->lineTo($x, $y + $height);
		$this->lineTo($x, $y);
	}

	/**
	 * Fills a rectangle. Does not affect the current path.
	 *
	 * @param float $x
	 * @param float $y
	 * @param float $width
	 * @param float $height
	 */
	function fillRect($x, $y, $width, $height)
	{
		$points = [
			$this->calc($x + $width, $y),
			$this->calc($x + $width, $y + $height),
			$this->calc($x, $y + $height),
			$this->calc($x, $y)
		];
		$this->polyFill($points);
	}

	function strokeRect($x, $y, $width, $height)
	{
		$this->rect($x, $y, $width, $height);
		$this->stroke();
	}

	function clearRect($x, $y, $width, $height)
	{
		$fs = $this->fillStyle;
		$this->fillStyle = 'white';
		$this->fillRect($x, $y, $width, $height);
		$this->fillStyle = $fs;
	}

	private function arcPoints($center, $radius, $startAngle, $endAngle, $anticlockwise)
	{
		$points = [];
		$angles = [];
		if ($anticlockwise) {
			while ($startAngle <= $endAngle) {
				$startAngle += deg2rad(360);
			}
			$angles = range($startAngle, $endAngle, -0.1);
		} else {
			while ($endAngle <= $startAngle) {
				$endAngle += deg2rad(360);
			}
			$angles = range($startAngle, $endAngle, 0.1);
		}
		foreach ($angles as $angle) {
			$points[] = [
				$center[0] + $radius * cos($angle),
				$center[1] + $radius * sin($angle)
			];
		}
		$points[] = [
			$center[0] + $radius * cos($endAngle),
			$center[1] + $radius * sin($endAngle)
		];
		return $points;
	}
}
