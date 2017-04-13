<?php

namespace cbenco\Forecaster\Controller;

use cbenco\Forecaster\Adapter\WeatherObjectAdapter;
use cbenco\Science\Math\Regression\LinearRegression;
use cbenco\Science\Math\Regression\QuadraticRegression;
use cbenco\Forecaster\Models\WeatherObjectModel;

class ForecastController {

	public function getQuadraticRegression (float $varX) : WeatherObjectModel {
		return $this->parseRegression(
			$varX,
			$this->calculateQuadraticRegression($varX)
		);
	}

	public function getLinearRegression (float $varX) : WeatherObjectModel {
		return $this->parseRegression(
			$varX,
			$this->calculateLinearRegression()
		);
	}

	public function getBestRegression(float $varX) : WeatherObjectModel {
		return $this->parseRegression(
			$varX,
			$this->calculateBestRegression()
		);
	}

	public function getListOfRegression(float $fromX, float $toX, float $stepping, string $regression = "best") :array {
		$bestRegression = null;
		switch ($regression) {
			case 'linear':
			case 'lin':
				$bestRegression = $this->calculateLinearRegression();
				break;
			case 'quadratic':
			case 'quad':
				$bestRegression = $this->calculateQuadraticRegression();
				break;
			case 'best':
			default:
				$bestRegression = $this->calculateBestRegression();
				break;
		}
		$listOfWeatherObjects = [];
		$counter = $fromX;
		while (($fromX + $toX) > $counter) {
			$listOfWeatherObjects[] = $this->parseRegression($counter, $bestRegression);
			$counter += $stepping;
		}
		return $listOfWeatherObjects; 
	}

	public function calculateQuadraticRegression() : array {
		return $this->calculateRegression('cbenco\\Science\\Math\\Regression\\QuadraticRegression');
	}

	public function calculateLinearRegression() : array {
		return $this->calculateRegression('cbenco\\Science\\Math\\Regression\\LinearRegression');
	}

	public function calculateBestRegression() : array {
		$linReg = $this->calculateLinearRegression();
		$quadReg = $this->calculateQuadraticRegression();
		if ($this->getCorrelationCoefficient($quadReg) >= $this->getCorrelationCoefficient($linReg)) {
			return $quadReg;
		}
		return $linReg;
	}

	private function getCorrelationCoefficient(array $regressionArray) : float {
		$coefficient = 0;
		foreach ($regressionArray as $regression) {
			$coefficient += $regression->getCorrelationCoefficient();
		}
		return $coefficient / count($regressionArray);
	}

	private function prepareData() : array {
		$weatherObjectAdapter = new WeatherObjectAdapter();
		$tmpArray = [
			"temperature" => [],
			"brightness" => [],
			"pressure" => [],
			"humidity" => []
		];
		$dbResult = $weatherObjectAdapter->getWeatherObjectFromDatabase();
		for ($index = 0; $index < count($dbResult); $index++) {
			$weatherObject = $dbResult[$index];
			$tmpArray["temperature"][$index + 1] = $weatherObject->getTemperature();
			$tmpArray["brightness"][$index + 1] = $weatherObject->getBrightness();
			$tmpArray["pressure"][$index + 1] = $weatherObject->getPressure();
			$tmpArray["humidity"][$index + 1] = $weatherObject->getHumidity();
		}
		return $tmpArray;
	}

	private function parseRegression(float $varX, array $regressionArray) : WeatherObjectModel {
		$weatherObject = new WeatherObjectModel;
		$weatherObject->setUId($varX);
		foreach ($regressionArray as $key => $column) {
			$val = $column->getValueOf($varX);
			switch ($key) {
				case 'temperature':
					$weatherObject->setTemperature($val);
					break;
				case 'brightness':
					$weatherObject->setBrightness($val);
					break;
				case 'pressure':
					$weatherObject->setPressure($val);
					break;
				case 'humidity':
					$weatherObject->setHumidity($val);
					break;
			}
		}
		return $weatherObject;
	}

	private function calculateRegression(string $regression) : array {
		$weatherData = $this->prepareData();
		$regressionArray = [];
		foreach ($weatherData as $key => $column) {
			$regressionArray[$key] = new $regression($column);
		}
		return $regressionArray;
	}
}