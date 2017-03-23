<?php

namespace cbenco\Routes;

use cbenco\Forecaster\Adapter\SensorDeviceAdapter;
use Klein\Klein;
use Klein\Request;
use Klein\Response;
use cbenco\Routes\RoutesHelper;

class SensorDeviceRoutes {
	private $sensorDeviceAdapter;

	public function __construct(SensorDeviceAdapter $sensorDeviceAdapter) {
		$this->sensorDeviceAdapter = $sensorDeviceAdapter;
	}

	public function getSensorRoutes(Klein $klein) {
		$klein->respond('GET', '/config/[:id]', [$this, 'getDeviceConfig']);
        $klein->respond('GET', '/devices', [$this, 'listDevices']);
        $klein->respond('POST', '/device', [$this, 'addNewDevice']);
        $klein->respond('GET', '/device/[:id]', [$this, 'getDeviceObject']);
        $klein->respond('GET', '/device/token/[:token]', [$this, 'getDeviceObjectByToken']);
        $klein->respond('DELETE', '/device/[:id]', [$this, 'deleteDevice']);
        $klein->respond('PATCH', '/device/[:id]', [$this, 'updateDeviceObject']);
		return $klein;
	}

	public function getDeviceConfig(Request $request, Response $response) {
        $response->json(false);
    }

    public function listDevices(Request $request, Response $response) {
        $response->json(
            array_map(function($n){return (string) $n;}, $this->sensorDeviceAdapter->getSensorObjectFromDatabase())
        );
    }

    public function getDeviceObject(Request $request, Response $response) {
    	if ($request->id < 1) {
    		$response->json(false);
    		return;
    	}
        $sObject = $this->sensorDeviceAdapter->getSensorObjectFromDatabase(
        		"*",
        		["id" => $request->id]
        	);
    	if (count($sObject) > 0) {
	        $response->json( json_decode((string) $sObject[0]));
	        return;
    	}
    	$response->json(false);
    }

    public function getDeviceObjectByToken(Request $request, Response $response) {
        $sObject = $this->sensorDeviceAdapter->getSensorObjectFromDatabase(
                "*",
                ["registerToken" => $request->token]
            );
        if (count($sObject) > 0) {
            $response->json( json_decode((string) $sObject[0]));
            return;
        }
        $response->json(false);
    }

    public function addNewDevice(Request $request, Response $response) {
        if ($this->sensorDeviceAdapter->addSensorObjectToDatabase(
        		$this->sensorDeviceAdapter->objectToSensorObject($request->json)
        	)) {
        	$response->json(true);
        	return;
        }
        $response->json(false);
    }

    public function updateDeviceObject(Request $request, Response $response) {
        $possibleKeys = ["registerToken", "name", "date", "configObject"];
    	$updateArray = [];
    	foreach ($possibleKeys as $key) {
    		if (isset((new RoutesHelper)->getHttpFormData()->{$key})) {
    			$updateArray[$key] = (new RoutesHelper)->getHttpFormData()->{$key};
    		}
    	}
        if ($this->sensorDeviceAdapter->updateSensorObject($request->id, $updateArray)) {
        	$response->json(true);
        	return;
        }
        $response->json(false);
    }

    public function deleteDevice(Request $request, Response $response) {
        if ($this->sensorDeviceAdapter->deleteSensorObject($request->id)) {
        	$response->json(true);
        	return;
        }
        $response->json(false);
    }
}