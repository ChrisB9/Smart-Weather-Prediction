<?php

namespace cbenco\Science\Math\Regression;

interface IRegression {
	public function getValueOf(float $newX) : float;
}
