<?php
/**
* Encapsulates data required to start session using the Session API.
*/

class SessionStartInfo {
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

    public function __construct($agentId, /*SessionType*/ $sessionType,
        $appIdOrName, $verId,
        $scenarioIdOrName, /*BatchInfo*/ $batchInfo,
        $envName, /*AppEnvironment*/ $environment,
        /*ImageMatchSettings*/ $defaultMatchSettings,
        $branchName, $parentBranchName) {
        /*ArgumentGuard.notNullOrEmpty(agentId, "agentId");
        ArgumentGuard.notNullOrEmpty(appIdOrName, "appIdOrName");
        ArgumentGuard.notNullOrEmpty(scenarioIdOrName, "scenarioIdOrName");
        ArgumentGuard.notNull(batchInfo, "batchInfo");
        ArgumentGuard.notNull(environment, "environment");
        ArgumentGuard.notNull(defaultMatchSettings, "defaultMatchSettings");*/
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

        //huge mock/ should be deleted


      /*  $this->appIdOrName = "App Id 1111";
        $this->scenarioIdOrName = "Scenario Name 1111";
        $this->branchName = "Branch name ";
        $this->batchInfo = array("startedAt"=>date("Y-m-d\TH:i:s\Z"));
        $this->environment = array(
                "displaySize" => array(
                    "width" => 1280,
                    "height" => 800
                )
        );
        $this->agentId = "mysdk/1.3";*/


    }

    public function getAgentId() {
        return $this->agentId;
    }

    public function getSessionType() {
        return $this->sessionType;
    }

    public function getAppIdOrName() {
        return $this->appIdOrName;
    }

    public function getVerId() {
        return $this->verId;
    }

    public function getScenarioIdOrName() {
        return $this->scenarioIdOrName;
    }

    public function getBatchInfo() {
        return $this->batchInfo;
    }

    public function getEnvName() {
        return $this->envName;
    }

    public function getEnvironment() {
        return $this->environment;
    }

    public function getBranchName() {
        return $this->branchName;
    }

    public function getParentBranchName() {
        return $this->parentBranchName;
    }

    public function getDefaultMatchSettings() {
        return $this->defaultMatchSettings;
    }
}