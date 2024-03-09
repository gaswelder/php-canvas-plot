<?php

namespace gaswelder\plot;

class Ticker
{
	static function makeTics(Coordinate $min, Coordinate $max)
	{
		[$min, $max, $step] = self::chooseBounds($min, $max);

		$tics = [];
		$x = $min;
		while (true) {
			$tics[] = $x;
			if ($x->num >= $max->num) break;
			$x = $x->add($step);
		}
		return $tics;
	}

	static function subtics($values)
	{
		$n = count($values);
		$s = [];
		for ($i = 1; $i < $n; $i++) {
			$s[] = Coordinate::parse(($values[$i]->num + $values[$i - 1]->num) / 2);
		}
		return $s;
	}

	static function chooseBounds(Coordinate $va, Coordinate $vb)
	{
		$a = $va->num;
		$b = $vb->num;
		if ($a > $b) {
			return self::chooseBounds($b, $a);
		}

		// Look at the largest exponent that's different between the two values.
		$exponent = $vb->maxExponent();
		while ($vb->digitAt($exponent) == $va->digitAt($exponent)) {
			$exponent--;
			// sanity check
			if ($exponent < -50) break;
		}

		// After choosing the differing exponent we will get between 1 step or
		// more between the values (1..10 for decimals).
		// If there are to few steps, we'll "switch gears" and use the next
		// exponent: (1,100 -> 0, 10, 20, 30, ..., 100).
		if ($vb->digitAt($exponent) - $va->digitAt($exponent) < 4) {
			$exponent--;
		}

		$left = $va->left($exponent);
		$right = $vb->right($exponent);

		// If after choosing the exponent the number of steps is too high,
		// increase the step.
		$step = $left->step($exponent);
		while (($right->num - $left->num) / $step > 10) {
			$step *= 2;
		}

		return [$left, $right, $step];
	}
}
