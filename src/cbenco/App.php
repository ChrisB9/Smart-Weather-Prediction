<?php

namespace cbenco;
use cbenco\Routes\Router;
use cbenco\Routes\WeatherObjectRoutes as WOR;
use cbenco\Database;
use cbenco\Forecaster\Adapter;

class App
{
    public $router;
    public function __construct()
    {
        $klein = new Router("/swp2/");
        $this->router = $klein->getRouter();
        $this->appendWeatherRoutes();
        $klein->dispatch();
    }

    public function appendWeatherRoutes() {
    	$sqliteDatabase = new Database\DatabaseFactory("sqlite");
        $woAdapter = new Adapter\WeatherObjectAdapter($sqliteDatabase);
        $woRouter = new WOR($woAdapter);
        $this->router = $woRouter->getWeatherRoutes($this->router);
    }
}