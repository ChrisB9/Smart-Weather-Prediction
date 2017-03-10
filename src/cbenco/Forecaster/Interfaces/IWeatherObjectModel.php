<?php

namespace cbenco\Forecaster\Interfaces;


interface IWeatherObjectModel
{
    public function getTemperature(): float;
    public function getHumidity(): float;
    public function getPressure(): float;
    public function getBrightness(): float;
    public function setTemperature(float $temperature);
    public function setHumidity(float $humidity);
    public function setPressure(float $pressure);
    public function setBrightness(float $brightness);
}
