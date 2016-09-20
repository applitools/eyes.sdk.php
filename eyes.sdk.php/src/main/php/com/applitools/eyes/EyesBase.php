<?php
/*require "../../eyes/eyes.php/eyes.common.php/src/main/php/com/applitools/eyes/ServerConnectorFactory.php";
require "../../eyes/eyes.php/eyes.common.php/src/main/php/com/applitools/eyes/RectangleSize.php";
require "../../eyes/eyes.php/eyes.common.php/src/main/php/com/applitools/eyes/Logger.php";
require "../../eyes/eyes.php/eyes.common.php/src/main/php/com/applitools/eyes/SessionStartInfo.php";
require "../../eyes/eyes.php/eyes.common.php/src/main/php/com/applitools/eyes/AppEnvironment.php";
require "../../eyes/eyes.php/eyes.common.php/src/main/php/com/applitools/eyes/BatchInfo.php";
require "../../eyes/eyes.php/eyes.common.php/src/main/php/com/applitools/utils/ArgumentGuard.php";
require "../../eyes/eyes.php/eyes.common.php/src/main/php/com/applitools/utils/ImageUtils.php";
require "../../eyes/eyes.php/eyes.common.php/src/main/php/com/applitools/utils/ImageDeltaCompressor.php";
require "../../eyes/eyes.php/eyes.common.php/src/main/php/com/applitools/eyes/EyesScreenshot.php";
require "../../eyes/eyes.php/eyes.common.php/src/main/php/com/applitools/eyes/Location.php";
require "../../eyes/eyes.php/eyes.common.php/src/main/php/com/applitools/eyes/BufferedImage.php";
require "../../eyes/eyes.php/eyes.common.php/src/main/php/com/applitools/eyes/EyesException.php";
require "../../eyes/eyes.php/eyes.common.php/src/main/php/com/applitools/eyes/AppOutput.php";
require "../../eyes/eyes.php/eyes.common.php/src/main/php/com/applitools/eyes/MatchResult.php";
require "../../eyes/eyes.php/eyes.common.php/src/main/php/com/applitools/eyes/TestResults.php";
require "../../eyes/eyes.php/eyes.images.php/src/main/php/com/applitools/eyes/EyesImagesScreenshot.php";
require "../../eyes/eyes.php/eyes.sdk.php/src/main/php/com/applitools/eyes/AppOutputProvider.php";
require "../../eyes/eyes.php/eyes.sdk.php/src/main/php/com/applitools/eyes/AppOutputProviderRedeclared.php";
require "../../eyes/eyes.php/eyes.sdk.php/src/main/php/com/applitools/eyes/MatchWindowTask.php";
require "../../eyes/eyes.php/eyes.sdk.php/src/main/php/com/applitools/eyes/InvalidPositionProvider.php";
require "../../eyes/eyes.php/eyes.sdk.php/src/main/php/com/applitools/eyes/ImageProvider.php";
require "../../eyes/eyes.php/eyes.sdk.php/src/main/php/com/applitools/eyes/EyesScreenshotFactory.php";
require "../../eyes/eyes.php/eyes.common.php/src/main/php/com/applitools/eyes/Region.php";
require "../../eyes/eyes.php/eyes.common.php/src/main/php/com/applitools/utils/SimplePropertyHandler.php";
require "../../eyes/eyes.php/eyes.common.php/src/main/php/com/applitools/eyes/SessionType.php";
require "../../eyes/eyes.php/eyes.common.php/src/main/php/com/applitools/utils/GeneralUtils.php";
require "../../eyes/eyes.php/eyes.sdk.php/src/main/php/com/applitools/eyes/NullScaleProvider.php";
require "../../eyes/eyes.php/eyes.common.php/src/main/php/com/applitools/eyes/ImageMatchSettings.php";
require "../../eyes/eyes.php/eyes.sdk.php/src/main/php/com/applitools/eyes/FailureReports.php";
require "../../eyes/eyes.php/eyes.sdk.php/src/main/php/com/applitools/eyes/NullCutProvider.php";
require "../../eyes/eyes.php/eyes.sdk.php/src/main/php/com/applitools/eyes/TestFailedException.php";*/

//require "../../eyes/eyes.php/eyes.common.php/src/main/php/com/applitools/eyes/Iterator.php";
class EyesBase
{

    const SEQUENTIAL = "aaa";  ///Session type FIXME
    const DEFAULT_MATCH_TIMEOUT = 5; // Seconds
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
    protected $scaleProviderHandler; //PropertyHandler<ScaleProvider>
    protected $cutProviderHandler; //PropertyHandler<CutProvider>


    public function __construct($serverUrl)
    {

        if ($this->getIsDisabled()) {
            $this->userInputs = null;
            return;
        }

        ArgumentGuard::notNull($serverUrl, "serverUrl");

        $this->logger = new Logger();

        $this->scaleProviderHandler = new SimplePropertyHandler();
        $scaleProvider = new NullScaleProvider();
        $this->scaleProviderHandler->set($scaleProvider);
        $this->cutProviderHandler = new SimplePropertyHandler();
        $cutProvider = new NullCutProvider();
        $this->cutProviderHandler->set($cutProvider);
        $this->positionProvider = new InvalidPositionProvider();
        $this->scaleMethod = ScaleMethod::getDefault();
        $this->viewportSize = null;

        $this->serverConnector = ServerConnectorFactory::create($this->logger, $this->getBaseAgentId(), $serverUrl);
        $this->matchTimeout = self::DEFAULT_MATCH_TIMEOUT;
        $this->runningSession = null;
        $this->defaultMatchSettings = new ImageMatchSettings();
        $this->failureReports = FailureReports::ON_CLOSE;
        $this->userInputs = array(); //new ArrayDeque<>();

        // New tests are automatically saved by default.
        $this->saveNewTests = true;
        $this->saveFailedTests = false;
        $this->agentId = null;

    }


    /**
     * @param hostOS The host OS running the AUT.
     */
    public function setHostOS($hostOS)
    {

        $this->logger->log("Host OS: " . $hostOS);

        if (empty($hostOS)) {
            $this->hostOS = null;
        } else {
            $this->hostOS = $hostOS->trim();
        }
    }

    /**
     * @return get the host OS running the AUT.
     */
    public function getHostOS()
    {
        return $this->hostOS;
    }


    /**
     * @return The base agent id of the SDK.
     */
    protected function getBaseAgentId()
    {//should be abstract FIXME
        return "mysdk/1.3";
    }

    /**
     * @return The full agent id composed of both the base agent id and the
     * user given agent id.
     */
    protected function getFullAgentId()
    {
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
    public function setIsDisabled($isDisabled)
    {
        $this->isDisabled = $isDisabled;
    }

    /**
     * @return Whether eyes is disabled.
     */
    public function getIsDisabled()
    {
        return $this->isDisabled;
    }

    /**
     * @return The currently set API key or {@code null} if no key is set.
     */
    public function getApiKey()
    {
        return $this->serverConnector->getApiKey();
    }

    /**
     * Sets the API key of your applitools Eyes account.
     *
     * @param apiKey The api key to set.
     */
    public function setApiKey($apiKey)
    {
        ArgumentGuard::notNull($apiKey, "apiKey");
        $this->serverConnector->setApiKey($apiKey);
    }

    /**
     * @return Whether a session is open.
     */
    public function getIsOpen()
    {
        return $this->isOpen;
    }

    public function setIsOpen($isOpen)
    {
        return $this->isOpen = $isOpen;
    }

    /**
     * Clears the user inputs list.
     */
    protected function clearUserInputs()
    {
        if ($this->getIsDisabled()) {
            return;
        }
        //userInputs.clear();
    }


    /**
     * @return The viewport size of the AUT.
     */
    protected function getViewportSize()
    { //should be abstract in this class
        return $this->viewportSize;
    }

    /**
     * @param size The required viewport size.
     */
    protected function setViewportSize(WebDriver $driver = null, RectangleSize $size)
    { //should be abstract in this class
        $this->viewportSize = $size;
    }

    /**
     *
     * @return The name of the application under test.
     */
    public function getAppName()
    {
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
    protected function getInferredEnvironment()
    {
        return "";
    }  /// Should be abstract


    /**
     * Application environment is the environment (e.g., the host OS) which
     * runs the application under test.
     * @return The current application environment.
     */
    protected function getAppEnvironment()
    {
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
     * Sets the current server URL used by the rest client.
     * @param serverUrl The URI of the rest server, or {@code null} to use
     *                  the default server.
     */

    public function setServerUrl($serverUrl)
    {
        if ($serverUrl == null) {
            $this->serverConnector->setServerUrl($this->getDefaultServerUrl());
        } else {
            $this->serverConnector->setServerUrl($serverUrl);
        }
    }

    public static function getDefaultServerUrl()
    {
        return "https://eyessdk.applitools.com";
    }


    /** Superseded by {@link #setHostOS(String)} and {@link #setHostApp
     * (String)}.
     * Sets the OS (e.g., Windows) and application (e.g., Chrome) that host the
     * application under test.
     *
     * @param hostOS  The name of the OS hosting the application under test or
     *                {@code null} to auto-detect.
     * @param hostApp The name of the application hosting the application under
     *                test or {@code null} to auto-detect.
     */
    public function setAppEnvironment($hostOS, $hostApp)
    {
        if ($this->isDisabled) {
            $this->logger->verbose("Ignored");
            return;
        }

        $this->logger->log("Warning: SetAppEnvironment is deprecated! Please use " +
            "'setHostOS' and 'setHostApp'");

        $this->logger->verbose("setAppEnvironment(" . $hostOS . ", " . $hostApp . ")");
        $this->setHostOS($hostOS);
        $this->setHostApp($hostApp);
    }

    /**
     * If a test is running, aborts it. Otherwise, does nothing.
     */
    public function abortIfNotClosed()
    {
        try {
            if ($this->getIsDisabled()) {
                //logger.verbose("Ignored");
                return;
            }

            $this->setIsOpen(false);

            $this->lastScreenshot = null;   //FIXME
            $this->clearUserInputs();

            if (empty($this->runningSession)) { //FIXME
                $this->logger->verbose("Closed");
                return;
            }

            $this->logger->verbose("Aborting server session...");
            try {
                // When aborting we do not save the test.
                $this->serverConnector->stopSession($this->runningSession, true, false);
                $this->logger->log("--- Test aborted.");
            } catch (EyesException $ex) {
                $this->logger->log("Failed to abort server session: " . $ex->getMessage());
            }
        } finally {
            $this->runningSession = null; /// FIXME
            $this->logger->getLogHandler()->close();
        }
    }


    public function openBase($appName, $testName, RectangleSize $viewportSize, SessionType $sessionType = null)
    {

        $this->logger->getLogHandler()->open();

        try {
            if ($this->isDisabled) {
                $this->logger->verbose("Ignored");
                return;
            }

            // If there's no default application name, one must be provided
            // for the current test.
            if ($appName == null) {
                ArgumentGuard::notNull($this->appName, "appName");
            }

            ArgumentGuard::notNull($testName, "testName");

            $this->logger->log("Agent = " . $this->getFullAgentId());
            $this->logger->verbose(sprintf("openBase('%s', '%s', '%s')", $appName, $testName, json_encode($viewportSize)));

            if ($this->getApiKey() == null) {
                $errMsg = "API key is missing! Please set it using setApiKey()";
                $this->logger->log($errMsg);
                throw new EyesException($errMsg);
            }

            $this->logger->log(sprintf("Eyes server URL is '%s'", $this->serverConnector->getServerUrl()));
            $this->logger->verbose(sprintf("Timeout = '%d'", $this->serverConnector->getTimeout()));
            $this->logger->log(sprintf("matchTimeout = '%d' ", $this->matchTimeout));
            $this->logger->log(sprintf("Default match settings = '%s' ", json_encode($this->defaultMatchSettings)));
            $this->logger->log(sprintf("FailureReports = '%s' ", $this->failureReports));


            if ($this->isOpen) {
                $this->abortIfNotClosed();
                $errMsg = "A test is already running";
                $this->logger->log($errMsg);
                throw new EyesException($errMsg);
            }

            $this->currentAppName = $appName != null ? $appName : $this->appName;
            $this->testName = $testName;
            $this->viewportSize = $viewportSize;
            $this->sessionType = $sessionType != null ? $sessionType : SessionType::SEQUENTIAL;
            $scaleProvider = new NullScaleProvider();
            $this->scaleProviderHandler->set($scaleProvider);
            $this->setScaleMethod(ScaleMethod::getDefault());
            $this->isOpen = true;
        } catch (EyesException $e) {
            $this->logger->log(sprintf("%s", $e->getMessage()));
            $this->logger->getLogHandler()->close();
            throw $e;
        }
    }


    /**
     * @param positionProvider The position provider to be used.
     */
    protected function setPositionProvider(PositionProvider $positionProvider)
    {
        $this->positionProvider = $positionProvider;
    }


    /**
     *
     * @param method The method used to perform scaling.
     */
    protected function setScaleMethod($method)
    {
        ArgumentGuard::notNull($method, "method");
        $this->scaleMethod = $method;
    }

    protected function getScaleMethod()
    {
        return $this->scaleMethod;
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
    public function checkWindowBase($regionProvider, $tag = "", $ignoreMismatch = null, $retryTimeout = null)
    {
        if ($this->getIsDisabled()) {
            $this->logger->log("Ignored");
            $result = new MatchResult();
            $result->setAsExpected(true);
            return $result;
        }
        //FIXME
        //require '../../eyes/eyes.php/eyes.selenium.php/src/main/php/com/applitools/eyes/selenium/EyesWebDriverScreenshot.php'; //FIXME
        $this->lastScreenshot = new EyesWebDriverScreenshot($this->logger, $this->driver,
            Gregwar\Image\Image::create(0, 0)); //FIXME


        ArgumentGuard::isValidState($this->getIsOpen(), "Eyes not open");
        ArgumentGuard::notNull($regionProvider, "regionProvider");

        $this->logger->log(sprintf("CheckWindowBase(regionProvider, '%s', %b, %d)", $tag, $ignoreMismatch, $retryTimeout));

        if ($this->runningSession == null) {
            $this->logger->log("No running session, calling start session..");
            $this->startSession();
            $this->logger->log("Done!");

            $appOutputProviderRedeclared = new AppOutputProviderRedeclared($this);

            $matchWindowTask = new MatchWindowTask(
                $this->logger,
                $this->serverConnector,
                $this->runningSession,
                $this->matchTimeout,
                // A callback which will call getAppOutput
                $appOutputProviderRedeclared //FIXME
            );
        }
        $this->logger->log("Calling match window...");
        $result = $matchWindowTask->matchWindow($this->getUserInputs(), $this->lastScreenshot, $regionProvider,
            $tag, $this->shouldMatchWindowRunOnceOnTimeout, $ignoreMismatch, $retryTimeout);
        $this->logger->log("MatchWindow Done!");
        //FIXME result is empty. But screenshot was added
        /*  if (!$result->getAsExpected()) {
            if (!$ignoreMismatch) {
                $this->clearUserInputs();
                $this->lastScreenshot = $result->getScreenshot();
            }

            $shouldMatchWindowRunOnceOnTimeout = true;

            if (!$this->runningSession->getIsNewSession()) {
                Logger::log(sprintf("Mismatch! (%s)", $tag));
            }

            if ($this->getFailureReports() == "FailureReports::IMMEDIATE") {
                throw new TestFailedException(sprintf("Mismatch found in '%s' of '%s'",
                    $this->sessionStartInfo->getScenarioIdOrName(), $this->sessionStartInfo->getAppIdOrName()));
            }
        } else { // Match successful
            clearUserInputs();
            $this->lastScreenshot = $result->getScreenshot();
        }

        Logger::log("Done!");
        return $result;*/
    }


    /**
     * @param regionProvider      A callback for getting the region of the
     *                            screenshot which will be set in the
     *                            application output.
     * @param lastScreenshot      Previous application screenshot (used for
     *                            compression) or {@code null} if not available.
     * @return The updated app output and screenshot.
     */
    private function getAppOutputWithScreenshot(RegionProvider $regionProvider, EyesScreenshot $lastScreenshot)
    {

        $this->logger->verbose("getting screenshot...");
        // Getting the screenshot (abstract function implemented by each SDK).
        $screenshot = $this->getScreenshot();
        $this->logger->verbose("Done getting screenshot!");

        // Cropping by region if necessary
        $region = $this->regionProvider->getRegion();
        if (!$region->isEmpty()) {
            $screenshot = $screenshot->getSubScreenshot($region,
                $regionProvider->getCoordinatesType(), false);
        }

        $this->logger->verbose("Compressing screenshot...");
        $compressResult = $this->compressScreenshot64($screenshot, $lastScreenshot);
        $this->logger->verbose("Done! Getting title...");
        $title = $this->getTitle();
        $this->logger->verbose("Done!");

        $result = new AppOutputWithScreenshot(new AppOutput($title, $compressResult), $screenshot);
        $this->logger->verbose("Done!");
        return $result;
    }

    /**
     * Compresses a given screenshot.
     *
     * @param screenshot     The screenshot to compress.
     * @param lastScreenshot The previous screenshot, or null.
     * @return A base64 encoded compressed screenshot.
     */
    public function compressScreenshot64(EyesScreenshot $screenshot,
                                         EyesScreenshot $lastScreenshot)
    {

        ArgumentGuard::notNull($screenshot, "screenshot");

        $screenshotImage = $screenshot->getImage();
        $uncompressed = ImageUtils::encodeAsPng($screenshotImage);

        $source = ($lastScreenshot != null) ?
            $lastScreenshot->getImage() : null;

        // Compressing the screenshot
        try {
            $compressedScreenshot = 'sobe byte string';/* FIXME ImageDeltaCompressor::compressByRawBlocks(
            $screenshotImage, $uncompressed, $source);*/
        } catch (IOException $e) {
            throw new EyesException("Failed to compress screenshot!", $e);
        }

        return base64_encode($compressedScreenshot); //FIXME just need to check
    }

    /**
     * Sets the batch in which context future tests will run or {@code null}
     * if tests are to run standalone.
     *
     * @param batch The batch info to set.
     */
    public function setBatch(BatchInfo $batch)
    {
        if ($this->isDisabled) {
            $this->logger->verbose("Ignored");
            return;
        }

        $this->logger->verbose("setBatch(" . json_encode($batch) . ")");

        $this->batch = $batch;
    }

    /**
     * @return User inputs collected between {@code checkWindowBase}
     * invocations.
     */
    protected function getUserInputs()
    {
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
    protected function startSession()
    {
        $this->logger->log("startSession()");
        if ($this->viewportSize == null) {
            $this->viewportSize = $this->getViewportSize();

        } else {
            $this->setViewportSize(null, $this->viewportSize); //FIXME
        }

        if ($this->batch == null) {
            $this->logger->log("No batch set");
            $testBatch = new BatchInfo(null);
        } else {
            $this->logger->log("Batch is " . $this->batch);
            $testBatch = $this->batch;
        }

        $appEnv = $this->getAppEnvironment(); ///////  need to check is it correct?  //FIXME
        $this->logger->log("Application environment is " . serialize($this->getAppEnvironment()));

        $this->sessionStartInfo = new SessionStartInfo($this->getBaseAgentId(), $this->sessionType,
            $this->getAppName(), null, $this->testName, $testBatch, $this->baselineName, $appEnv,
            $this->defaultMatchSettings, $this->branchName, $this->parentBranchName);

        $this->logger->log("Starting server session...");
        $this->runningSession = $this->serverConnector->startSession($this->sessionStartInfo);
        $this->logger->log("Server session ID is " . $this->runningSession->getId());

        $testInfo = "'" . $this->testName . "' of '" . $this->getAppName() . "' " . serialize($appEnv);
        if ($this->runningSession->getIsNewSession()) {
            $this->logger->log("--- New test started - " . $testInfo);
            $shouldMatchWindowRunOnceOnTimeout = true;
        } else {
            $this->logger->log("--- Test started - " . $testInfo);
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
    public function close($throwEx = true)
    {
        try {
            if ($this->isDisabled) {
                $this->logger->verbose("Ignored");
                return null;
            }
            $this->logger->verbose(sprintf("close(%b)", $throwEx));
            ArgumentGuard::isValidState($this->isOpen, "Eyes not open");

            $this->isOpen = false;

            $lastScreenshot = null;
            //$this->clearUserInputs();

            if ($this->runningSession == null) {
                $this->logger->verbose("Server session was not started");
                $this->logger->verbose("--- Empty test ended.");
                return new TestResults();
            }
            $isNewSession = $this->runningSession->getIsNewSession();
            $sessionResultsUrl = $this->runningSession->getUrl();

            $this->logger->verbose("Ending server session...");
            $save = ($isNewSession && $this->saveNewTests) || (!$isNewSession && $this->saveFailedTests);
            $this->logger->verbose("Automatically save test? " . $save);
            $results = $this->serverConnector->stopSession($this->runningSession, false, $save);

            $results->setNew($isNewSession);
            $results->setUrl($sessionResultsUrl);
            $this->logger->verbose($results->toString());

            if (!$isNewSession && (0 < $results->getMismatches() || 0 < $results->getMissing())) {

                Logger::log("--- Failed test ended. See details at " . $sessionResultsUrl);

                if ($throwEx) {
                    $message = "'" . $this->sessionStartInfo->getScenarioIdOrName()
                        . "' of '" . $this->sessionStartInfo->getAppIdOrName() . "'. See details at " . $sessionResultsUrl;
                    throw new /*FIXME TestFailed*/Exception(/*$results, */$message/*, $throwEx*/);
                }
                return $results;
            }

            if ($isNewSession) {
                $instructions = "Please approve the new baseline at " . $sessionResultsUrl;
                $this->logger->verbose("--- New test ended. " . $instructions);
                if ($throwEx) {
                    $message = "'" . $this->sessionStartInfo->getScenarioIdOrName()
                        . "' of '" . $this->sessionStartInfo->getAppIdOrName()
                        . "'. " . $instructions;
                    throw new NewTestException($results, $message);
                }
                return $results;
            }
            // Test passed
            $this->logger->verbose("--- Test passed. See details at " . $sessionResultsUrl);
            return $results;
        } finally {
            // Making sure that we reset the running session even if an
            // exception was thrown during close.
            $this->runningSession = null;
            $this->currentAppName = null;
            $this->logger->getLogHandler()->close();
        }
    }
}
