<?php

namespace cbenco\Config;

class BaseConfig
{
	const CONFIGFILE = "config.json";

    public function getSettings() {
    	$config = json_decode(file_get_contents(self::CONFIGFILE));
    	return $config->settings;
    }

    public function getBaseUrl() : string {
    	return $this->getSettings()->baseurl;
    }

    public function getDatabaseDriver(string $adapter) : string {
    	return $this->getSettings()->database->{$adapter}->driver;
    }

    public function getAdapterDBTable(string $adapter) : string {
    	return $this->getSettings()->database->{$adapter}->tablename;
    }
}