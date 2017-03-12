<?php

namespace cbenco\Forecaster\Interfaces;


interface ISensorDeviceModel
{
    public function getDeviceId();

    /**
     * @param mixed $deviceId
     */
    public function setDeviceId($deviceId);

    /**
     * @return mixed
     */
    public function getRegisterToken();

    /**
     * @param mixed $registerToken
     */
    public function setRegisterToken($registerToken);

    /**
     * @return mixed
     */
    public function getDeviceName();

    /**
     * @param mixed $deviceName
     */
    public function setDeviceName($deviceName);

    /**
     * @return \DateTime
     */
    public function getRegisterDate() : \DateTime;

    /**
     * @param \DateTime $registerDate
     */
    public function setRegisterDate(\DateTime $registerDate);

    /**
     * @return ConfigurationModel
     */
    public function getConfigObject() : int;

    /**
     * @param ConfigurationModel $configObject
     */
    public function setConfigObject(int $configObjectId);
}