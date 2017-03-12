<?php

namespace cbenco\Science\Math\Regression;
use cbenco\Science\Math\Arithmetic;

class LinearRegression implements IRegression {
	use Arithmetic\BasicOperations;

	private $dataset;
	private $averageX;
	private $averageY;
	private $correlationCoefficient;
	private $parameterM;
	private $parameterN;
	private $parameterS;

	public function __construct(array $dataset) {
		$this->dataset = $dataset;
		$this->init();
	}

	private function init() {
		$this->averageX = $this->getAverage(array_keys($this->dataset));
		$this->averageY = $this->getAverage($this->dataset);
		$this->setParameterS();
		$this->setParameterM();
		$this->setParameterN();
	}

	public function setParameterS() {
		$this->parameterS = [
			"x" => $this->averageX,
			"y" => $this->averageY,
			"xx" => $this->getAverage(array_map(function($xval) {return pow($xval, 2);}, array_keys($this->dataset))),
			"xy" => $this->getAverage(array_map(function ($xval, $yval) {
				return $this->multiply($xval, $yval);
			}, array_keys($this->dataset), $this->dataset))
		];
	}

	public function getParameterS(string $index) : float {
		return $this->parameterS[$index];
	}

	public function setParameterM() {
		$this->parameterM = $this->divide(
			$this->subtract(
				$this->getParameterS("xy"),
				$this->multiply(
					$this->getParameterS("y"),
					$this->getParameterS("x")
				)
			),
			$this->subtract(
				$this->getParameterS("xx"),
				pow($this->getParameterS("x"), 2)
			)
		);
	}

	public function getParameterM() : float {
		return $this->parameterM;
	}

	public function setParameterN() {
		$this->parameterN = $this->subtract(
			$this->getParameterS("y"),
			$this->multiply(
				$this->getParameterM(),
				$this->getParameterS("x")
			)
		);
	}

	public function getParameterN() : float {
		return $this->parameterN;
	}

	public function getValueOf(float $newX) : float
	{
	    // y = f(x) = mx+n
	    return $this->add(
	    	$this->multiply(
	    		$this->getParameterM(),
	    		$newX
	    	),
	    	$this->getParameterN()
	    );
	}
}