<?php
/**
 * Applitools software
 */

namespace Applitools;


class StartInfo
{
    /** @var string */
    private $sessionType;

    /** @var boolean */
    private $isTransient;

    /** @var boolean */
    private $ignoreBaseline;

    /** @var string */
    private $appIdOrName;

    /** @var boolean */
    private $compareWithParentBranch;

    /** @var string */
    private $scenarioIdOrName;

    /** @var BatchInfo */
    private $batchInfo;

    /** @var BaselineEnv */
    private $environment;

    /** @var string */
    private $matchLevel;

    /** @var ImgMatchSettings */
    private $defaultMatchSettings;

    /** @var string */
    private $agentId;

    /** @var PropertyData[] */
    private $properties;


    function __construct($data = null) {
        if(is_array($data)){
            foreach($data as $key => $val) {
                if(property_exists(__CLASS__,$key)) {
                    $this->$key = $val;
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getSessionType()
    {
        return $this->sessionType;
    }

    /**
     * @return bool
     */
    public function isIsTransient()
    {
        return $this->isTransient;
    }

    /**
     * @return bool
     */
    public function isIgnoreBaseline()
    {
        return $this->ignoreBaseline;
    }

    /**
     * @return string
     */
    public function getAppIdOrName()
    {
        return $this->appIdOrName;
    }

    /**
     * @return bool
     */
    public function isCompareWithParentBranch()
    {
        return $this->compareWithParentBranch;
    }

    /**
     * @return string
     */
    public function getScenarioIdOrName()
    {
        return $this->scenarioIdOrName;
    }

    /**
     * @return BatchInfo
     */
    public function getBatchInfo()
    {
        return $this->batchInfo;
    }

    /**
     * @return BaselineEnv
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @return string
     */
    public function getMatchLevel()
    {
        return $this->matchLevel;
    }

    /**
     * @return ImgMatchSettings
     */
    public function getDefaultMatchSettings()
    {
        return $this->defaultMatchSettings;
    }

    /**
     * @return string
     */
    public function getAgentId()
    {
        return $this->agentId;
    }

    /**
     * @return PropertyData[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

}