<?php

namespace cbenco\Tests\APITests;

use Klein\Klein;
use Klein\Request;
use Klein\Response;
use cbenco\Routes\Router;
use cbenco\Config\BaseConfig;
use cbenco\Routes\WeatherObjectRoutes as WOR;
use cbenco\Routes\SensorDeviceRoutes as SDR;
use cbenco\Forecaster\Adapter\WeatherObjectAdapter;
use cbenco\Forecaster\Adapter\SensorDeviceAdapter;
use cbenco\Forecaster\Models\WeatherObjectModel;
use cbenco\Forecaster\Models\SensorDeviceModel;
use cbenco\Database\DatabaseFactory;
use cbenco\Forecaster\Adapter;
use cbenco\Tests\APITests\Mocks\MockRequestFactory;
use cbenco\Tests\APITests\Mocks\MockRoutesHelper;
use PHPUnit\Framework\TestCase;

class RoutesTest extends TestCase {

	protected $klein_app;
	protected $router;
	protected $sqliteDatabaseConnection;
    protected $rethinkConnection;
	protected $weatherObjectAdapter;
    protected $sensorObjectAdapter;

	public function setUp() {
		$this->router = new Router((new BaseConfig)->getBaseUrl());
		$this->klein_app = $this->router->getRouter();
		$this->klein_app->service()->bind(new Request(), new Response());
        $this->weatherObjectAdapter = new WeatherObjectAdapter("weatherobjectadapterTest");
        $this->sensorObjectAdapter = new SensorDeviceAdapter("sensordeviceadapterTest");
        $this->klein_app = (new WOR($this->weatherObjectAdapter))->getWeatherRoutes($this->klein_app);
        $this->klein_app = (new SDR($this->sensorObjectAdapter))->getSensorRoutes($this->klein_app);
	}

	protected function dispatchAndReturnOutput($request = null, $response = null)
    {
        return $this->klein_app->dispatch(
            $request,
            $response,
            false,
            Klein::DISPATCH_CAPTURE_AND_RETURN
        );
    }

    public function getWeatherObjectModel(): WeatherObjectModel {
		$wom = new WeatherObjectModel();
		$wom->setBrightness(20);
		$wom->setHumidity(100);
		$wom->setPressure(904);
		$wom->setTemperature(30);
		$wom->sensorObjectId = 1;
		return $wom;
	}

    public function getSensorObjectModel(): SensorDeviceModel {
        $sdm = new SensorDeviceModel();
        $sdm->setDeviceName("test-device");
        $sdm->setRegisterToken("123456");
        $sdm->setConfigObject(1);
        return $sdm;
    }

    public function getWeatherObjectFromDatabase() {
        $this->sensorObjectAdapter->addSensorObjectToDatabase($this->getSensorObjectModel());
        $wObj = $this->getWeatherObjectModel();
        $wObj->sensorObjectId = $this->sensorObjectAdapter->getLastInsertedId();
        $this->weatherObjectAdapter->addWeatherObjectToDatabase($wObj);
        $dbselect = $this->weatherObjectAdapter->getWeatherObjectFromDatabase();
        $wObject = null;
        if (count($dbselect) > 0) {
            $wObject = $dbselect[0];
        }
        return [$wObject, $dbselect];
    }

    /**
     * 
     */
    public function testGetWeatherObjectById() {
        $wObject = $this->getWeatherObjectFromDatabase()[0];
        $route = "/weather/".($wObject != null ? $wObject->getUId() : "-1");
        $objectRoute = $this->dispatchAndReturnOutput(
            MockRequestFactory::create($route)
        );
        $this->assertSame(
            ($wObject != null ? (string) $wObject : json_encode(false)),
            $objectRoute
        );
    }

    public function testDeleteWeatherObjectById() {
        $wObject = $this->getWeatherObjectFromDatabase()[0];
        $route = ["/weather/".($wObject != null ? $wObject->getUId() : "-1"), "DELETE"];
        $objectRoute = $this->dispatchAndReturnOutput(
            MockRequestFactory::create(...$route)
        );
        $this->assertSame(json_encode(true), $objectRoute);
    }

    public function testBaseRoute() {
        $objectRoute = $this->dispatchAndReturnOutput(
            MockRequestFactory::create("/")
        );
        $this->assertSame("Welcome!", $objectRoute);
    }

    public function testGetAllWeatherDataRoute() {
        $select = $this->getWeatherObjectFromDatabase()[1];
        $result = json_encode(array_map(function($n) {return (string) $n;}, $select));
        $objectRoute = $this->dispatchAndReturnOutput(
            MockRequestFactory::create("/weather")
        );
        $this->assertSame($result, $objectRoute);
    }

    public function testPostWeatherDataRoute() {
        $route = [
            "/weather",
            "POST", [
                "json" => 
                '{"temperature": 32.30,"humidity": 20.1,"pressure": 1004,"brightness": 90,"sensorObjectId":1}'
            ]
        ];
        $objectRoute = $this->dispatchAndReturnOutput(
            MockRequestFactory::create(...$route)
        );
        $this->assertSame(json_encode(true), $objectRoute);
    }

    // public function testGetLatestForecasterRegression() {
    //     $objectRoute = $this->dispatchAndReturnOutput(
    //         MockRequestFactory::create("/vorhersager/regression")
    //     );
    //     $this->assertJson($objectRoute);
    // }

    // public function testGetForecasterRegression() {
    //     $objectRoute = $this->dispatchAndReturnOutput(
    //         MockRequestFactory::create("/vorhersager/regression/l/10")
    //     );
    //     $this->assertJson($objectRoute, "linear");
    //     $objectRoute = $this->dispatchAndReturnOutput(
    //         MockRequestFactory::create("/vorhersager/regression/a/10")
    //     );
    //     $this->assertJson($objectRoute, "all");
    //     $objectRoute = $this->dispatchAndReturnOutput(
    //         MockRequestFactory::create("/vorhersager/regression/q/10")
    //     );
    //     $this->assertJson($objectRoute, "quadratic");
    // }

    // public function testGetForecasterRegressionList() {
    //     $objectRoute = $this->dispatchAndReturnOutput(
    //         MockRequestFactory::create("/vorhersager/regression/10")
    //     );
    //     $this->assertJson($objectRoute);
    // }

    public function testGetWeatherObjectByNegativeId() {
        $objectRoute = $this->dispatchAndReturnOutput(
            MockRequestFactory::create("/weather/-1")
        );
        $this->assertSame(json_encode(false), $objectRoute);
    }

    public function testGetWeatherObjectByHighId() {
        $objectRoute = $this->dispatchAndReturnOutput(
            MockRequestFactory::create("/weather/100000")
        );
        $this->assertSame(json_encode(false), $objectRoute);
    }

    public function testPostWeatherDataByTokenRoute() {
        $route = [
            "/weather/token/123456",
            "POST", [
                "data" => 
                '"data":{"light":429120.000000,"environment":[18,99979.000000,32170.000000]}'
            ]
        ];
        $objectRoute = $this->dispatchAndReturnOutput(
            MockRequestFactory::create(...$route)
        );
        $this->assertSame(json_encode(true), $objectRoute);
    }

    // --> TODO: Add test for PATCH & PUT
	/*
		$putAndPatchRoutes = [
    		[
    			"route" => ["/weather/".($wObject != null ? $wObject->getUId() : "-1"), "PUT", [
    				"json" => '{"temperature": 33,"humidity": 20.1,"pressure": 994.4,"brightness": 90,"sensorObjectId": 1}'
    			]],
    			"result" => json_encode(true)
    		],
    		[
    			"route" => ["/weather/".($wObject != null ? $wObject->getUId() : "-1"), "PATCH" ,[
    				"temperature" => 10
    			]],
    			"result" => json_encode(true)
    		]
    	];
	*/
    public function testSensorRoutes() {
        $this->sensorObjectAdapter->addSensorObjectToDatabase($this->getSensorObjectModel());
        $dbselect = $this->sensorObjectAdapter->getSensorObjectFromDatabase();
        $sObject = count($dbselect) > 0 ? $dbselect[0] : null;
        $arrayOfDatabaseResultsForSensorRoutes = [
            [
                "route" => ["/devices"], 
                "result" => json_encode(array_map(function($n) {return (string) $n;}, $dbselect))
            ],
            [
                "route" => ["/device", "POST", [
                    "json" => '{"name": "gandalf","registerToken": "hash9","configObject": 1}'
                ]], 
                "result" => json_encode(true) 
            ],
            [
                "route" => ["/device/-1"], 
                "result" => json_encode(false)
            ],
            [
                "route" => ["/device/".($sObject != null ? $sObject->getDeviceId() : "-1")], 
                "result" => ($sObject != null ? (string) $sObject : json_encode(false)) 
            ],
            [
                "route" => ["/device/".($sObject != null ? $sObject->getDeviceId() : "-1"), "DELETE"], 
                "result" => json_encode(true) 
            ]
        ];
        foreach ($arrayOfDatabaseResultsForSensorRoutes as $routeDataset) {
            $objectRoute = json_encode(
                $this->removeDate(
                    json_decode(
                        $this->dispatchAndReturnOutput(
                            MockRequestFactory::create(...$routeDataset["route"])
                        )
                    )
                )
            );
            $objectResult = json_encode(
                $this->removeDate(
                    json_decode($routeDataset["result"])
                )
            );
            $this->assertSame(
                $objectResult,
                $objectRoute
            );
        }
    }

	private function removeDate($object) {
		unset($object->date);
		return $object;
	}

    public function tearDown() {
        foreach ($this->sensorObjectAdapter->getSensorObjectFromDatabase() as $result) {
            $this->sensorObjectAdapter->deleteSensorObject($result->getDeviceId());
        }
        foreach ($this->weatherObjectAdapter->getWeatherObjectFromDatabase() as $result) {
            $this->weatherObjectAdapter->deleteWeatherObject($result->getUId());
        }
    }
}