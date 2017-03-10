<?php

namespace cbenco\Forecaster\Models;


class ConfigurationModel
{
    private $wLANssID; // max size 50
    private $wLANPassPhrase; // max size 100
    private $httpApi; // max size 100
    private $httpDomain; // max size 100
    private $sensorFilename; // max size 100
    private $httpPort; // signed integer
    private $timer; // signed integer
    private $timerAutoReload; // boolean
    /**
     * @return mixed
     */
    public function getWLANssID()
    {
        return $this->wLANssID;
    }

    /**
     * @param mixed $wLANssID
     */
    public function setWLANssID($wLANssID)
    {
        if (sizeof($wLANssID) > 50) {
            throw new \InvalidArgumentException("ssId exceeds maximum length of 50");
        }
        $this->wLANssID = $wLANssID;
    }

    /**
     * @return mixed
     */
    public function getWLANPassPhrase()
    {
        return $this->wLANPassPhrase;
    }

    /**
     * @param mixed $wLANPassPhrase
     */
    public function setWLANPassPhrase($wLANPassPhrase)
    {
        if (sizeof($wLANPassPhrase) > 100) {
            throw new \InvalidArgumentException("pass phrase exceeds maximum length of 100");
        }
        $this->wLANPassPhrase = $wLANPassPhrase;
    }

    /**
     * @return mixed
     */
    public function getHttpApi()
    {
        return $this->httpApi;
    }

    /**
     * @param mixed $httpApi
     */
    public function setHttpApi($httpApi)
    {
        if (sizeof($httpApi) > 100) {
            throw new \InvalidArgumentException("url exceeds maximum length of 100");
        }
        $this->httpApi = $httpApi;
    }

    /**
     * @return mixed
     */
    public function getHttpDomain()
    {
        return $this->httpDomain;
    }

    /**
     * @param mixed $httpDomain
     */
    public function setHttpDomain($httpDomain)
    {
        if (sizeof($httpDomain) > 100) {
            throw new \InvalidArgumentException("domain exceeds maximum length of 100");
        }
        $this->httpDomain = $httpDomain;
    }

    /**
     * @return mixed
     */
    public function getSensorFilename()
    {
        return $this->sensorFilename;
    }

    /**
     * @param mixed $sensorFilename
     */
    public function setSensorFilename($sensorFilename)
    {
        if (sizeof($sensorFilename) > 100) {
            throw new \InvalidArgumentException("filename exceeds maximum length of 100");
        }
        $this->sensorFilename = $sensorFilename;
    }

    /**
     * @return mixed
     */
    public function getHttpPort()
    {
        return $this->httpPort;
    }

    /**
     * @param mixed $httpPort
     */
    public function setHttpPort(int $httpPort)
    {
        if (!is_int($httpPort)) {
            throw new \InvalidArgumentException("port has to be an integer");
        }
        $this->httpPort = $httpPort;
    }

    /**
     * @return mixed
     */
    public function getTimer()
    {
        return $this->timer;
    }

    /**
     * @param mixed $timer
     */
    public function setTimer(int $timer)
    {
        if (!is_int($timer)) {
            throw new \InvalidArgumentException("timer has to be an integer");
        }
        $this->timer = $timer;
    }

    /**
     * @return mixed
     */
    public function getTimerAutoReload()
    {
        return $this->timerAutoReload;
    }

    /**
     * @param mixed $timerAutoReload
     */
    public function setTimerAutoReload(bool $timerAutoReload)
    {
        if (!is_bool($timerAutoReload)) {
            throw new \InvalidArgumentException("autoReload flag has to be a boolean");
        }
        $this->timerAutoReload = $timerAutoReload;
    }
}
