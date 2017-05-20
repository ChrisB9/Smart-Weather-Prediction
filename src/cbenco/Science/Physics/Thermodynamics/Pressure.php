<?php

namespace cbenco\Science\Physics\Thermodynamics;

use cbenco\Science\Math\Arithmetic\BasicOperations;

class Pressure {

	use BasicOperations;

	private $partialPressure;
	private $atomDensity;
	private $temperature;
	private $molGasConstant;
	private $volume;
	private $mass;
	private $mol;

	public function __construct(float $atomDensity,float $volume,float $mass, float $mol, float $temperature)
	{
		$this->atomDensity = $atomDensity;
		$this->volume = $volume;
		$this->mass = $mass;
		$this->mol = $mol;
		$this->temperature = $temperature;
		$this->setMolGasConstant();
	}

	public function getIdealGasEquation() {
		return $this->multiply(
			$this->atomDensity,
			$this->getMolGasContant(),
			$this->temperature
		);
	}

	public function setPartialPressureWithHumidity(Humidity $humidity) {
		$this->partialPressure = $this->calcPartialPressureWithHumidity($humidity);
	}

	public function getPartialPressure() {
		return $this->partialPressure;
	}

	public function calcPartialPressureWithHumidity(Humidity $humidity) {
		return $this->multiply(
			$this->divide(
				$this->getMolGasContant(),
				$this->mol
			),
			$humidity->getAbsoluteHumidity(),
			$this->temperature
		);
	}

	public function getMolGasContant() {
		return $this->molGasConstant;
	}

	public function setMolGasConstant() {
		$this->molGasConstant = $this->divide(
			$this->mass,
			$this->mol
		);
	}

	public function setPartialPressure() {
		$this->partialPressure = $this->multiply(
			$this->divide(1, $this->volume),
			$this->getIdealGasEquation()
		);
	}

}