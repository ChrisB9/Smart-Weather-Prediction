<?php


namespace cbenco\Forecaster\Adapter;

use cbenco\Database\DatabaseFactory;
use cbenco\Config\DatabaseConfig;
use cbenco\Forecaster\Models\SensorDeviceModel;

class SensorDeviceAdapter {
	private $database;
	const DB_TABLE_NAME = "sensorobject";
	const DB_TYPE = "sqlite";
	public function __construct(DatabaseFactory $database) {
		$this->database = $database;
		$this->database->createTable(
			self::DB_TABLE_NAME,
			(new DatabaseConfig)->getDatabaseTableSchema(self::DB_TYPE, self::DB_TABLE_NAME)
		);
	}

	public function addSensorObjectToDatabase(SensorDeviceModel $deviceObject) : bool {
		$insertArray = [
			"registerToken" => $deviceObject->getRegisterToken(),
			"name" => $deviceObject->getDeviceName(),
			"date" => $deviceObject->getRegisterDate()->format('Y-m-d H:i:s'),
			"configObject" => $deviceObject->getConfigObject(),
			"deleted" => 0
		];
		try {
			$this->database->getDatabase()->insert(
				self::DB_TABLE_NAME,
				$insertArray
			);
			return true;
		} catch (\PDOException $exception) {
			echo $exception->getMessage();
			return false;
		}
	}

	public function getSensorObjectFromDatabase(...$arguments) : array {
		$objectArray = [];
		$result = [];
		switch (count($arguments)) {
			case 1:
				$result = $this->database->getDatabase()->select(self::DB_TABLE_NAME, $arguments[0]);
				break;
			case 2:
				$result = $this->database->getDatabase()->select(self::DB_TABLE_NAME, "*", $arguments[1]);
			default:
				$result = $this->database->getDatabase()->select(self::DB_TABLE_NAME, "*");
		}
		$objectArray = array_map(function ($entry) {
			return $this->objectToSensorObject(
				json_encode($entry)
			);
		}, $result);
		return $objectArray;
	}

	public function replaceSensorObject(int $uId, SensorDeviceModel $deviceObject) : bool {
		$replacementArray = [
			"registerToken" => $deviceObject->getRegisterToken(),
			"name" => $deviceObject->getDeviceName(),
			"date" => $deviceObject->getRegisterDate(),
			"configObject" => $deviceObject->getConfigObject(),
		];
		return $this->updateSensorObject($uId, $replacementArray);
	}

	public function updateSensorObject(int $uId, array $newValues) : bool {
		$possibleKeys = ["registerToken", "name", "date", "configObject"];
		foreach (array_keys($newValues) as $key) {
			if (!in_array($key, $possibleKeys)) {
				throw new \Exception("Unknown update variable $key");
			}
		}
		if ($uId < 1) throw new \Exception("Id $uId can't be negative");
		try {
			$this->database->getDatabase()->update(
				self::DB_TABLE_NAME,
				$newValues,
				["id" => $uId]
			);
			return true;
		} catch (\Exception $exception) {
			var_dump($exception);
			return false;
		}
	}

	public function deleteSensorObject(int $uId) : bool {
		if ($uId < 1) throw new \InvalidArgumentException("Id $uId can't be negative");
		try {
			$this->database->getDatabase()->delete(
				self::DB_TABLE_NAME,
				["id" => $uId]
			);
			return true;
		} catch (\Exception $exception) {
			var_dump($exception);
			return false;
		}
	}

	public function getLastInsertedId() : int {
		return $this->database->getDatabase()->id();
	}

	public function objectToSensorObject($object) : SensorDeviceModel {
		$sensorObject = new SensorDeviceModel();
		$sensorObject->setDataByJson($object);
		return $sensorObject;
	}
}
