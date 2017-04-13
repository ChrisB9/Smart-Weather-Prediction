<?php

namespace cbenco\Routes;
use cbenco\Forecaster\Adapter\WeatherObjectAdapter;
use Klein\Klein;
use Klein\Request;
use Klein\Response;
use cbenco\Routes\RoutesHelper;
use cbenco\Forecaster\Controller\ForecastController;

class WeatherObjectRoutes {
	private $weatherObjectAdapter;
    private $routeHelper;

	public function __construct(WeatherObjectAdapter $weatherObjectAdapter) {
		$this->weatherObjectAdapter = $weatherObjectAdapter;
	}

	public function getWeatherRoutes(Klein $klein) {
		$klein->respond('GET', '/weather', [$this, 'listWeatherObjects']);
        $klein->respond('POST', '/weather', [$this, 'addNewWeatherObject']);
        //$klein->respond('GET', '/weather/token/[:token]', [$this, 'addNewWeatherObjectByToken']);
        $klein->respond('POST', '/weather/token/[:token]', [$this, 'addNewWeatherObjectByToken']);
        $klein->respond('GET', '/weather/[:id]', [$this, 'getWeatherObject']);
        $klein->respond('DELETE', '/weather/[:id]', [$this, 'deleteWeatherObject']);
        $klein->respond('PUT', '/weather/[:id]', [$this, 'replaceWeatherObject']);
        $klein->respond('PATCH', '/weather/[:id]', [$this, 'updateWeatherObject']);
        $klein->respond('GET', '/[vorhersager|forecaster]/regression', [$this, 'getLatestForecasterRegression']);
        $klein->respond('GET', '/[vorhersager|forecaster]/regression/[a|q|l:regtype]/[:x]', [$this, 'getForecasterRegression']);
        $klein->respond('GET', '/[vorhersager|forecaster]/regression/[:to]', [$this, 'getForecasterRegressionList']);
        return $klein;
	}

	public function listWeatherObjects(Request $request, Response $response) {
        $result = $this->weatherObjectAdapter->getWeatherObjectFromDatabase(
            null,
            isset($request->limit) ? $request->limit : 0
        );
        $response->json(
            array_map(function($n){return (string) $n;}, $result),
            $request->callback
        );
    }

    public function getWeatherObject(Request $request, Response $response) {
        if ((int) $request->id < 1) {
            $response->json(false, $request->callback);
            return;
        }
    	$wObject = $this->weatherObjectAdapter->getWeatherObjectFromDatabase(
        		["id" => $request->id]
        	);
    	if (count($wObject) > 0) {
	        $response->json( json_decode((string) $wObject[0]), $request->callback);
	        return;
    	}
    	$response->json(false, $request->callback);
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

    public function addNewWeatherObjectByToken(Request $request, Response $response) {
        $data = json_decode("{".$request->data."}");
        $object = json_encode([
            "temperature" => $data->data->environment[2],
            "pressure" => $data->data->environment[1],
            "humidity" => $data->data->environment[0],
            "brightness" => $data->data->light,
            "sensorObjectId" => 1
        ]); 
        if ($this->weatherObjectAdapter->addWeatherObjectByTokenToDatabase(
                $request->token,
                $this->weatherObjectAdapter->objectToWeatherObject($object)
            )) {
            $response->json(true);
            return;
        }
        $response->json(false);
    }

    public function replaceWeatherObject(Request $request, Response $response) {
        if ($this->weatherObjectAdapter->replaceWeatherObject(
        		$request->id,
        		$this->weatherObjectAdapter->objectToWeatherObject((new RoutesHelper)->getHttpFormData()->json)
        	)) {
        	$response->json(true);
        	return;
        }
        $response->json(false);
    }

    public function updateWeatherObject(Request $request, Response $response) {
    	$updateArray = (new RoutesHelper)->getFormDataArray(
            ["temperature", "humidity", "brightness", "pressure"]
        );
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

    public function getLatestForecasterRegression(Request $request, Response $response) {
        $count = (int) $this->weatherObjectAdapter->countWeatherObjects();
        $response->json((string) $this->forecastingRegression(
            $count,
            'a'
        ));
    }

    public function getForecasterRegression(Request $request, Response $response) {
        $response->json((string) $this->forecastingRegression($request->x, $request->regtype));
    }

    private function forecastingRegression(int $val, string $regtype) {
        $forecaster = new ForecastController;
        switch ($regtype) {
            case 'q':
                return $forecaster->getQuadraticRegression($val);
                break;
            case 'l':
                return $forecaster->getLinearRegression($val);
                break;
            case 'a':
                return $forecaster->getBestRegression($val);
                break;
        }
    }

    public function getForecasterRegressionList(Request $request, Response $response) {
        $forecaster = new ForecastController;
        $stepping = isset($request->step) ? (float) $request->step : 1;
        $from = isset($request->from) ? (float) $request->from : 0;
        $regression = isset($request->regression) ? $request->regression : "best";
        $response->json(
            array_map(
                function($n) {
                    return (string) $n;
                },
                $forecaster->getListOfRegression($from, $request->to, $stepping, $regression)),
            $request->callback
        );
    }
}
