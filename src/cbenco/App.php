<?php

namespace cbenco;
use cbenco\Routes\Router;
use cbenco\Routes\WeatherObjectRoutes as WOR;
use cbenco\Routes\SensorDeviceRoutes as SDR;
use cbenco\Database\DatabaseFactory;
use cbenco\Forecaster\Adapter\WeatherObjectAdapter;
use cbenco\Forecaster\Adapter\SensorDeviceAdapter;

class App
{
    public $router;
    public function __construct()
    {
        $klein = new Router("/swp2/");
        $this->router = $klein->getRouter();
        $this->appendWeatherRoutes();
        $this->appendDeviceRoutes();
        $klein->dispatch();
    }

    public function appendWeatherRoutes() {
    	$sqliteDatabase = new DatabaseFactory("sqlite");
        $woAdapter = new WeatherObjectAdapter($sqliteDatabase);
        $woRouter = new WOR($woAdapter);
        $this->router = $woRouter->getWeatherRoutes($this->router);
    }

    public function appendDeviceRoutes() {
        $sqliteDatabase = new DatabaseFactory("sqlite");
        $sdAdapter = new SensorDeviceAdapter($sqliteDatabase);
        $sdRouter = new SDR($sdAdapter);
        $this->router = $sdRouter->getSensorRoutes($this->router);
    }
}