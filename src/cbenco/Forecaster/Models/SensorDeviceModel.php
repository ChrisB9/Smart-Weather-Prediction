<?php

namespace cbenco\Forecaster\Models;
use cbenco\Forecaster\Interfaces;

class SensorDeviceModel implements Interfaces\ISensorDeviceModel
{
    private $deviceId;
    private $registerToken;
    private $deviceName;
    private $registerDate;
    private $configObject;

    /**
     * @return mixed
     */
    public function getDeviceId()
    {
        return $this->deviceId;
    }

    /**
     * @param mixed $deviceId
     */
    public function setDeviceId($deviceId)
    {
        $this->deviceId = $deviceId;
    }

    /**
     * @return mixed
     */
    public function getRegisterToken()
    {
        return $this->registerToken;
    }

    /**
     * @param mixed $registerToken
     */
    public function setRegisterToken($registerToken)
    {
        $this->registerToken = $registerToken;
    }

    /**
     * @return mixed
     */
    public function getDeviceName()
    {
        return $this->deviceName;
    }

    /**
     * @param mixed $deviceName
     */
    public function setDeviceName($deviceName)
    {
        $this->deviceName = $deviceName;
    }

    /**
     * @return \DateTime
     */
    public function getRegisterDate() : \DateTime
    {
        return $this->registerDate;
    }

    /**
     * @param \DateTime $registerDate
     */
    public function setRegisterDate(\DateTime $registerDate)
    {
        $this->registerDate = $registerDate;
    }

    /**
     * @return ConfigurationModel
     */
    public function getConfigObject() : ConfigurationModel
    {
        return $this->configObject;
    }

    /**
     * @param ConfigurationModel $configObject
     */
    public function setConfigObject(ConfigurationModel $configObject)
    {
        $this->configObject = $configObject;
    }
}