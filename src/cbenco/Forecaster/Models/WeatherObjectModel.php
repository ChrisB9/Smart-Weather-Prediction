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
    private $cloudiness;
    private $windspeed;
    private $winddirection;
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
    public function getCloudiness(): float
    {
        return $this->cloudiness;
    }

    /**
     * @return float
     */
    public function getWindspeed(): float
    {
        return $this->windspeed;
    }

    /**
     * @return float
     */
    public function getWinddirection(): float
    {
        return $this->winddirection;
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
        $this->temperature = $temperature;
    }

    public function setUId(int $uid) {
        $this->uid = $uid;
    }

    /**
     * @param $cloudiness float
     */
    public function setCloudiness(float $cloudiness)
    {
        $this->cloudiness = $cloudiness;
    }

    /**
     * @param $windspeed float
     */
    public function setWindspeed(float $windspeed)
    {
        $this->windspeed = $windspeed;
    }

    /**
     * @param $winddirection int
     */
    public function setWinddirection(int $winddirection)
    {
        if ($winddirection < 0 || $winddirection > 360) {
            throw new \Exception($winddirection.': winddirection has to be between 0 and 360 degree');
        }
        $this->winddirection = $winddirection;
    }

    /**
     * @param $humidity float
     * @return bool
     */
    public function setHumidity(float $humidity)
    {
        $this->humidity = $humidity;
        return true;
    }

    /**
     * @param $pressure float
     * @return bool
     */
    public function setPressure(float $pressure)
    {
        $this->pressure = $pressure;
    }

    /**
     * @param $brightness float
     * @return bool
     */
    public function setBrightness(float $brightness)
    {
        $this->brightness = $brightness;
    }

    public function setDataByJson(string $json)
    {
        $jsonObject = json_decode($json);
        if (isset($jsonObject->id)) {
            $this->setUId($jsonObject->id);
        }
        $this->sensorObjectId = $jsonObject->sensorObjectId;
        $this->setBrightness((float) $jsonObject->brightness);
        $this->setHumidity((float) $jsonObject->humidity);
        $this->setTemperature((float) $jsonObject->temperature);
        $this->setPressure((float) $jsonObject->pressure);
        $this->setCloudiness((float) $jsonObject->cloudiness);
        $this->setWinddirection((float) $jsonObject->winddirection);
        $this->setWindspeed((float) $jsonObject->windspeed);
        if (isset($jsonObject->date)) {
            $this->creationDate = new \DateTime($jsonObject->date);
        }
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
            "cloudiness" => $this->getCloudiness(),
            "windspeed" => $this->getWindspeed(),
            "winddirection" => $this->getWinddirection(),
            "date" => $this->creationDate
        ];
        return json_encode($ret);
    }
}