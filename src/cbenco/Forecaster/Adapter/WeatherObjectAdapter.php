<?php

namespace cbenco\Forecaster\Adapter;
use cbenco\Forecaster\Adapter\SensorDeviceAdapter;
use cbenco\Config\BaseConfig;
use cbenco\Database\DatabaseFactory;
use cbenco\Forecaster\Models;
use cbenco\Config\DatabaseConfig;

class WeatherObjectAdapter {

	private $database;
	private $tableName;
	public function __construct(string $adapter = "weatherobjectadapter") {
		$this->database = new DatabaseFactory((new BaseConfig)->getDatabaseDriver($adapter));
		$this->tableName = (new BaseConfig)->getAdapterDBTable($adapter);
		$this->database->createTable(
			$this->tableName,
			(new DatabaseConfig)->getDatabaseTableSchema(
				(new BaseConfig)->getDatabaseDriver($adapter),
				$this->tableName
			)
		);
		$this->database->createSecondaryIndex($this->tableName, "date");
	}

	private function insertArrayFromWeatherObject(Models\WeatherObjectModel $weatherObject) {
		return [
			"temperature" => $weatherObject->getTemperature(),
			"humidity" => $weatherObject->getHumidity(),
			"pressure" => $weatherObject->getPressure(),
			"brightness" => $weatherObject->getBrightness(),
			"cloudiness" => $weatherObject->getCloudiness(),
			"windspeed" => $weatherObject->getWindspeed(),
			"winddirection" => $weatherObject->getWinddirection(),
			"sensorObjectId" => $weatherObject->sensorObjectId,
			"date" => $weatherObject->creationDate->format(\DateTime::ATOM),
			"deleted" => 0
		];
	}

	public function addWeatherObjectToDatabase(Models\WeatherObjectModel $weatherObject) : bool {
		if (!$this->validateSensorObject($weatherObject->sensorObjectId)) {
			throw new \Exception("$weatherObject->sensorObjectId doesnt exist");
		}
		try {
			$this->database->insert($this->tableName, $this->insertArrayFromWeatherObject($weatherObject));
			return true;
		} catch (\PDOException $exception) {
			echo $exception->getMessage();
			return false;
		}
		
	}

	public function addWeatherObjectByTokenToDatabase(string $token, Models\WeatherObjectModel $weatherObject) : bool {
		$sensorToken = $this->getSensorIdByToken($token);
		if ($sensorToken == 0) throw new \Exception("$token doesn't exist");
		$weatherObject->sensorObjectId = $sensorToken;
		return $this->addWeatherObjectToDatabase($weatherObject);
	}

	public function getWeatherObjectFromDatabase(...$arguments) : array {
		$objectArray = [];
		$result = [];
	    switch (count($arguments)) {
			case 1:
				$result = $this->database->select($this->tableName, "*", $arguments[0]);
				break;
			case 2:
				$result = $this->database->select($this->tableName, "*", $arguments[0], $arguments[1]);
				break;
			default:
				$result = $this->database->select($this->tableName, "*");
				break;
		}
		foreach ($result as $entry) {
			if (count($entry) == 0) continue;
			$objectArray[] = $this->objectToWeatherObject(json_encode($entry));
		}
		return $objectArray;
	}

	public function replaceWeatherObject(int $uId, Models\WeatherObjectModel $weatherObject) : bool {
		$replacementArray = [
			"temperature" => $weatherObject->getTemperature(),
			"humidity" => $weatherObject->getHumidity(),
			"pressure" => $weatherObject->getPressure(),
			"brightness" => $weatherObject->getBrightness(),
			"cloudiness" => $weatherObject->getCloudiness(),
			"windspeed" => $weatherObject->getWindspeed(),
			"winddirection" => $weatherObject->getWinddirection(),
			"sensorObjectId" => $weatherObject->sensorObjectId
		];
		return $this->updateWeatherObject($uId, $replacementArray);
	}

	public function updateWeatherObject(int $uId, array $newValues) : bool {
		$possibleKeys = ["temperature", "humidity", "pressure", "windspeed",
			"brightness", "sensorObjectId", "cloudiness", "winddirection"];
		foreach ($newValues as $key => $value) {
			if (!in_array($key, $possibleKeys)) {
				throw new \Exception("Unknown update variable $key");
			}
			if (!is_numeric($value)) {
				throw new \InvalidArgumentException("$value has to be a number");
			}
			if ($key == "sensorObjectId") {
				if (!$this->validateSensorObject($value)) {
					throw new \Exception("$value doesnt exist");
				}
			}
		}
		try {
			$this->database->update(
				$this->tableName,
				$newValues,
				["id" => $uId]
			);
			return true;
		} catch (\Exception $exception) {
			var_dump($exception);
			return false;
		}
	}

	public function deleteWeatherObject(int $uId) : bool {
		if ($uId < 1) {
			throw new \InvalidArgumentException("Id $uId can't be negative!");
		} 
		try {
			$this->database->delete($this->tableName, ["id" => $uId]);
			return true;
		} catch (\PDOException $exception) {
			var_dump($exception);
			return false;
		}
	}

	public function validateSensorObject(int $sensorObjectId) : bool {
		$sensorAdapter = new SensorDeviceAdapter();
		$res = $sensorAdapter->getSensorObjectFromDatabase("*", ["id" => $sensorObjectId]);
		if (count($res) > 0) {
			foreach ($res as $entry) {
				if ($entry->getDeviceId() == $sensorObjectId) {
					return true;
				}
			}
		}
		return false;
	}

	public function getSensorIdByToken(string $token) : int {
		$sensorAdapter = new SensorDeviceAdapter();
		$res = $sensorAdapter->getSensorObjectFromDatabase("*", ["registerToken" => $token]);
		if (count($res) > 0) {
			return $res[0]->getDeviceId();
		}
		return 0;
	}

	public function countWeatherObjects() : int {
		return $this->database->countDatabaseEntries($this->tableName);
	}

	public function objectToWeatherObject($object) : Models\WeatherObjectModel {
		$weatherObject = new Models\WeatherObjectModel();
        $weatherObject->setDataByJson($object);
        return $weatherObject;
    }

    private function resetDatabase() : bool {
    	if ($this->database->checkIfTableExists($this->tableName)) {
    		$this->database->deleteDatabase();
    		return $this->database->checkIfTableExists($this->tableName);
    	}
    	return false;
    }
}
