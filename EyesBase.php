<?php
require "ServerConnectorFactory.php";
require "Logger.php";
require "SessionStartInfo.php";
require "AppEnvironment.php";
require "BatchInfo.php";
require "AppOutputProvider.php";
require "MatchWindowTask.php";
require "EyesImagesScreenshot.php";
require "Region.php";
require "StitchMode.php";

class EyesBase {

    const SEQUENTIAL = "aaa";  ///Session type ???
    const DEFAULT_MATCH_TIMEOUT = 2; // Seconds
    const USE_DEFAULT_TIMEOUT = -1;

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
    private $failureReports;
    private $hostApp;
    private $hostOS;
    private $userInputs = array(); //new ArrayDeque<Trigger>();
    private $shouldMatchWindowRunOnceOnTimeout;
    private $lastScreenshot;


    public function __construct($serverUrl)
    {

        if ($this->getIsDisabled()) {
            $this->userInputs = null;
            return;
        }

        //ArgumentGuard.notNull(serverUrl, "serverUrl");

        $this->logger = new Logger();
        /*      scaleProviderHandler = new SimplePropertyHandler<ScaleProvider>();
              scaleProviderHandler.set(new NullScaleProvider());
              positionProvider = new InvalidPositionProvider();
              scaleMethod = ScaleMethod.getDefault();
              */
        $this->viewportSize = null;

        $logger = "";
        $this->serverConnector = ServerConnectorFactory::create($logger, $this->getBaseAgentId(), $serverUrl);
        $this->matchTimeout = self::DEFAULT_MATCH_TIMEOUT;
   /*     runningSession = null;
        defaultMatchSettings = new ImageMatchSettings();
        failureReports = FailureReports.ON_CLOSE;
        userInputs = new ArrayDeque<Trigger>();
*/
        // New tests are automatically saved by default.
        $this->saveNewTests = true;
        $this->saveFailedTests = false;
        $this->agentId = null;
        $this->lastScreenshot = new EyesImagesScreenshot();

    }
    /**
     * @return The base agent id of the SDK.
     */
    protected function getBaseAgentId(){//should be abstract
        return "mysdk/1.3";
    }

    /**
     * @return The full agent id composed of both the base agent id and the
     * user given agent id.
     */
    protected function getFullAgentId() {
        return $this->getBaseAgentId();
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
    protected function setViewportSize(WebDriver $driver = null, RectangleSize $size){ //should be abstract in this class
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
     * @return The inferred environment string
     * or {@code null} if none is available. The inferred string is in the
     * format "source:info" where source is either "useragent" or "pos".
     * Information associated with a "useragent" source is a valid browser user
     * agent string. Information associated with a "pos" source is a string of
     * the format "process-name;os-name" where "process-name" is the name of the
     * main module of the executed process and "os-name" is the OS name.
     */
    protected function getInferredEnvironment(){return "";}  /// Should be abstract


    /**
     * Application environment is the environment (e.g., the host OS) which
     * runs the application under test.
     * @return The current application environment.
     */
    protected function getAppEnvironment() {

        //ATTENTION!!!!!!!!
        //return '';   ///// temporary mock NEED TO SET ALL ENV
        $appEnv = new AppEnvironment();

            // If hostOS isn't set, we'll try and extract and OS ourselves.
        if ($this->hostOS != null) {
            $appEnv->setOs($this->hostOS);
        }

        if ($this->hostApp != null) {
            $appEnv->setHostingApp($this->hostApp);
        }

        $appEnv->setInferred($this->getInferredEnvironment());
        $appEnv->setDisplaySize($this->viewportSize);
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

            Logger::log("Agent = ". $this->getFullAgentId());
            Logger::log(sprintf("openBase('%s', '%s', '%s')", $appName, $testName, json_encode($viewportSize)));

            if ($this->getApiKey() == null) {
                Logger::log(errMsg);
                throw new Exception("API key is missing! Please set it using setApiKey()");
            }

            Logger::log(sprintf("Eyes server URL is '%s'", $this->serverConnector->getServerUrl()));
            Logger::log(sprintf("Timeout = '%d'", $this->serverConnector->getTimeout()));
            Logger::log(sprintf("matchTimeout = '%d' ", $this->matchTimeout));
            Logger::log(sprintf("Default match settings = '%s' ", $this->defaultMatchSettings));
            Logger::log(sprintf("FailureReports = '%s' ", $this->failureReports));


            if ($this->getIsOpen()) {
                $this->abortIfNotClosed();
                $errMsg = "A test is already running";
                Logger::log($errMsg);
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
                Logger::log(String.format("%s", e.getMessage()));
                //Logger::getLogHandler().close();
            die($e->getMessage()); // temporary
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
    public function checkWindowBase($regionProvider, $tag = null, $ignoreMismatch = null, $retryTimeout = null) {
        if ($this->getIsDisabled()) {
            Logger::log("Ignored");
            $result = new MatchResult();
            $result->setAsExpected(true);
            return $result;
        }

        //ArgumentGuard::isValidState($this->getIsOpen(), "Eyes not open");
        ArgumentGuard::notNull($regionProvider, "regionProvider");

        Logger::log(sprintf("CheckWindowBase(regionProvider, '%s', %b, %d)", $tag, $ignoreMismatch, $retryTimeout));

        if ($tag == null) {
            $tag = "";
        }

        if ($this->runningSession == null) {
            Logger::log("No running session, calling start session..");
            $this->startSession();
            Logger::log("Done!");

            $appOutputProvider = new AppOutputProvider();

            $matchWindowTask = new MatchWindowTask(
                                    //$logger,
                                    $this->serverConnector,
                                    $this->runningSession,
                                    $this->matchTimeout,
                                    // A callback which will call getAppOutput
                                    $appOutputProvider
            );
        }

        Logger::log("Calling match window...");
        $result = $matchWindowTask->matchWindow($this->getUserInputs(), $this->lastScreenshot, $regionProvider, $tag,
                $this->shouldMatchWindowRunOnceOnTimeout, $ignoreMismatch, $retryTimeout);
        Logger::log("MatchWindow Done!");

        if (!$result->getAsExpected()) {
            if (!$ignoreMismatch) {
                $this->clearUserInputs();
                $this->lastScreenshot = $result->getScreenshot();
            }

            $shouldMatchWindowRunOnceOnTimeout = true;

            if (!$this->runningSession.getIsNewSession()) {
                Logger::log(sprintf("Mismatch! (%s)", $tag));
            }

            if ($this->getFailureReports() == "FailureReports::IMMEDIATE") {
                throw new TestFailedException(sprintf("Mismatch found in '%s' of '%s'",
                        $sessionStartInfo->getScenarioIdOrName(), $sessionStartInfo->getAppIdOrName()));
            }
        } else { // Match successful
            clearUserInputs();
            $this->lastScreenshot = $result->getScreenshot();
        }

        Logger::log("Done!");
        return $result;
    }


    /**
     * @return User inputs collected between {@code checkWindowBase}
     * invocations.
     */
    protected function getUserInputs() {
        if ($this->isDisabled) {
            return null;
        }
        /*result = new Trigger[userInputs.size()];
                return userInputs.toArray(result);*/
        return $this->userInputs;
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
            $testBatch = new BatchInfo(null);
        } else {
            Logger::log("Batch is " . $this->batch);
            $testBatch = $this->batch;
        }

        $appEnv = $this->getAppEnvironment(); ///////  need to check is it correct?
        Logger::log("Application environment is " . serialize($this->getAppEnvironment()));

        $sessionStartInfo = new SessionStartInfo($this->getBaseAgentId(), $this->sessionType,
            $this->getAppName(), null, $this->testName, $testBatch, $this->baselineName, $appEnv,
            $this->defaultMatchSettings, $this->branchName, $this->parentBranchName);
//echo "ddddddddd"; print_r($sessionStartInfo); die();
        Logger::log("Starting server session...");
        $this->runningSession = $this->serverConnector->startSession($sessionStartInfo);
//print_r($this->runningSession); die();
        Logger::log("Server session ID is ". $this->runningSession->getId());

        $testInfo = "'" . $this->testName . "' of '" . $this->getAppName() . "' " . serialize($appEnv);
        if ($this->runningSession->getIsNewSession()) {
            Logger::log("--- New test started - " . $testInfo);
            $shouldMatchWindowRunOnceOnTimeout = true;
        } else {
            Logger::log("--- Test started - " . $testInfo);
            $shouldMatchWindowRunOnceOnTimeout = false;
        }
    }



    /**
     * Ends the test.
     *
     * @param throwEx If true, an exception will be thrown for failed/new tests.
     * @return The test results.
     * @throws TestFailedException if a mismatch was found and throwEx is true.
     * @throws NewTestException    if this is a new test was found and throwEx
     *                             is true.
     */
    public function close($throwEx) {
        try {
            if ($this->isDisabled) {
                logger::log("Ignored");
                return null;
            }
            Logger::verbose(sprintf("close(%b)", $throwEx));
            ArgumentGuard::isValidState($this->isOpen, "Eyes not open");

            $this->isOpen = false;

            $lastScreenshot = null;
            //$this->clearUserInputs();

            if ($this->runningSession == null) {
                Logger::log("Server session was not started");
                Logger::log("--- Empty test ended.");
                return new TestResults();
            }

            $isNewSession = $this->runningSession->getIsNewSession();
            $sessionResultsUrl = $this->runningSession->getUrl();

            Logger::log("Ending server session...");
            $save = ($isNewSession && $this->saveNewTests) || (!$isNewSession && $this->saveFailedTests);
            Logger::verbose("Automatically save test? " + $save);
            $results = $this->serverConnector->stopSession($this->runningSession, false, $save);

            $results->setNew($isNewSession);
            $results->setUrl($sessionResultsUrl);
            Logger::verbose($results->toString());

            if (!$isNewSession && (0 < $results->getMismatches() || 0 < $results->getMissing())) {

                Logger::log("--- Failed test ended. See details at " . sessionResultsUrl);

                if ($throwEx) {
                    $message = "'" . $this->sessionStartInfo->getScenarioIdOrName()
                          . "' of '" . $this->sessionStartInfo->getAppIdOrName() . "'. See details at " . $sessionResultsUrl;
                    throw new TestFailedException($results, $message);
                }
                return $results;
            }

            if ($isNewSession) {
                $instructions = "Please approve the new baseline at " . $sessionResultsUrl;
                Logger::log("--- New test ended. " . $instructions);
                if (throwEx) {
                    $message = "'" . sessionStartInfo.getScenarioIdOrName()
                                . "' of '" . $this->sessionStartInfo->getAppIdOrName()
                                . "'. " . $instructions;
                            throw new NewTestException($results, $message);
                }
                return $results;
            }
            // Test passed
            Logger::log("--- Test passed. See details at " . $sessionResultsUrl);
            return $results;
        } finally {
            // Making sure that we reset the running session even if an
            // exception was thrown during close.
            $this->runningSession = null;
            $this->currentAppName = null;
            Logger::getLogHandler()->close();
        }
    }
}

?>
