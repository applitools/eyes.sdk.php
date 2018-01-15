<?php

namespace Applitools;

/**
 * Eyes session results.
 */
class SessionResults {
    /** @var string */
    private $id;

    /** @var int */
    private $revision;

    /** @var string */
    private $runningSessionId;

    /** @var boolean */
    private $isAborted;

    /** @var boolean */
    private $isStarred;

    /** @var StartInfo */
    private $startInfo;

    /** @var string */
    private $batchId;

    /** @var string */
    private $secretToken;

    /** @var string */
    private $state;

    /** @var string */
    private $status;

    /** @var boolean */
    private $isDefaultStatus;

    /** @var \DateTime */
    private $startedAt;

    /** @var int */
    private $duration;

    /** @var boolean */
    private $isDifferent;

    /** @var BaselineEnv */
    private $env;

    /** @var Branch */
    private $branch;

    /** @var ExpectedAppOutput[] */
    private $expectedAppOutput;

    /** @var ActualAppOutput[] */
    private $actualAppOutput;

    /** @var string */
    private $baselineId;

    /** @var string */
    private $baselineRevId;

    /** @var string */
    private $scenarioId;

    /** @var string */
    private $scenarioName;

    /** @var string */
    private $appId;

    /** @var string */
    private $baselineModelId;

    /** @var string */
    private $baselineEnvId;

    /** @var BaselineEnv */
    private $baselineEnv;

    /** @var string */
    private $appName;

    /** @var string */
    private $baselineBranchName;

    /** @var boolean */
    private $isNew;


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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getRevision()
    {
        return $this->revision;
    }

    /**
     * @return string
     */
    public function getRunningSessionId()
    {
        return $this->runningSessionId;
    }

    /**
     * @return bool
     */
    public function isIsAborted()
    {
        return $this->isAborted;
    }

    /**
     * @return bool
     */
    public function isIsStarred()
    {
        return $this->isStarred;
    }

    /**
     * @return StartInfo
     */
    public function getStartInfo()
    {
        return $this->startInfo;
    }

    /**
     * @return string
     */
    public function getBatchId()
    {
        return $this->batchId;
    }

    /**
     * @return string
     */
    public function getSecretToken()
    {
        return $this->secretToken;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return bool
     */
    public function isIsDefaultStatus()
    {
        return $this->isDefaultStatus;
    }

    /**
     * @return \DateTime
     */
    public function getStartedAt()
    {
        return $this->startedAt;
    }

    /**
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @return bool
     */
    public function isIsDifferent()
    {
        return $this->isDifferent;
    }

    /**
     * @return BaselineEnv
     */
    public function getEnv()
    {
        return $this->env;
    }

    /**
     * @return Branch
     */
    public function getBranch()
    {
        return $this->branch;
    }

    /**
     * @return ExpectedAppOutput[]
     */
    public function getExpectedAppOutput()
    {
        return $this->expectedAppOutput;
    }

    /**
     * @return ActualAppOutput[]
     */
    public function getActualAppOutput()
    {
        return $this->actualAppOutput;
    }

    /**
     * @return string
     */
    public function getBaselineId()
    {
        return $this->baselineId;
    }

    /**
     * @return string
     */
    public function getBaselineRevId()
    {
        return $this->baselineRevId;
    }

    /**
     * @return string
     */
    public function getScenarioId()
    {
        return $this->scenarioId;
    }

    /**
     * @return string
     */
    public function getScenarioName()
    {
        return $this->scenarioName;
    }

    /**
     * @return string
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @return string
     */
    public function getBaselineModelId()
    {
        return $this->baselineModelId;
    }

    /**
     * @return string
     */
    public function getBaselineEnvId()
    {
        return $this->baselineEnvId;
    }

    /**
     * @return BaselineEnv
     */
    public function getBaselineEnv()
    {
        return $this->baselineEnv;
    }

    /**
     * @return string
     */
    public function getAppName()
    {
        return $this->appName;
    }

    /**
     * @return string
     */
    public function getBaselineBranchName()
    {
        return $this->baselineBranchName;
    }

    /**
     * @return bool
     */
    public function isIsNew()
    {
        return $this->isNew;
    }


}