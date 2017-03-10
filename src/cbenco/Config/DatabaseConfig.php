<?php

namespace cbenco\Config;


class DatabaseConfig
{
	const databaseSchemaFile = "database.json";
    const configuredDatabase = [
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
        ]
    ];

    public static function getDatabaseArray(string $type) {
    	return self::configuredDatabase[$type];
    }

    public static function getDatabaseTableSchema(string $type, string $table) {
    	$schema = json_decode(file_get_contents(self::databaseSchemaFile));
    	return $schema->$type->$table;
    }
}