<?php

namespace cbenco\Database;
use Medoo\Medoo;
use cbenco\Config\DatabaseConfig;

class DatabaseFactory {
	private $database;
	private $databaseType;
	public function __construct(string $databaseType) {
		$this->databaseType = $databaseType;
		$this->database = new Medoo((new DatabaseConfig)->getDatabaseArray($this->getDatabaseType()));
	}

	public function getDatabase() {
		return $this->database;
	}

	public function getDatabaseType() {
		return $this->databaseType;
	}

	public function deleteDatabase() {
		switch ((new DatabaseConfig)->getDatabaseArray($this->getDatabaseType())["database_type"]) {
			case "sqlite":
				unlink((new DatabaseConfig)->getDatabaseArray($this->getDatabaseType())["database_file"]);
				break;
		}
	}

	public function createTable(string $tableName, array $schema) {
		$sql = "CREATE TABLE IF NOT EXISTS $tableName (";
		$sql.= implode(",", $schema);
		try {
			$this->database->query($sql.")");
		} catch (\PDOException $e) {
			var_dump($e);
		}
	}
}