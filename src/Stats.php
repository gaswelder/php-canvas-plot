<?php

namespace gaswelder\plot;

class Stats
{
	/*
	 * Average of the series.
	 */
	static function average($a)
	{
		return array_sum($a) / count($a);
	}

	/*
	 * Median value of the series.
	 */
	static function median($X)
	{
		sort($X);
		$n = count($X);
		$n2 = $n / 2;
		$n2r = round($n / 2);

		if ($n2r == $n2) {
			return ($X[$n2] + $X[$n2 - 1]) / 2;
		} else {
			return $X[$n2];
		}
	}

	/*
	 * Standard deviation.
	 */
	static function SD($a)
	{
		$N = count($a);

		$mean = self::average($a);

		$variance = 0.0;
		for ($i = 0; $i < $N; $i++) {
			$variance += $mean * $mean + $a[$i] * $a[$i] - 2 * $a[$i] * $mean;
		}
		$variance /= ($N - 1);

		return sqrt($variance);
	}

	/*
	 * Covariance between two series of values.
	 */
	static function covariance($X, $Y)
	{
		$mean_xy = $mean_x = $mean_y = 0.0;
		$n = count($X);
		for ($i = 0; $i < $n; $i++) {
			$mean_xy = ($mean_xy * $i + $X[$i] * $Y[$i]) / ($i + 1);
			$mean_x = ($mean_x * $i + $X[$i]) / ($i + 1);
			$mean_y = ($mean_y * $i + $Y[$i]) / ($i + 1);
		}
		return $mean_xy - $mean_x * $mean_y;
	}

	/*
	 * Pearson's product-moment correlation coefficient
	 * between series $a and $b.
	 */
	static function cor($a, $b)
	{
		return self::covariance($a, $b) / self::SD($a) / self::SD($b);
	}


	/*
	 * Linear regression.
	 */
	static function lm($X, $Y)
	{
		// slope
		$b = self::covariance($X, $Y) / self::covariance($X, $X);

		// intercept
		$a = self::average($Y) - $b * self::average($X);

		return [
			'slope' => $b,
			'intercept' => $a,
			'func' => function ($x) use ($a, $b) {
				return $a + $b * $x;
			}
		];
	}

	static function gauss($mean, $sigma2)
	{
		return function ($x) use ($mean, $sigma2) {
			$d = $x - $mean;
			$s2 = $sigma2;
			return 1 / sqrt(2 * deg2rad(180) * $s2) * exp(-($d * $d) / (2 * $s2));
		};
	}
}
