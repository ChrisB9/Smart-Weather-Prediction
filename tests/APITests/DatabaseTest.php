<?php

namespace cbenco\Tests\APITests;

use cbenco\Database;
use cbenco\Config\DatabaseConfig;
use cbenco\Forecaster\Adapter;
use cbenco\Forecaster\Adapter\WeatherObjectAdapter;
use cbenco\Forecaster\Models\WeatherObjectModel;
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase {

	protected $sqliteDatabaseConnection;
	protected $weatherAdapter;
	public function setUp() {
		$this->sqliteDatabaseConnection = new Database\DatabaseFactory("sqliteTest");
		$this->sqliteDatabaseConnection->createTable("weatherdata", DatabaseConfig::getDatabaseTableSchema($this->sqliteDatabaseConnection->getDatabaseType(), "weatherdata"));
		$this->weatherAdapter = new WeatherObjectAdapter($this->sqliteDatabaseConnection);
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

	public function testInsertQueryWithWeatherObjectWithCorrectData() {
		$this->assertTrue($this->weatherAdapter->addWeatherObjectToDatabase($this->getWeatherObjectModel()));
	}

	/**
	 * @depends testInsertQueryWithWeatherObjectWithCorrectData
	 */ 
	public function testSelectQueryWithWeatherObjectWithCorrectData() {
		$getResult = $this->weatherAdapter->getWeatherObjectFromDatabase();
		foreach ($getResult as $result) {
			$this->assertObjectHasAttribute("temperature", $result);
			$this->assertNotEmpty($this->weatherAdapter->getWeatherObjectFromDatabase("*", ["id" => $result->getUId()]));
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

	/**
	 * @expectedException Exception
	 * @expectedExceptionMessageRegExp /Unknown update variable \w+/
	 */ 
	public function testUpdateQueryWithWeatherObjectWithWrongInputVariable() {
		foreach ($this->weatherAdapter->getWeatherObjectFromDatabase() as $result) {
			$this->assertTrue($this->weatherAdapter->updateWeatherObject($result->getUId(), ["bla" => "bla"]));
		}
	}

	public function testReplaceQueryWithWeatherObjectWithCorrectData() {
		foreach ($this->weatherAdapter->getWeatherObjectFromDatabase() as $result) {
			$this->assertTrue($this->weatherAdapter->replaceWeatherObject($result->getUId(), $this->getWeatherObjectModel()));
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
	 * @depends testUpdateQueryWithWeatherObjectWithCorrectData
	 * @depends testReplaceQueryWithWeatherObjectWithCorrectData
	 * @depends testInsertQueryWithWeatherObjectWithCorrectData
	 * @expectedException InvalidArgumentException
	 */ 
	public function testDeleteQueryWithWeatherObjectWithNegativeId() {
		$this->assertTrue($this->weatherAdapter->deleteWeatherObject(-1));
	}

	public function __destruct() {
		//var_dump($this->weatherAdapter->getWeatherObjectFromDatabase());
		//$this->sqliteDatabaseConnection->deleteDatabase();
	}
}
