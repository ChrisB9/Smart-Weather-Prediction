<?php

namespace cbenco\Tests\MathTests;

use cbenco\Science\Math\Regression\QuadraticRegression;
use cbenco\Science\Math\Regression\LinearRegression;
use PHPUnit\Framework\TestCase;

class RegressionTests extends TestCase {
	/**
	 * @dataProvider quadraticRegressionProvider
	 */
	public function testQuadraticRegression(array $values, float $newX, float $result) {
		$quadraticRegression = new QuadraticRegression($values);
		$this->assertEquals($result, $quadraticRegression->getValueOf($newX));
	}

	public function quadraticRegressionProvider() {
		return [
			[
				[1 => 0.38, 2 => 1.15, 3 => 2.71, 4 => 3.92, 5 => 5.93, 6 => 8.56, 7 => 11.24],
				64,
				819.67
			],
			[
				[83 => 183, 71 => 168, 64 => 171, 63 => 178, 69 => 176, 61 => 172, 68 => 165, 59 => 158, 81 => 183, 91 => 182, 57 => 163],
				20,
				113.03882207913
			]
		];
	}
	/**
	 * @dataProvider linearRegressionProvider
	 */
	public function testLinearRegression(array $values, float $newX, float $result) {
		$linearRegression = new LinearRegression($values);
		$this->assertEquals($result, $linearRegression->getValueOf($newX));
	}

	public function linearRegressionProvider() {
		return [
			[
				[1 => 0.38, 2 => 1.15, 3 => 2.71, 4 => 3.92, 5 => 5.93, 6 => 8.56, 7 => 11.24],
				64,
				113.31285714286
			],
			[
				[83 => 183, 71 => 168, 64 => 171, 63 => 178, 69 => 176, 61 => 172, 68 => 165, 59 => 158, 81 => 183, 91 => 182, 57 => 163],
				20,
				142.73326216257
			]
		];
	}
}
