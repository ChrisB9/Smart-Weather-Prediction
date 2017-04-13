<?php

namespace cbenco\Science\Math\Regression;

class QuadraticRegression implements IRegression {
	use \cbenco\Science\Math\Arithmetic\BasicOperations;
	private $dataset;
	private $averageX;
	private $averageY;
	private $correlationCoefficient;
	private $parameterA;
	private $parameterB;
	private $parameterC;
	private $parameterS;

	public function __construct($dataset) {
		$this->dataset = $dataset;
		$this->init();
	}
	private function init() {
		$this->averageX = $this->getAverage(array_keys($this->dataset));
		$this->averageY = $this->getAverage($this->dataset);
		$this->setParameterS($this->dataset);
		$this->setParameterC();
		$this->setParameterB();
		$this->setParameterA();
		$this->setCorrelationCoefficient();
	}

	private function setParameterA() {
		// function: y-Bx-Cx^2
		// 
		$this->parameterA = $this->subtract(
			$this->getAverage($this->dataset), 
			$this->multiply(
				$this->getParameterB(),
				$this->getAverage(array_keys($this->dataset))
			),
			$this->multiply(
				$this->getParameterC(),
				$this->getAverage(
					array_map(function ($num) {
						return pow($num, 2);
					}, array_keys($this->dataset))
				)
			)
		);
	}

	private function setParameterB() {
		// function:
		//      Sxy * Sx^2x^2 - Sx^2y * Sxx^2
		// B = -------------------------------
		//      Sxx * Sx^2x^2 - (Sxx^2)^2
		//      
		$this->parameterB = $this->divide(
			$this->subtract(
				$this->multiply(
					$this->parameterS["xy"],
					$this->parameterS["x2x2"]
				),
				$this->multiply(
					$this->parameterS["x2y"],
					$this->parameterS["xx2"]
				)
			),
			$this->subtract(
				$this->multiply(
					$this->parameterS["xx"],
					$this->parameterS["x2x2"]
				),
				pow($this->parameterS["xx2"], 2)
			)
		);
	}

	private function setParameterC() {
		// function:
		//      Sx2y * Sxx - Sxy * Sxx^2
		// C = -------------------------------
		//      Sxx * Sx^2x^2 - (Sxx^2)^2
		//      
		$this->parameterC = $this->divide(
			$this->subtract(
				$this->multiply(
					$this->parameterS["x2y"],
					$this->parameterS["xx"]
				),
				$this->multiply(
					$this->parameterS["xy"],
					$this->parameterS["xx2"]
				)
			),
			$this->subtract(
				$this->multiply(
					$this->parameterS["xx"],
					$this->parameterS["x2x2"]
				),
				pow($this->parameterS["xx2"], 2)
			)
		);
	}

	public function getParameterA() {
		return $this->parameterA;
	}

	public function getParameterB() {
		return $this->parameterB;
	}

	public function getParameterC() {
		return $this->parameterC;
	}

	public function getCorrelationCoefficient() {
		return $this->correlationCoefficient;
	}

	private function setCorrelationCoefficient() {
		$numerator = array_sum(array_map(function ($xval, $yval) {
			return pow($this->subtract(
				$yval,
				$this->add(
					$this->getParameterA(),
					$this->multiply(
						$this->getParameterB(),
						$xval
					),
					$this->multiply(
						$this->getParameterC(),
						pow($xval, 2)
					)
				)
			), 2);
		}, array_keys($this->dataset), $this->dataset));
		$denumerator = array_sum(array_map(function($yval) {
			return pow($this->subtract(
				$yval, $this->averageY
			), 2);
		}, $this->dataset));
		$this->correlationCoefficient = sqrt(1 - $this->divide($numerator, $denumerator));
	}

	private function setParameterS(array $dataset) {
		$dataSetPow = array_map(function ($num) {
						return pow($num, 2);
					}, array_keys($this->dataset));
		$this->parameterS = [
			"xx" => $this->getParameterS(array_combine(array_keys($this->dataset), array_keys($this->dataset))),
			"xy" => $this->getParameterS(array_combine(array_keys($this->dataset), $this->dataset)),
			"xx2" => $this->getParameterS(array_combine(array_keys($this->dataset), $dataSetPow)),
			"x2x2" => $this->getParameterS(array_combine($dataSetPow, $dataSetPow)),
			"x2y" => $this->getParameterS(array_combine($dataSetPow, $this->dataset))
		];
	}

	private function getParameterS(array $dataset) {
		$tmp = [];
		foreach ($dataset as $key => $value) {
			if (!is_numeric($key) && !is_numeric($value)) {
				throw new \Exception("dataset has to be numeric");
			}
			$tmp[] = (float) $key * (float) $value;
		}
		$minuend = $this->getAverage($dataset) * $this->getAverage(array_keys($dataset));
		return (array_sum($tmp) / count($dataset)) - $minuend;
	}

	public function getValueOf(float $newX) : float {
		return $this->add(
			$this->getParameterA(),
			$this->multiply(
				$this->getParameterB(), $newX
			),
			$this->multiply(
				$this->getParameterC(),
				pow($newX, 2)
			)
		);
	}

}
