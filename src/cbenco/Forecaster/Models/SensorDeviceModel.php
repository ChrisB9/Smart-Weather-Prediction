<?php

namespace cbenco\Forecaster\Models;
use cbenco\Forecaster\Interfaces;

class SensorDeviceModel implements Interfaces\ISensorDeviceModel
{
    private $deviceId;
    private $registerToken;
    private $deviceName;
    private $registerDate;
    private $configObjectId;

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
    public function getConfigObject() : int
    {
        return $this->configObjectId;
    }

    /**
     * @param ConfigurationModel $configObject
     */
    public function setConfigObject(int $configObjectId)
    {
        $this->configObjectId = $configObjectId;
    }

    public function __construct() {
        $this->setRegisterDate(new \DateTime);
    }

    public function __toString() {
        $ret = (object) [
            "id" => $this->getDeviceId(),
            "registerToken" => $this->getRegisterToken(),
            "name" => $this->getDeviceName(),
            "date" => $this->getRegisterDate(),
            "configObject" => $this->getConfigObject()
        ];
        return json_encode($ret);
    }

    public function setDataByJson(string $json)
    {
        $jsonObject = json_decode($json);
        if (isset($jsonObject->id)) {
            $this->setDeviceId($jsonObject->id);
        }
        $this->setDeviceName($jsonObject->name);
        $this->setRegisterToken($jsonObject->registerToken);
        $this->setConfigObject($jsonObject->configObject);
        if (isset($jsonObject->date)) {
            $this->setRegisterDate(new \DateTime($jsonObject->date));
        }
    }
}