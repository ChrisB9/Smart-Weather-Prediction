<?php

namespace cbenco;
use cbenco\Config\BaseConfig;
use cbenco\Routes\Router;
use cbenco\Routes\WeatherObjectRoutes as WOR;
use cbenco\Routes\SensorDeviceRoutes as SDR;
use cbenco\Routes\RoutesHelper;
use cbenco\Database\DatabaseFactory;
use cbenco\Forecaster\Adapter\WeatherObjectAdapter;
use cbenco\Forecaster\Adapter\SensorDeviceAdapter;

class App
{
    public $router;
    public function __construct()
    {
        $klein = new Router((new BaseConfig)->getBaseUrl());
        $this->router = $klein->getRouter();
        $this->appendWeatherRoutes();
        $this->appendDeviceRoutes();
        $klein->dispatch();
    }

    public function appendWeatherRoutes() {
        $woRouter = new WOR((new WeatherObjectAdapter));
        $this->router = $woRouter->getWeatherRoutes($this->router);
    }

    public function appendDeviceRoutes() {
        $sdRouter = new SDR((new SensorDeviceAdapter));
        $this->router = $sdRouter->getSensorRoutes($this->router);
    }
}