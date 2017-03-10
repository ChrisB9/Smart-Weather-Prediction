<?php

namespace cbenco\Database;
use Medoo\Medoo;
use cbenco\Config;

class DatabaseFactory {
	private $database;
	private $databaseType;
	public function __construct(string $databaseType) {
		$this->databaseType = $databaseType;
		$this->database = new Medoo(Config\DatabaseConfig::configuredDatabase[$databaseType]);
	}

	public function getDatabase() {
		return $this->database;
	}

	public function getDatabaseType() {
		return $this->databaseType;
	}

	public function deleteDatabase() {
		switch (Config\DatabaseConfig::getDatabaseArray($this->getDatabaseType())["database_type"]) {
			case "sqlite":
				unlink(Config\DatabaseConfig::getDatabaseArray($this->getDatabaseType())["database_file"]);
				break;
		}
	}

	public function createTable(string $tableName, array $schema) {
		$sql = "CREATE TABLE IF NOT EXISTS $tableName (";
		for ($i = 0; $i < count($schema); $i++) {
			$sql .= $schema[$i];
			if ($i < (count($schema)-1))
				$sql .= ",";
		}
		try {
			$this->database->query($sql.")");
		} catch (\PDOException $e) {
			var_dump($e);
		}
	}
}