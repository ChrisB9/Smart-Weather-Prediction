<?php


namespace cbenco\Forecaster\Adapter;

use cbenco\Database\DatabaseFactory;
use cbenco\Config\DatabaseConfig;
use cbenco\Config\BaseConfig;
use cbenco\Forecaster\Models\SensorDeviceModel;

class SensorDeviceAdapter {
	private $database;
	private $tableName;
	public function __construct(string $adapter = "sensordeviceadapter") {
		$this->database = new DatabaseFactory((new BaseConfig)->getDatabaseDriver($adapter));
		$this->tableName = (new BaseConfig)->getAdapterDBTable($adapter);
		$this->database->createTable(
			$this->tableName,
			(new DatabaseConfig)->getDatabaseTableSchema(
				(new BaseConfig)->getDatabaseDriver($adapter),
				$this->tableName
			)
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
				$this->tableName,
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
			case 2:
				$result = $this->database->getDatabase()->select($this->tableName, "*", $arguments[1]);
			default:
				$result = $this->database->getDatabase()->select($this->tableName, "*");
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

	public function deleteSensorObject(int $uId) : bool {
		if ($uId < 1) throw new \InvalidArgumentException("Id $uId can't be negative");
		try {
			$this->database->getDatabase()->delete(
				$this->tableName,
				["id" => $uId]
			);
			return true;
		} catch (\Exception $exception) {
			var_dump($exception);
			return false;
		}
	}

	public function getLastInsertedId() : int {
		return $this->database->getLastId();
	}

	public function countSensorObjects() : int {
		return $this->database->countDatabaseEntries($this->tableName);
	}

	public function objectToSensorObject($object) : SensorDeviceModel {
		$sensorObject = new SensorDeviceModel();
		$sensorObject->setDataByJson($object);
		return $sensorObject;
	}
}
