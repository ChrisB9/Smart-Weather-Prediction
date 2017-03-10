<?php

namespace cbenco\Science\Math\Arithmetic;

trait BasicOperations
{
    private function subtract(...$arguments): float {
		if (count($arguments) < 2) {
			throw new \Exception("Too few arguments");
		}
		$res = $arguments[0];
		for ($i = 1; $i < count($arguments); $i++) {
			$res -= $arguments[$i];
		}
		return $res;
	}

	private function add(...$arguments): float {
		return array_sum($arguments);
	}

	public function divide(float $numerator,float $denumerator): float {
		return $numerator / $denumerator;
	}

	public function multiply(...$arguments) {
		$num = 1;
		foreach ($arguments as $multiplier) {
			$num *= $multiplier;
		}
		return $num;
	}

	public function getAverage(array $values): float {
		return array_sum($values) / count($values);
	}
}