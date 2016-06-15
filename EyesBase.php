<?php
require "ServerConnectorFactory.php";
require "Logger.php";
require "SessionStartInfo.php";
class EyesBase {

    const SEQUENTIAL = "aaa";  ///Session type ???
    const DEFAULT_MATCH_TIMEOUT = 2; // Seconds
    const  USE_DEFAULT_TIMEOUT = -1;

    private $isDisabled;
    private $isOpen;
    private $serverConnector;
    private $runningSession;
    private $viewportSize;/*RectangleSize*/
    private $batch;/*BatchInfo*/
    private $sessionType;/*it should be class to*/
    private $currentAppName;
    private $appName;
    private $testName;
    private $defaultMatchSettings;
    private $baselineName;
    private $branchName;
    private $parentBranchName;

    public function __construct($serverUrl)
    {

       /* if (isDisabled) {
            userInputs = null;
            return;
        }

        //ArgumentGuard.notNull(serverUrl, "serverUrl");

        logger = new Logger();
        scaleProviderHandler = new SimplePropertyHandler<ScaleProvider>();
        scaleProviderHandler.set(new NullScaleProvider());
        positionProvider = new InvalidPositionProvider();
        scaleMethod = ScaleMethod.getDefault();
        viewportSize = null;
 */
        $logger = "";
        $this->serverConnector = ServerConnectorFactory::create($logger, $this->getBaseAgentId(), $serverUrl);
  /*    matchTimeout = DEFAULT_MATCH_TIMEOUT;
        runningSession = null;
        defaultMatchSettings = new ImageMatchSettings();
        failureReports = FailureReports.ON_CLOSE;
        userInputs = new ArrayDeque<Trigger>();

        // New tests are automatically saved by default.
        saveNewTests = true;
        saveFailedTests = false;
        agentId = null;
        lastScreenshot = null;
*/
    }
    /**
     * @return The base agent id of the SDK.
     */
    protected function getBaseAgentId(){//should be abstract
        return '';
    }

    /**
     * @return The full agent id composed of both the base agent id and the
     * user given agent id.
     */
    protected function getFullAgentId() {
        return "";
        /*String agentId = getAgentId();
        if (agentId == null) {
            return getBaseAgentId();
        }
        return String.format("%s [%s]", agentId, getBaseAgentId());*/
    }

    /**
     * @param isDisabled If true, all interactions with this API will be
     *                   silently ignored.
     */
    public function setIsDisabled($isDisabled) {
        $this->isDisabled = $isDisabled;
    }

    /**
     * @return Whether eyes is disabled.
     */
    public function getIsDisabled() {
        return $this->isDisabled;
    }

    /**
     * @return The currently set API key or {@code null} if no key is set.
     */
    public function getApiKey() {
        return $this->serverConnector->getApiKey();
    }

    /**
     * Sets the API key of your applitools Eyes account.
     *
     * @param apiKey The api key to set.
     */
    public function setApiKey($apiKey) {
        //ArgumentGuard.notNull(apiKey, "apiKey");
        $this->serverConnector->setApiKey($apiKey);
    }

    /**
     * @return Whether a session is open.
     */
    public function getIsOpen() {
        return $this->isOpen;
    }

    public function setIsOpen($isOpen) {
        return $this->isOpen = $isOpen;
    }

    /**
     * Clears the user inputs list.
     */
    protected function clearUserInputs() {
        if ($this->getIsDisabled()) {
            return;
        }
        //userInputs.clear();
    }


    /**
     * @return The viewport size of the AUT.
     */
    protected function getViewportSize(){ //should be abstract in this class
        return $this->viewportSize;
    }

    /**
     * @param size The required viewport size.
     */
    protected function setViewportSize(/*RectangleSize*/ $size){ //should be abstract in this class
        $this->viewportSize = $size;
    }

    /**
     *
     * @return The name of the application under test.
     */
    public function getAppName() {
        return $this->currentAppName != null ? $this->currentAppName : $this->appName;
    }


/**
     * Application environment is the environment (e.g., the host OS) which
     * runs the application under test.
     * @return The current application environment.
     */
    protected function getAppEnvironment() {

        //ATTENTION!!!!!!!!
        return '';   ///// temporary mock NEED TO SET ALL ENV
        $appEnv = new AppEnvironment();

            // If hostOS isn't set, we'll try and extract and OS ourselves.
        if ($this->hostOS != null) {
            $this->appEnv->setOs($hostOS);
        }

        if ($this->hostApp != null) {
            $appEnv->setHostingApp($hostApp);
        }

        $this->appEnv->setInferred($this->getInferredEnvironment());
        $this->appEnv->setDisplaySize($viewportSize);
        return $appEnv;
    }

    /**
     * If a test is running, aborts it. Otherwise, does nothing.
     */
    public function abortIfNotClosed() {
        try {
            if ($this->getIsDisabled()) {
                //logger.verbose("Ignored");
                return;
            }

            $this->setIsOpen(false);

            $this->lastScreenshot = null;   //!!!!!!!!!!
            $this->clearUserInputs();

            if (empty($this->runningSession)) { //!!!!!!!!!
                //logger.verbose("Closed");
                return;
            }

            //logger.verbose("Aborting server session...");
            try {
                // When aborting we do not save the test.
                serverConnector.stopSession($this->runningSession, true, false);
                //logger.log("--- Test aborted.");
            } catch (EyesException $ex) {
                //logger.log("Failed to abort server session: " + ex.getMessage());
            }
        } finally {
            $this->runningSession = null; /// !!!!!
            //logger.getLogHandler().close();
        }
    }


    public function openBase($appName, $testName, $viewportSize, $sessionType) {
        try {
            if ($this->getIsDisabled()) {
                Logger::log("Ignored");
                return;
            }

            // If there's no default application name, one must be provided
            // for the current test.
            if(empty($appName)){
                throw new Exception('$appName is not null');
            }

            if(empty($testName)){
                throw new Exception('$testName is not null');
            }
//$this->serverConnector->startSession("hohohh"); /// not from here. just test

            Logger::log("Agent = ". $this->getFullAgentId());
            Logger::log(sprintf("openBase('%s', '%s', '%s')", $appName, $testName, $viewportSize));

            if ($this->getApiKey() == null) {
                Logger::log(errMsg);
                throw new Exception("API key is missing! Please set it using setApiKey()");
            }

            Logger::log(sprintf("Eyes server URL is '%s'", $this->serverConnector->getServerUrl()));
            Logger::log(sprintf("Timeout = '%d'", $this->serverConnector->getTimeout()));
            Logger::log(sprintf("matchTimeout = '%d' ", $matchTimeout));
            Logger::log(sprintf("Default match settings = '%s' ", $defaultMatchSettings));
            Logger::log(sprintf("FailureReports = '%s' ", $failureReports));


            if ($this->getIsOpen()) {
                $this->abortIfNotClosed();
                $errMsg = "A test is already running";
                            //logger.log(errMsg);
                            throw new EyesException(errMsg);
                        }

            $this->currentAppName = ($appName != null) ? $appName : $this->appName;
            $this->testName = $testName;
            $this->viewportSize = $viewportSize;
            //$this->sessionType = ($sessionType != null) ? $sessionType : $SessionType::SEQUENTIAL; ///????????
            //scaleProviderHandler.set(new NullScaleProvider());
            //setScaleMethod(ScaleMethod.getDefault());
            $isOpen = true;

        } catch (Exception $e) {
                //logger.log(String.format("%s", e.getMessage()));
                //logger.getLogHandler().close();
            die($e->getMessage());
            throw $e;
        }
    }

    /**
     * Takes a snapshot of the application under test and matches it with the
     * expected output.
     *
     * @param regionProvider      Returns the region to check or the empty
     *                            rectangle to check the entire window.
     * @param tag                 An optional tag to be associated with the
     *                            snapshot.
     * @param ignoreMismatch      Whether to ignore this check if a mismatch is
     *                            found.
     * @param retryTimeout        The amount of time to retry matching in
     *                            milliseconds or a negative value to use the
     *                            default retry timeout.
     * @return The result of matching the output with the expected output.
     * @throws com.applitools.eyes.TestFailedException Thrown if a mismatch is
     *          detected and immediate failure reports are enabled.
     */
    public function checkWindowBase($regionProvider, $tag, $ignoreMismatch, $retryTimeout) {

        $result;

        if ($this->getIsDisabled()) {
            Logger::log("Ignored");
            //$result = new MatchResult();
            //$result->setAsExpected(true);
            return $result;
        }

        //ArgumentGuard.isValidState(getIsOpen(), "Eyes not open");
        //ArgumentGuard.notNull(regionProvider, "regionProvider");

        Logger::log(sprintf("CheckWindowBase(regionProvider, '%s', %b, %d)", $tag, $ignoreMismatch, $retryTimeout));

        if ($tag == null) {
            $tag = "";
        }

        if ($this->runningSession == null) {
            Logger::log("No running session, calling start session..");
            $this->startSession();
            Logger::log("Done!");

            /*$matchWindowTask = new MatchWindowTask(
                                    $logger,
                                    $serverConnector,
                                    $runningSession,
                                    $matchTimeout,
                                    // A callback which will call getAppOutput
                                    new AppOutputProvider() {
                                        getAppOutput(RegionProvider $regionProvider_, EyesScreenshot lastScreenshot_) {
                                            return getAppOutputWithScreenshot($regionProvider_, $lastScreenshot_);
                                        }
                                    }
            );*/
        }

        Logger::log("Calling match window...");
        $result = $matchWindowTask->matchWindow(getUserInputs(), $lastScreenshot, $regionProvider, $tag,
                $shouldMatchWindowRunOnceOnTimeout, $ignoreMismatch, $retryTimeout);
        Logger::log("MatchWindow Done!");

        if (!$result->getAsExpected()) {
            if (!ignoreMismatch) {
                $this->clearUserInputs();
                $lastScreenshot = $result->getScreenshot();
            }

            $shouldMatchWindowRunOnceOnTimeout = true;

            if (!$runningSession.getIsNewSession()) {
                Logger::log(sprintf("Mismatch! (%s)", $tag));
            }

            if ($this->getFailureReports() == FailureReports.IMMEDIATE) {
                throw new TestFailedException(sprintf("Mismatch found in '%s' of '%s'",
                        $sessionStartInfo->getScenarioIdOrName(), $sessionStartInfo->getAppIdOrName()));
            }
        } else { // Match successful
            clearUserInputs();
            $lastScreenshot = $result->getScreenshot();
        }

        Logger::log("Done!");
        return $result;
    }



    /**
     * Start eyes session on the eyes server.
     */
    protected function startSession() {
        Logger::log("startSession()");

        if ($this->viewportSize == null) {
            $this->viewportSize = $this->getViewportSize();    //need to check is it correct idea?
        } else {
            $this->setViewportSize($this->viewportSize);  //need to check is it correct idea?
        }

        if ($this->batch == null) {
            Logger::log("No batch set");
            $testBatch = ''/*new BatchInfo(null)*/;
        } else {
            Logger::log("Batch is " . $batch);
            $testBatch = $batch;
        }

        //$appEnv = $this->getAppEnvironment();   need to check is it correct?
        Logger::log("Application environment is " . $this->getAppEnvironment());

        $sessionStartInfo = new SessionStartInfo($this->getBaseAgentId(), $this->sessionType,
            $this->getAppName(), null, $this->testName, $testBatch, $this->baselineName, $this->getAppEnvironment(),
            $this->defaultMatchSettings, $this->branchName, $this->parentBranchName);

        Logger::log("Starting server session...");
        $runningSession = $this->serverConnector->startSession($sessionStartInfo);

        Logger::log("Server session ID is ". $runningSession->getId());

        $testInfo = "'" . $testName . "' of '" . $this->getAppName() . "' " . $appEnv;
        if ($runningSession->getIsNewSession()) {
            Logger::log("--- New test started - " . $testInfo);
            $shouldMatchWindowRunOnceOnTimeout = true;
        } else {
            Logger::log("--- Test started - " . $testInfo);
            $shouldMatchWindowRunOnceOnTimeout = false;
        }
    }

}

?>
