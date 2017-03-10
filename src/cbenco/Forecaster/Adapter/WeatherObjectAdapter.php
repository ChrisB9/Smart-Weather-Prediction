<?php

namespace cbenco\Forecaster\Adapter;
use cbenco\Database;
use cbenco\Forecaster\Models;
use cbenco\Config;

class WeatherObjectAdapter {
	private $database;
	public function __construct(Database\DatabaseFactory $database) {
		$this->database = $database;
		$this->database->createTable(
			"weatherdata", 
			Config\DatabaseConfig::getDatabaseTableSchema(
				"sqlite", "weatherdata")
		);
	}
	public function addWeatherObjectToDatabase(Models\WeatherObjectModel $weatherObject) : bool {
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
		} catch (\PDOException $e) {
			echo $e->getMessage();
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

	public function replaceWeatherObject(int $id, Models\WeatherObjectModel $weatherObject) : bool {
		$replacementArray = [
			"temperature" => $weatherObject->getTemperature(),
			"humidity" => $weatherObject->getHumidity(),
			"pressure" => $weatherObject->getPressure(),
			"brightness" => $weatherObject->getBrightness(),
			"sensorObjectId" => $weatherObject->sensorObjectId
		];
		return $this->updateWeatherObject($id, $replacementArray);
	}

	public function updateWeatherObject(int $id, array $newValues) : bool {
		$possibleKeys = ["temperature", "humidity", "pressure", "brightness", "sensorObjectId"];
		foreach (array_keys($newValues) as $key) {
			if (!in_array($key, $possibleKeys)) {
				throw new \Exception("Unknown update variable $key");
			}
		}
		foreach ($newValues as $value) {
			if (!is_numeric($value)) {
				throw new \InvalidArgumentException("$value has to be a number");
			}
		}
		try {
			$this->database->getDatabase()->update(
				"weatherdata",
				$newValues,
				["id" => $id]
			);
			return true;
		} catch (\Exception $e) {
			var_dump($e);
			return false;
		}
	}

	public function deleteWeatherObject(int $id) : bool {
		if ($id < 1) {
			throw new \InvalidArgumentException("Id $id can't be negative!");
		} 
		try {
			$this->database->getDatabase()->delete("weatherdata", ["id" => $id]);
			return true;
		} catch (\PDOException $e) {
			var_dump($e);
			return false;
		}
	}

	public function objectToWeatherObject($object) : Models\WeatherObjectModel {
		$weatherObject = new Models\WeatherObjectModel();
        $weatherObject->setDataByJson($object);
        return $weatherObject;
    }
}
