<?php

namespace cbenco\Science\Physics\Thermodynamics;

use cbenco\Science\Math\Arithmetic\BasicOperations;

class Humidity {

	use BasicOperations;

	private $absoluteHumidity;
	private $mass;
	private $volume;
	private $relativeHumidity;

	public function __construct(float $mass, float $volume) {
		$this->mass = $mass;
		$this->volume = $volume;
		$this->absoluteHumidity = $this->setAbsoluteHumidity();
	}

	public function setAbsoluteHumidity() {
		$this->absoluteHumidity = $this->divide(
			$this->mass, 
			$this->volume
		);
	}

	public function getAbsoluteHumidity() {
		return $this->absoluteHumidity;
	}

	public function getRelativeHumidity() {
		return $this->relativeHumidity;
	}

	public function setRelativeHumidity(Pressure $pressureMax, Pressure $pressure) {
		$this->relativeHumidity = $this->divide(
			$pressure->calcPartialPressureWithHumidity($this),
			$pressureMax->calcPartialPressureWithHumidity($this)
		);
	}

}
