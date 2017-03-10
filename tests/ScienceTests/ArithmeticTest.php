<?php

namespace cbenco\Tests\MathTests;

use cbenco\Science\Math\Arithmetic\BasicOperations;
use PHPUnit\Framework\TestCase;

class ArithmeticTest extends TestCase {
	use BasicOperations;

	public function dataProviderForAddition() {
		return [
			[10, 9, 1],
			[10, 5, 4, 1],
			[20, 10, 5, 4, 1],
			[10, 1, 2, 3, 4]
		];
	}

	/**
	 * @dataProvider dataProviderForAddition
	 */
	public function testAddition(float $result, ...$arguments) {
		$this->assertEquals($result, $this->add(...$arguments));
	}

	public function dataProviderForSubtraction() {
		return [
			[8, 9, 1],
			[0, 5, 4, 1],
			[0, 10, 5, 4, 1],
			[-8, 1, 2, 3, 4]
		];
	}

	/**
	 * @dataProvider dataProviderForSubtraction
	 */
	public function testSubtraction(float $result, ...$arguments) {
		$this->assertEquals($result, $this->subtract(...$arguments));
	}

	public function dataProviderForMultiplication() {
		return [
			[9, 9, 1],
			[20, 5, 4, 1],
			[200, 10, 5, 4, 1],
			[24, 1, 2, 3, 4]
		];
	}

	/**
	 * @dataProvider dataProviderForMultiplication
	 */
	public function testMultiplication(float $result, ...$arguments) {
		$this->assertEquals($result, $this->multiply(...$arguments));
	}

	public function dataProviderForDividation() {
		return [
			[9, 9, 1],
			[1.25, 5, 4, 1],
			[2, 10, 5, 4, 1],
			[0.5, 1, 2, 3, 4]
		];
	}

	/**
	 * @dataProvider dataProviderForDividation
	 */
	public function testDividation(float $result, ...$arguments) {
		$this->assertEquals($result, $this->divide(...$arguments));
	}

	public function dataProviderForCalculatingTheAverageOfADataset() {
		return [
			[5, [9, 1]],
			[3.3333333333333335, [5, 4, 1]],
			[5, [10, 5, 4, 1]],
			[2.5, [1, 2, 3, 4]]
		];
	}

	/**
	 * @dataProvider dataProviderForCalculatingTheAverageOfADataset
	 */
	public function testCalculatingTheAverageOfADataset(float $result, $values) {
		$this->assertEquals($result, $this->getAverage($values));
	}

}