<?php

namespace cbenco\Tests\APITests;

//use cbenco\Tests\APITests\;
use Klein\Klein;
use Klein\Request;
use Klein\Response;
use cbenco\Routes\Router;
use cbenco\Routes\WeatherObjectRoutes as WOR;
use cbenco\Routes\SensorDeviceRoutes as SDR;
use cbenco\Forecaster\Models\WeatherObjectModel;
use cbenco\Forecaster\Models\SensorDeviceModel;
use cbenco\Database;
use cbenco\Forecaster\Adapter;
use PHPUnit\Framework\TestCase;

class RoutesTest extends TestCase {

	protected $klein_app;
	protected $router;
	protected $sqliteDatabaseConnection;
	protected $weatherObjectAdapter;
    protected $sensorObjectAdapter;

	public function setUp() {
		$this->router = new Router("/swp2/");
		$this->klein_app = $this->router->getRouter();
		$this->klein_app->service()->bind(new Request(), new Response());
		$this->sqliteDatabaseConnection = new Database\DatabaseFactory("sqliteTest");
        $this->weatherObjectAdapter = new Adapter\WeatherObjectAdapter($this->sqliteDatabaseConnection);
        $this->sensorObjectAdapter = new Adapter\SensorDeviceAdapter($this->sqliteDatabaseConnection);
        $this->klein_app = (new WOR($this->weatherObjectAdapter))->getWeatherRoutes($this->klein_app);
        $this->klein_app = (new SDR($this->sensorObjectAdapter))->getSensorRoutes($this->klein_app);
        $_SERVER["CONTENT_TYPE"] = 'application/x-www-form-urlencoded';
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

    /**
     * 
     */
	public function testWeatherRoutes() {
        $this->sensorObjectAdapter->addSensorObjectToDatabase($this->getSensorObjectModel());
        $wObj = $this->getWeatherObjectModel();
        $wObj->sensorObjectId = $this->sensorObjectAdapter->getLastInsertedId();
        $this->weatherObjectAdapter->addWeatherObjectToDatabase($wObj);
		$dbselect = $this->weatherObjectAdapter->getWeatherObjectFromDatabase();
		$wObject = null;
		if (count($dbselect) > 0) {
			$wObject = $dbselect[0];
		}
		$arrayOfDatabaseResultsForWeatherRoutes = [
			[
				"route" => ["/"],
				"result" => "Welcome!"
			],
    		[
    			"route" => ["/weather"], 
    			"result" => json_encode(array_map(function($n) {return (string) $n;}, $dbselect))
    		],
    		[
    			"route" => ["/weather", "POST", [
    				"json" => '{"temperature": 32.30,"humidity": 20.1,"pressure": 1004,"brightness": 90,"sensorObjectId":1}'
    			]], 
    			"result" => json_encode(true) 
    		],
    		[
    			"route" => ["/weather/-1"], 
    			"result" => json_encode(false)
    		]/*, --> TODO: Add test for PATCH & PUT ,
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
    		]*/,
    		[
    			"route" => ["/weather/".($wObject != null ? $wObject->getUId() : "-1")], 
    			"result" => ($wObject != null ? (string) $wObject : json_encode(false)) 
    		],
    		[
    			"route" => ["/weather/".($wObject != null ? $wObject->getUId() : "-1"), "DELETE"], 
    			"result" => json_encode(true) 
    		]
    	];
    	foreach ($arrayOfDatabaseResultsForWeatherRoutes as $routeDataset) {
    		$objectRoute = json_encode(
    			$this->removeDate(
    				json_decode(
    					$this->dispatchAndReturnOutput(
    						Mocks\MockRequestFactory::create(...$routeDataset["route"])
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
                            Mocks\MockRequestFactory::create(...$routeDataset["route"])
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