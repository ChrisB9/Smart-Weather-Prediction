<?php

namespace cbenco\Forecaster\Models;
use cbenco\Forecaster\Interfaces;

class WeatherObjectModel implements Interfaces\IWeatherObjectModel
{
    /**
     * @label private variables
     */
    private $temperature;
    private $humidity;
    private $pressure;
    private $brightness;
    private $uid;

    /**
     * @label protected variables
     */
    public $sensorObjectId;
    public $creationDate;

    /**
     * @return float
     */
    public function getTemperature(): float
    {
        return $this->temperature;
    }

    public function getUId(): int {
        return $this->uid;
    }

    /**
     * @return float
     */
    public function getHumidity(): float
    {
        return $this->humidity;
    }

    /**
     * @return float
     */
    public function getPressure(): float
    {
        return $this->pressure;
    }

    /**
     * @return float
     */
    public function getBrightness(): float
    {
        return $this->brightness;
    }

    /**
     * @param $temperature float
     * @return bool
     */
    public function setTemperature(float $temperature)
    {
        if (!is_numeric($temperature)) {
            throw new \InvalidArgumentException("argument should be numeric");
        }
        $this->temperature = $temperature;
    }

    public function setUId(int $uid) {
        $this->uid = $uid;
    }

    /**
     * @param $humidity float
     * @return bool
     */
    public function setHumidity(float $humidity)
    {
        if (!is_numeric($humidity)) {
            throw new \InvalidArgumentException("argument should be numeric");
        }
        $this->humidity = $humidity;
        return true;
    }

    /**
     * @param $pressure float
     * @return bool
     */
    public function setPressure(float $pressure)
    {
        if (!is_numeric($pressure)) {
            throw new \InvalidArgumentException("argument should be numeric");
        }
        $this->pressure = $pressure;
    }

    /**
     * @param $brightness float
     * @return bool
     */
    public function setBrightness(float $brightness)
    {
        if (!is_numeric($brightness)) {
            throw new \InvalidArgumentException("argument should be numeric");
        }
        $this->brightness = $brightness;
    }

    public function setDataByJson(string $json)
    {
        $jsonObject = json_decode($json);
        if (isset($jsonObject->id)) {
            $this->uid = $jsonObject->id;
        }
        $this->sensorObjectId = $jsonObject->sensorObjectId;
        $this->setBrightness((float) $jsonObject->brightness);
        $this->setHumidity((float) $jsonObject->humidity);
        $this->setTemperature((float) $jsonObject->temperature);
        $this->setPressure((float) $jsonObject->pressure);
    }

    /**
     * @param $json string
     * WeatherObjectModel constructor.
     */
    public function __construct()
    {
        $this->creationDate = new \DateTime();
    }

    public function __toString() {
        $ret = (object) [
            "id" => $this->getUId(),
            "sensorObjectId" => $this->sensorObjectId,
            "brightness" => $this->getBrightness(),
            "humidity" => $this->getHumidity(),
            "temperature" => $this->getTemperature(),
            "pressure" => $this->getPressure(),
            "date" => $this->creationDate
        ];
        return json_encode($ret);
    }
}