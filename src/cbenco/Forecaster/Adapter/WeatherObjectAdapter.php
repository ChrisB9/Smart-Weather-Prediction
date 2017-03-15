<?php

namespace cbenco\Forecaster\Adapter;
use cbenco\Forecaster\Adapter\SensorDeviceAdapter;
use cbenco\Database;
use cbenco\Forecaster\Models;
use cbenco\Config\DatabaseConfig;

class WeatherObjectAdapter {
	private $database;
	public function __construct(Database\DatabaseFactory $database) {
		$this->database = $database;
		$this->database->createTable(
			"weatherdata", 
			(new DatabaseConfig)->getDatabaseTableSchema(
				"sqlite", "weatherdata")
		);
	}
	public function addWeatherObjectToDatabase(Models\WeatherObjectModel $weatherObject) : bool {
		if (!$this->validateSensorObject($weatherObject->sensorObjectId)) {
			throw new \Exception("$weatherObject->sensorObjectId doesnt exist");
		}
		$insertArray = [
			"temperature" => $weatherObject->getTemperature(),
			"humidity" => $weatherObject->getHumidity(),
			"pressure" => $weatherObject->getPressure(),
			"brightness" => $weatherObject->getBrightness(),
			"sensorObjectId" => $weatherObject->sensorObjectId,
			"date" => $weatherObject->creationDate->format('Y-m-d H:i:s'),
			"deleted" => 0
		];
		try {
			$this->database->getDatabase()->insert("weatherdata", $insertArray);
			return true;
		} catch (\PDOException $exception) {
			echo $exception->getMessage();
			return false;
		}
		
	}

	public function addWeatherObjectByTokenToDatabase(string $token, Models\WeatherObjectModel $weatherObject) : bool {
		$sensorToken = $this->getSensorIdByToken($token);
		if ($token == 0) throw new \Exception("$token doesnt exist");
		$insertArray = [
			"temperature" => $weatherObject->getTemperature(),
			"humidity" => $weatherObject->getHumidity(),
			"pressure" => $weatherObject->getPressure(),
			"brightness" => $weatherObject->getBrightness(),
			"sensorObjectId" => $sensorToken,
			"date" => $weatherObject->creationDate->format('Y-m-d H:i:s'),
			"deleted" => 0
		];
		try {
			$this->database->getDatabase()->insert("weatherdata", $insertArray);
			return true;
		} catch (\PDOException $exception) {
			echo $exception->getMessage();
			return false;
		}
		
	}

	public function getWeatherObjectFromDatabase(...$arguments) : array {
		$objectArray = [];
		$result = [];
		if (isset($arguments)) {
			switch (count($arguments)) {
				case 1:
					$result = $this->database->getDatabase()->select("weatherdata", $arguments[0]);
					break;
				case 2:
					if (!is_array($arguments[0])) {
						$result = $this->database->getDatabase()->select("weatherdata", "*", $arguments[1]);
					} else {
						$result = $this->database->getDatabase()->select("weatherdata", $arguments[0], $arguments[1]);
					}
					break;
				default:
					$result = $this->database->getDatabase()->select("weatherdata", "*");
					break;
			}
		} else {
			$result = $this->database->getDatabase()->select("weatherdata", "*");
		}
		foreach ($result as $entry) {
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
			"sensorObjectId" => $weatherObject->sensorObjectId
		];
		return $this->updateWeatherObject($uId, $replacementArray);
	}

	public function updateWeatherObject(int $uId, array $newValues) : bool {
		$possibleKeys = ["temperature", "humidity", "pressure", "brightness", "sensorObjectId"];
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
			$this->database->getDatabase()->update(
				"weatherdata",
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
			$this->database->getDatabase()->delete("weatherdata", ["id" => $uId]);
			return true;
		} catch (\PDOException $exception) {
			var_dump($exception);
			return false;
		}
	}

	public function validateSensorObject(int $sensorObjectId) : bool {
		$sensorAdapter = new SensorDeviceAdapter($this->database);
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
		$sensorAdapter = new SensorDeviceAdapter($this->database);
		$res = $sensorAdapter->getSensorObjectFromDatabase("*", ["registerToken" => $token]);
		if (count($res) > 0) {
			return $res[0]->getDeviceId();
		}
		return 0;
	}

	public function objectToWeatherObject($object) : Models\WeatherObjectModel {
		$weatherObject = new Models\WeatherObjectModel();
        $weatherObject->setDataByJson($object);
        return $weatherObject;
    }
}
