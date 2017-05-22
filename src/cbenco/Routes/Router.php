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
        $klein->respond('/', function() {
            echo "Welcome!";
        });
        $klein->onHttpError(function ($code) {
            echo $code;
        });

        return $klein;
    }
}