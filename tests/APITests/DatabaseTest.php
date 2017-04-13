<?php

namespace cbenco\Tests\APITests;

use cbenco\Database\DatabaseFactory;
use cbenco\Config\DatabaseConfig;
use cbenco\Forecaster\Adapter;
use cbenco\Forecaster\Adapter\WeatherObjectAdapter;
use cbenco\Forecaster\Models\WeatherObjectModel;
use cbenco\Forecaster\Adapter\SensorDeviceAdapter;
use cbenco\Forecaster\Models\SensorDeviceModel;
use cbenco\Forecaster\Adapter\ConfigurationAdapter;
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase {

	protected $sqliteDatabaseConnection;
	protected $rethinkConnection;
	protected $weatherAdapter;
	protected $sensorAdapter;
	public function setUp() {
		$this->weatherAdapter = new WeatherObjectAdapter("weatherobjectadapterTest");
		$this->sensorAdapter = new SensorDeviceAdapter("sensordeviceadapterTest");
	}

	public function getWeatherObjectModel(): WeatherObjectModel {
		$wom = new WeatherObjectModel();
		$wom->setBrightness(20);
		$wom->setHumidity(100);
		$wom->setPressure(904);
		$wom->setTemperature(30);
		$wom->sensorObjectId = 1;
		return $wom;
	}

	public function getSensorObjectModel(): SensorDeviceModel {
		$sdm = new SensorDeviceModel();
		$sdm->setDeviceName("test-device");
		$sdm->setRegisterToken("123456");
		$sdm->setConfigObject(1);
		return $sdm;
	}

	public function testInsertQueryWithWeatherObjectWithCorrectData() {
		$this->assertTrue($this->sensorAdapter->addSensorObjectToDatabase($this->getSensorObjectModel()));
		$this->assertTrue($this->weatherAdapter->addWeatherObjectToDatabase($this->getWeatherObjectModel()));
	}

	public function testCountingDatabaseEntries() {
		$this->assertEquals(2, $this->weatherAdapter->countWeatherObjects());
		$this->assertEquals(1, $this->sensorAdapter->countSensorObjects());
	}

	/**
	 * @depends testInsertQueryWithWeatherObjectWithCorrectData
	 */ 
	public function testSelectQueryWithWeatherObjectWithCorrectData() {
		$getResult = $this->weatherAdapter->getWeatherObjectFromDatabase();
		foreach ($getResult as $result) {
			$this->assertObjectHasAttribute("temperature", $result);
			$this->assertNotEmpty($this->weatherAdapter->getWeatherObjectFromDatabase(["id" => $result->getUId()]));
		}
		$tmp = array_map(function($n){return (string) $n;}, $getResult);
		foreach ($tmp as $result) {
			$this->assertJson($result);
		}
	}

	/**
	 * @depends testInsertQueryWithWeatherObjectWithCorrectData
	 */ 
	public function testSelectQueryWithSensorObjectWithCorrectData() {
		$getResult = $this->sensorAdapter->getSensorObjectFromDatabase();
		foreach ($getResult as $result) {
			$this->assertObjectHasAttribute("deviceName", $result);
			$this->assertNotEmpty($this->sensorAdapter->getSensorObjectFromDatabase(
				"*",
				["id" => $result->getDeviceId()]
			));
		}
		$tmp = array_map(function($n){return (string) $n;}, $getResult);
		foreach ($tmp as $result) {
			$this->assertJson($result);
		}
	}

	public function testUpdateQueryWithWeatherObjectWithCorrectData() {
		foreach ($this->weatherAdapter->getWeatherObjectFromDatabase() as $result) {
			$this->assertTrue($this->weatherAdapter->updateWeatherObject($result->getUId(), ["temperature" => 10]));
		}
	}

	public function testUpdateQueryWithSensorObjectWithCorrectData() {
		foreach ($this->sensorAdapter->getSensorObjectFromDatabase() as $result) {
			$this->assertTrue($this->sensorAdapter->updateSensorObject($result->getDeviceId(), ["name" => "new device"]));
		}
	}

	/**
	 * @expectedException Exception
	 * @expectedExceptionMessageRegExp /Unknown update variable \w+/
	 */ 
	public function testUpdateQueryWithWeatherObjectWithWrongInputVariable() {
		foreach ($this->weatherAdapter->getWeatherObjectFromDatabase() as $result) {
			$this->assertTrue($this->weatherAdapter->updateWeatherObject($result->getUId(), ["bla" => "bla"]));
		}
	}

	public function testIfSensorObjectIdValidationWorks() {
		$this->assertFalse($this->weatherAdapter->validateSensorObject(1000));
	}

	/**
	 * @expectedException Exception
	 */ 
	public function testUpdateQueryWithWeatherObjectWithWrongSensorObjectId() {
		foreach ($this->weatherAdapter->getWeatherObjectFromDatabase() as $result) {
			$this->assertTrue($this->weatherAdapter->updateWeatherObject($result->getUId(), ["sensorObjectId" => 1000]));
		}
	}

	/**
	 * @expectedException Exception
	 * @expectedExceptionMessageRegExp /Unknown update variable \w+/
	 */ 
	public function testUpdateQueryWithSensorObjectWithWrongInputVariable() {
		foreach ($this->sensorAdapter->getSensorObjectFromDatabase() as $result) {
			$this->assertTrue($this->sensorAdapter->updateSensorObject($result->getDeviceId(), ["bla" => "bla"]));
		}
	}

	public function testReplaceQueryWithWeatherObjectWithCorrectData() {
		foreach ($this->weatherAdapter->getWeatherObjectFromDatabase() as $result) {
			$this->assertTrue($this->weatherAdapter->replaceWeatherObject($result->getUId(), $this->getWeatherObjectModel()));
		}
	}

	public function testReplaceQueryWithSensorObjectWithCorrectData() {
		foreach ($this->sensorAdapter->getSensorObjectFromDatabase() as $result) {
			$this->assertTrue($this->sensorAdapter->replaceSensorObject($result->getDeviceId(), $this->getSensorObjectModel()));
		}
	}

	/**
	 * @depends testInsertQueryWithWeatherObjectWithCorrectData
	 * @depends testSelectQueryWithSensorObjectWithCorrectData
	 */ 
	public function testToGetSensorIdBySensorToken() {
		$getResult = $this->sensorAdapter->getSensorObjectFromDatabase();
		foreach ($getResult as $result) {
			$sensorId = $this->weatherAdapter->getSensorIdByToken(
				$result->getRegisterToken()
			);
			$this->assertEquals($result->getDeviceId(), $sensorId);
		}
	}

	/**
	 * @depends testUpdateQueryWithWeatherObjectWithCorrectData
	 * @depends testReplaceQueryWithWeatherObjectWithCorrectData
	 * @depends testInsertQueryWithWeatherObjectWithCorrectData
	 */ 
	public function testDeleteQueryWithWeatherObjectWithCorrectData() {
		foreach ($this->weatherAdapter->getWeatherObjectFromDatabase() as $result) {
			$this->assertTrue($this->weatherAdapter->deleteWeatherObject($result->getUId()));
		}
	}

	/**
	 * @depends testUpdateQueryWithSensorObjectWithCorrectData
	 * @depends testReplaceQueryWithSensorObjectWithCorrectData
	 * @depends testInsertQueryWithWeatherObjectWithCorrectData
	 */ 
	public function testDeleteQueryWithSensorObjectWithCorrectData() {
		foreach ($this->sensorAdapter->getSensorObjectFromDatabase() as $result) {
			$this->assertTrue($this->sensorAdapter->deleteSensorObject($result->getDeviceId()));
		}
	}

	/**
	 * @depends testUpdateQueryWithWeatherObjectWithCorrectData
	 * @depends testReplaceQueryWithWeatherObjectWithCorrectData
	 * @depends testInsertQueryWithWeatherObjectWithCorrectData
	 * @expectedException InvalidArgumentException
	 */ 
	public function testDeleteQueryWithWeatherObjectWithNegativeId() {
		$this->assertTrue($this->weatherAdapter->deleteWeatherObject(-1));
	}

	/**
	 * @depends testUpdateQueryWithSensorObjectWithCorrectData
	 * @depends testReplaceQueryWithSensorObjectWithCorrectData
	 * @depends testInsertQueryWithWeatherObjectWithCorrectData
	 * @expectedException InvalidArgumentException
	 */ 
	public function testDeleteQueryWithSensorObjectWithNegativeId() {
		$this->assertTrue($this->sensorAdapter->deleteSensorObject(-1));
	}

	public function __destruct() {
		//var_dump($this->weatherAdapter->getWeatherObjectFromDatabase());
		//$this->sqliteDatabaseConnection->deleteDatabase();
	}
}
