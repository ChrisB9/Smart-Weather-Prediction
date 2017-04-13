<?php

namespace cbenco\Database;
use Medoo\Medoo;
use cbenco\Config\DatabaseConfig;
use r as rethinkdb;

class DatabaseFactory {
	private $database;
	private $databaseType;
	private $databaseConfig;
	private $lastInsertedId;
	public function __construct(string $databaseType) {
		$this->databaseType = $databaseType; 
		$this->createDatabase((new DatabaseConfig));
	}

	public function createDatabase(DatabaseConfig $dbConfig) {
		$this->databaseConfig = $dbConfig->getDatabaseArray($this->getDatabaseType());
		switch ($this->databaseConfig["database_type"]) {
			case "rethinkdb":
				$this->database = rethinkdb\connect($this->databaseConfig["database_host"]);
				if (!$this->checkIfDatabaseExists($this->databaseConfig["database_name"])) {
					rethinkdb\dbCreate($this->databaseConfig["database_name"])
						->run($this->database);
				}
				break;
			default:
				$this->database = new Medoo($this->databaseConfig);
				break;
		}
	}

	public function checkIfDatabaseExists(string $dbName) : bool {
		switch ($this->databaseConfig["database_type"]) {
			case "rethinkdb":
				foreach (rethinkdb\dbList()->run($this->database) as $database) {
					if ($database == $dbName) return true;
				}
				return false;
				break;
			default:
				return false;
				break;
		}
	}

	public function getDatabase() {
		return $this->database;
	}

	public function getDatabaseType() {
		return $this->databaseType;
	}

	public function deleteDatabase() {
		switch ($this->databaseConfig["database_type"]) {
			case "rethinkdb":
				rethinkdb\dbDrop($this->databaseConfig["database_name"])
					->run($this->database);
				break;
			case "sqlite":
				unlink($this->databaseConfig["database_file"]);
				break;
			default:
				break;
		}
	}

	public function getRethinkDbTable(string $table) {
		return rethinkdb\db($this->databaseConfig["database_name"])
					->table($table);
	}


	/*
	 * @TODO:
	 * 
	 * Streaming mit https://github.com/reactphp/http umsetzen
	 * 
	 *
	*/
	public function getDatabaseStream(string $table) {
		return $this->getRethinkDbTable($table)
			->changes()
			->run($this->database);
	}

	public function countDatabaseEntries(string $table) {
		switch ($this->databaseConfig["database_type"]) {
			case 'rethinkdb':
				return ($this->getRethinkDbTable($table)
					->info()
					->getField("doc_count_estimates")
					->run($this->database))[0];
			default:
				return $this->getDatabase()->count($table);
		}
	}

	public function insert(string $table, $insert) {
		switch ($this->databaseConfig["database_type"]) {
			case "rethinkdb":
				$insert["id"] = ((int) $this->getLastId($table)) + 1;
				$this->getRethinkDbTable($table)
					->insert($insert)
					->run($this->database);
				break;
			default:
				$this->getDatabase()->insert($table, $insert);
				break;
		}
	}

	// TODO: rewrite method (to many ifs)
	public function select(string $table, $columns, $where = null, int $limit = 0) {
		switch ($this->databaseConfig["database_type"]) {
			case "rethinkdb":
				if (is_null($where)) {
					if ($limit === 0) {
						return $this->getRethinkDbTable($table)
							->orderBy(['index' => rethinkdb\desc("id")])
							->run($this->database)
							->toArray();
					}
					return $this->getRethinkDbTable($table)
						->orderBy(['index' => rethinkdb\desc("id")])
						->limit($limit)
						->run($this->database)
						->toArray();
				}
				if (isset($where["id"])) {
					return [(array) $this->getRethinkDbTable($table)
						->get((int) $where["id"])
						->run($this->database)];
				}
				return $this->getRethinkDbTable($table)
					->filter($where)
					->run($this->database)
					->toArray();
			default:
				if ($limit > 0) {
					$where["LIMIT"] = $limit;
				}
				return $this->getDatabase()->select($table, $columns, $where);
		}
	}

	public function update(string $table, $new, $where) {
		switch ($this->databaseConfig["database_type"]) {
			case "rethinkdb":
				$this->getRethinkDbTable($table)
					->get($where["id"])
					->update($new)
					->run($this->database);
				break;
			default:
				$this->getDatabase()->update($table, $new, $where);
				break;
		}
	}

	public function delete(string $table, $where) {
		switch ($this->databaseConfig["database_type"]) {
			case "rethinkdb":
				$this->getRethinkDbTable($table)
					->get($where["id"])
					->delete()
					->run($this->database);
				break;
			default:
				$this->getDatabase()->delete($table, $where);
				break;
		}
	}

	public function getLastId($table = null) {
		switch ($this->databaseConfig["database_type"]) {
			case "rethinkdb":
				if (is_null($table)) {
					throw new rethinkdb\Exceptions\RqlException("rethink needs a table as parameter");
				}
				if ($this->getRethinkDbTable($table)
					->count()
					->run($this->database) == 0) return 0;
				return $this->getRethinkDbTable($table)
					->max('id')
					->run($this->database)["id"];
				break;
			default:
				$this->lastInsertedId = (int) $this->getDatabase()->id();
				return $this->lastInsertedId;
				break;
		}
	}

	public function createTable(string $tableName, array $schema) {
		switch ($this->databaseConfig["database_type"]) {
			case "rethinkdb":
				if (!$this->checkIfTableExists($tableName)) {
					rethinkdb\db($this->databaseConfig["database_name"])
						->tableCreate($tableName)
						->run($this->database);
				}
				break;
			default:
				$sql = "CREATE TABLE IF NOT EXISTS $tableName (";
				$sql.= implode(",", $schema).")";
				try {
					$this->database->query($sql);
				} catch (\PDOException $e) {
					var_dump($e);
				}
				break;
		}
	}

	public function checkIfTableExists(string $tableName) : bool {
		switch ($this->databaseConfig["database_type"]) {
			case "rethinkdb":
				$tables = rethinkdb\db($this->databaseConfig["database_name"])
					->tableList()
					->run($this->database);
				foreach ($tables as $table) {
					if ($table == $tableName) return true;
				}
				break;
			default:
				return false;
				break;
		}
		return false;
	}
}