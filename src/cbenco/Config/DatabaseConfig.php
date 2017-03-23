<?php

namespace cbenco\Config;


class DatabaseConfig
{
	const DATABASESCHEMAFILE = "database.json";
    const CONFIGUREDDATABASE = [
    	"mysql" => [
			'database_type' => 'mysql',
		    'database_name' => '',
		    'server' => '',
		    'username' => '',
		    'password' => '',
		    'charset' => 'utf8'
		],
		"sqlite" => [
			'database_type' => 'sqlite',
            'database_file' => 'data/database.db',
            'option' => [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ]
		],
        "sqliteTest" => [
            'database_type' => 'sqlite',
            'database_file' => 'data/databaseTest.db',
            'option' => [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ]
        ],
        "rethinkdb" => [
            'database_type' => 'rethinkdb',
            'database_name' => 'smartWeatherPrediction',
            'database_host' => 'localhost'
        ],
        "rethinkdbTest" => [
            'database_type' => 'rethinkdb',
            'database_name' => 'smartWeatherPredictionTest',
            'database_host' => 'localhost'
        ]
    ];

    public function getDatabaseArray(string $type) {
    	return self::CONFIGUREDDATABASE[$type];
    }

    public function getDatabaseTableSchema(string $type, string $table) {
    	$schema = json_decode(file_get_contents(self::DATABASESCHEMAFILE));
    	return $schema->{$type}->{$table};
    }
}