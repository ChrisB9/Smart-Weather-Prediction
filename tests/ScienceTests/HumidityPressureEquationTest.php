<?php

namespace cbenco\Tests\PhysicsTest;

use cbenco\Science\Physics\Thermodynamics\Humidity;
use cbenco\Science\Physics\Thermodynamics\Pressure;
use PHPUnit\Framework\TestCase;

class HumidityPressureEquationTest extends TestCase {
	public function dataProviderForTemperature() {
		return [
			[0, 22.8],
			[0, 22.9],
			[0, 23.0],
			[0, 23.3],
			[0, 23.4],
			[0, 23.4],
			[0, 23.2],
			[0, 23.3],
			[0, 23.3]
		];
	}

	/**
	 * @dataProvider dataProviderForTemperature
	 */ 
	public function testRelativeHumidity($result, $temperature) {
		$pressure = new Pressure(1, 1, 1, 1, $temperature);
		$pressureMax = new Pressure(5, 5, 5, 5, $temperature);

		$humidity = new Humidity(1, 1);
		$humidity->setRelativeHumidity($pressureMax, $pressure);

		$this->assertEquals($result, $humidity->getRelativeHumidity());
	}

	public function testPartialPressure() {
		$pressure = new Pressure(2, 3, 4, 5, 6);
		$pressure->setPartialPressure();
		$this->assertEquals(3.2, $pressure->getPartialPressure());
		$humidity = new Humidity(1, 1);
		$pressure->setPartialPressureWithHumidity($humidity);
		$this->assertEquals(0, $pressure->getPartialPressure());
	}
}