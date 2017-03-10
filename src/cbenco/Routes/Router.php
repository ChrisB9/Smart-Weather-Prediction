<?php

namespace cbenco\Routes;
use cbenco\App;
use cbenco\Database;
use cbenco\Forecaster\Adapter;
use Klein\Klein;
use Klein\Request;
use Klein\Response;

class Router
{
    protected $app;
    private $appPath;

    public function __construct($appPath)
    {
        $this->app = $this->registerRoutes((new Klein()));
        $this->setAppPath($appPath);
    }

    public function dispatch() {
        $request = Request::createFromGlobals();
        $uri = $request->server()->get('REQUEST_URI');
        $request->server()->set('REQUEST_URI', '/'.substr($uri, strlen($this->appPath)));
        $this->app->dispatch($request);
    }

    public function getRouter() {
        return $this->app;
    }

    public function setAppPath($appPath) {
        $this->appPath = $appPath;
    }

    public function registerRoutes(Klein $klein) {
        $klein->respond('/', function($request) {
            echo "Welcome!";
        });
        $klein->onHttpError(function ($code, Klein $router) {
            echo $code;
        });

        
        $klein->respond('GET', '/config/[:id]', [$this, 'getDeviceConfig']);
        $klein->respond('GET', '/devices', [$this, 'listDevices']);
        $klein->respond('POST', '/device', [$this, 'addNewDevice']);
        $klein->respond('DELETE', '/device', [$this, 'deleteDevices']);
        $klein->respond('GET', '/device/[:id]', [$this, 'getDeviceObject']);
        $klein->respond('DELETE', '/device/[:id]', [$this, 'deleteDevice']);
        $klein->respond('PATCH', '/device/[:id]', [$this, 'updateDeviceObject']);
        return $klein;
    }

    

    public function getDeviceConfig(Request $request, Response $response) {
        echo "Hello";
        $response->body("no");
    }

    public function listDevices(Request $request, Response $response) {
        echo "Hello";
        $response->body("no");
    }

    public function getDeviceObject(Request $request, Response $response) {
        echo "Hello";
        $response->body("no");
    }

    public function addNewDevice(Request $request, Response $response) {
        echo "Hello";
        $response->body("no");
    }

    public function updateDeviceObject(Request $request, Response $response) {
        echo "Hello";
        $response->body("no");
    }

    public function deleteDevices(Request $request, Response $response) {
        echo "Hello";
        $response->body("no");
    }

    public function deleteDevice(Request $request, Response $response) {
        echo "Hello";
        $response->body("no");
    }
}