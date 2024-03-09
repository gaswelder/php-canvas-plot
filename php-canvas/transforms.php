<?php

namespace gaswelder;

trait Transforms
{
	/*
	 * Current transformation matrix.
	 */
	protected $matrix = [[1, 0, 0], [0, 1, 0], [0, 0, 1]];

	/*
	 * Applies current transformation matrix to the given
	 * coordinates. Returns the result as an array [x, y].
	 */
	protected function calc($x, $y)
	{
		$m = $this->matrix;
		return [
			$m[0][0] * $x + $m[0][1] * $y + $m[0][2] * 1,
			$m[1][0] * $x + $m[1][1] * $y + $m[0][2] * 1
		];
	}

	function setTransform($a, $b, $c, $d, $e, $f)
	{
		$this->matrix = [[$a, $c, $e], [$b, $d, $f], [0, 0, 1]];
	}

	function transform($a, $b, $c, $d, $e, $f)
	{
		$current = $this->matrix;
		$multiplier = [
			[$a, $c, $e],
			[$b, $d, $f],
			[0, 0, 1]
		];
		$result = [[0, 0, 0], [0, 0, 0], [0, 0, 0]];
		for ($i = 0; $i < 3; $i++) {
			for ($j = 0; $j < 3; $j++) {
				for ($k = 0; $k < 3; $k++) {
					$result[$i][$j] += $current[$i][$k] * $multiplier[$k][$j];
				}
			}
		}
		$this->matrix = $result;
	}

	/**
	 * Translates the current coordinate system by the given vector.
	 *
	 * @param float $x
	 * @param float $y
	 */
	function translate($x, $y)
	{
		$this->transform(1, 0, 0, 1, $x, $y);
	}

	/**
	 * Rotates the current coordinate system by the given angle in clockwise radians.
	 *
	 * @param float $angle
	 */
	function rotate($angle)
	{
		$angle *= -1;
		$this->transform(cos($angle), -sin($angle), sin($angle), cos($angle), 0, 0);
	}
}
