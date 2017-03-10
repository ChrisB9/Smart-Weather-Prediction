<?php

namespace cbenco\Routes;
use cbenco\Forecaster\Adapter;
use Klein\Klein;
use Klein\Request;
use Klein\Response;
use cbenco\Routes\RoutesHelper;

class WeatherObjectRoutes {
	private $weatherObjectAdapter;

	public function __construct(Adapter\WeatherObjectAdapter $weatherObjectAdapter) {
		$this->weatherObjectAdapter = $weatherObjectAdapter;
	}

	public function getWeatherRoutes(Klein $klein) {
		$klein->respond('GET', '/weather', [$this, 'listWeatherObjects']);
        $klein->respond('POST', '/weather', [$this, 'addNewWeatherObject']);
        $klein->respond('GET', '/weather/[:id]', [$this, 'getWeatherObject']);
        $klein->respond('DELETE', '/weather/[:id]', [$this, 'deleteWeatherObject']);
        $klein->respond('PUT', '/weather/[:id]', [$this, 'replaceWeatherObject']);
        $klein->respond('PATCH', '/weather/[:id]', [$this, 'updateWeatherObject']);
        return $klein;
	}

	public function listWeatherObjects(Request $request, Response $response) {
        $response->json(
            array_map(function($n){return (string) $n;}, $this->weatherObjectAdapter->getWeatherObjectFromDatabase())
        );
    }

    public function getWeatherObject(Request $request, Response $response) {
    	$wObject = $this->weatherObjectAdapter->getWeatherObjectFromDatabase(
        		"*",
        		["id" => $request->id]
        	);
    	if (count($wObject) > 0) {
	        $response->json( json_decode((string) $wObject[0]));
	        return;
    	}
    	$response->json(false);
    }

    public function addNewWeatherObject(Request $request, Response $response) {
        if ($this->weatherObjectAdapter->addWeatherObjectToDatabase(
        		$this->weatherObjectAdapter->objectToWeatherObject($request->json)
        	)) {
        	$response->json(true);
        	return;
        }
        $response->json(false);
    }

    public function replaceWeatherObject(Request $request, Response $response) {
        if ($this->weatherObjectAdapter->replaceWeatherObject(
        		$request->id,
        		$this->weatherObjectAdapter->objectToWeatherObject(RoutesHelper::getHttpFormData()->json)
        	)) {
        	$response->json(true);
        	return;
        }
        $response->json(false);
    }

    public function updateWeatherObject(Request $request, Response $response) {
    	$possibleKeys = ["temperature", "humidity", "brightness", "pressure"];
    	$updateArray = [];
    	foreach ($possibleKeys as $key) {
    		if (isset(RoutesHelper::getHttpFormData()->{$key})) {
    			$updateArray[$key] = RoutesHelper::getHttpFormData()->{$key};
    		}
    	}
        if ($this->weatherObjectAdapter->updateWeatherObject($request->id, $updateArray)) {
        	$response->json(true);
        	return;
        }
        $response->json(false);
    }

    public function deleteWeatherObject(Request $request, Response $response) {
        if ($this->weatherObjectAdapter->deleteWeatherObject($request->id)) {
        	$response->json(true);
        	return;
        }
        $response->json(false);
    }
}