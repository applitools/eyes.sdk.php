<?php
namespace Applitools;

/**
 * Encapsulates data required to start session using the Session API.
 */
class SessionStartInfo
{
    private $agentId;
    private $sessionType;
    private $appIdOrName;
    private $verId;
    private $scenarioIdOrName;
    private $batchInfo;
    private $envName;
    private $environment;
    private $branchName;
    private $parentBranchName;
    private $defaultMatchSettings;

    /** @var PropertyData[] */
    private $properties;

    public function __construct($agentId,
                                $sessionType,
                                $appIdOrName, $verId,
                                $scenarioIdOrName,
                                BatchInfo $batchInfo,
                                $envName,
                                AppEnvironment $environment,
                                ImageMatchSettings $defaultMatchSettings,
                                $branchName, $parentBranchName,
                                $properties)
    {
        $this->agentId = $agentId;
        $this->sessionType = $sessionType;
        $this->appIdOrName = $appIdOrName;
        $this->verId = $verId;
        $this->scenarioIdOrName = $scenarioIdOrName;
        $this->batchInfo = $batchInfo; // class
        $this->envName = $envName;
        $this->environment = $environment;  // class
        $this->defaultMatchSettings = $defaultMatchSettings; // class
        $this->branchName = $branchName;
        $this->parentBranchName = $parentBranchName;
        $this->properties = $properties;
    }

    public function getAgentId()
    {
        return $this->agentId;
    }

    public function getSessionType()
    {
        return $this->sessionType;
    }

    public function getAppIdOrName()
    {
        return $this->appIdOrName;
    }

    public function getVerId()
    {
        return $this->verId;
    }

    public function getScenarioIdOrName()
    {
        return $this->scenarioIdOrName;
    }

    public function getBatchInfo()
    {
        return $this->batchInfo->getAsArray();
    }

    public function getEnvName()
    {
        return $this->envName;
    }

    public function getEnvironment()
    {
        return $this->environment;
    }

    public function getBranchName()
    {
        return $this->branchName;
    }

    public function getParentBranchName()
    {
        return $this->parentBranchName;
    }

    public function getDefaultMatchSettings()
    {
        return $this->defaultMatchSettings;
    }

    public function getProperties()
    {
        return $this->properties;
    }
}