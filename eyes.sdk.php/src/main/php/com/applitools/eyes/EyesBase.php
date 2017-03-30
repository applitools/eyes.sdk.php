<?php
abstract class EyesBase
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
            $this->userInputs = array();
            return;
        }

        ArgumentGuard::notNull($serverUrl, "serverUrl");

        $this->logger = new Logger(new PrintLogHandler());

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
     * @return The application name running the AUT.
     */
    public function getHostApp() {
        return $this->hostApp;
    }

    /**
     * @param hostApp The application running the AUT (e.g., Chrome).
     */
    public function setHostApp($hostApp) {
        $this->logger->log("Host App: " . $hostApp);

        if ($hostApp == null || $hostApp->isEmpty()) {
            $this->hostApp = null;
        } else {
            $this->hostApp = $hostApp;
        }
    }

    /**
     * Sets the branch in which the baseline for subsequent test runs resides.
     * If the branch does not already exist it will be created under the
     * specified parent branch (see {@link #setParentBranchName}).
     * Changes to the baseline or model of a branch do not propagate to other
     * branches.
     *
     * @param branchName Branch name or {@code null} to specify the default
     *                   branch.
     */
    public function setBranchName($branchName) {
        $this->branchName = $branchName;
    }

    /**
     *
     * @return The current branch (see {@link #setBranchName(String)}).
     */
    public function getBranchName() {
        return $this->branchName;
    }

    /**
     * Sets the branch under which new branches are created. (see {@link
     * #setBranchName(String)}.
     *
     * @param branchName Branch name or {@code null} to specify the default
     *                   branch.
     */
    public function setParentBranchName($branchName) {
        $this->parentBranchName = $branchName;
    }

    /**
     *
     * @return The name of the current parent branch under which new branches
     * will be created. (see {@link #setParentBranchName(String)}).
     */
    public function getParentBranchName() {
        return $this->parentBranchName;
    }

    /**
     * @return The currently set position provider.
     */
    protected function getPositionProvider() {
        return $this->positionProvider;
    }

    /**
     *
     * @return The current proxy settings used by the server connector,
     * or {@code null} if no proxy is set.
     */
    public function getProxy() {
        return $this->serverConnector->getProxy();
    }

    /**
     * Sets the proxy settings to be used by the rest client.
     * @param proxySettings The proxy settings to be used by the rest client.
     * If {@code null} then no proxy is set.
     */
    public function setProxy(ProxySettings $proxySettings) {
        $this->serverConnector->setProxy($proxySettings);
    }

    /**
     * @param failureReports The failure reports setting.
     * @see FailureReports
     */
    public function setFailureReports(FailureReports $failureReports) {
        $this->failureReports = $failureReports;
    }

    /**
     * @return the failure reports setting.
     */
    public function getFailureReports() {
        return $this->failureReports;
    }

    /**
     * Updates the match settings to be used for the session.
     *
     * @param defaultMatchSettings The match settings to be used for the
     *                             session.
     */
    public function setDefaultMatchSettings(ImageMatchSettings $defaultMatchSettings) {
        ArgumentGuard::notNull($defaultMatchSettings, "defaultMatchSettings");
        $this->defaultMatchSettings = $defaultMatchSettings;
    }

    /**
     *
     * @return The match settings used for the session.
     */
    public function getDefaultMatchSettings() {
        return $this->defaultMatchSettings;
    }

    /**
     * This function is deprecated. Please use
     * {@link #setDefaultMatchSettings} instead.
     * <p>
     * The test-wide match level to use when checking application screenshot
     * with the expected output.
     *
     * @param matchLevel The match level setting.
     * @see com.applitools.eyes.MatchLevel
     */
    public function setMatchLevel($matchLevel) {
        $this->defaultMatchSettings->setMatchLevel($matchLevel);
    }

    /**
     * @deprecated  Please use{@link #getDefaultMatchSettings} instead.
     * @return The test-wide match level.
     */
    public function getMatchLevel() {
        return $this->defaultMatchSettings->getMatchLevel();
    }
    
    /**
     * @return The maximum time in seconds {@link #checkWindowBase
     * (RegionProvider, String, boolean, int)} waits for a match.
     */
    public function getMatchTimeout() {
        return $this->matchTimeout;
    }

    /**
     * Sets the maximal time (in seconds) a match operation tries to perform
     * a match.
     *
     * @param seconds Total number of seconds to wait for a match.
     */
    public function setMatchTimeout($seconds) {
        if ($this->getIsDisabled()) {
            $this->logger->verbose("Ignored");
            return;
        }

        $this->logger->verbose("setMatchTimeout(" . $seconds . ")");
        ArgumentGuard::greaterThanOrEqualToZero($seconds, "seconds");

        $this->matchTimeout = $seconds;

        $this->logger->log("Match timeout set to " . $seconds . " second(s)");
    }

    /**
     * Adds a mouse trigger.
     *
     * @param action  Mouse action.
     * @param control The control on which the trigger is activated
     *                (location is relative to the window).
     * @param cursor  The cursor's position relative to the control.
     */
    protected function addMouseTriggerBase(MouseAction $action, Region $control, Location $cursor) {
        if ($this->getIsDisabled()) {
            $this->logger->verbose(sprintf("Ignoring %s (disabled)", $action));
            return;
        }

        ArgumentGuard::notNull($action, "action");
        ArgumentGuard::notNull($control, "control");
        ArgumentGuard::notNull($cursor, "cursor");

        // Triggers are actually performed on the previous window.
        if ($this->lastScreenshot == null) {
            $this->logger->verbose(sprintf("Ignoring %s (no screenshot)", $action));
            return;
        }

        // Getting the location of the cursor in the screenshot
        $cursorInScreenshot = new Location($cursor);
        // First we need to getting the cursor's coordinates relative to the
        // context (and not to the control).
        $cursorInScreenshot->offset($control->getLocation());
        try {
            $cursorInScreenshot = $this->lastScreenshot->getLocationInScreenshot(
                $cursorInScreenshot, CoordinatesType::CONTEXT_RELATIVE);
        } catch (OutOfBoundsException $e) {
            $this->logger->verbose(sprintf("Ignoring %s (out of bounds)", $action));
            return;
        }

        $controlScreenshotIntersect = $this->lastScreenshot->getIntersectedRegion($control,
        CoordinatesType::CONTEXT_RELATIVE,
        CoordinatesType::SCREENSHOT_AS_IS);

        // If the region is NOT empty, we'll give the coordinates relative to
        // the control.
        if (!$controlScreenshotIntersect->isEmpty()) {
            $l = $controlScreenshotIntersect->getLocation();
            $cursorInScreenshot->offset(-$l->getX(), -$l->getY());
        }

        $trigger = new MouseTrigger($action, $controlScreenshotIntersect, $cursorInScreenshot);
        addUserInput($trigger);

        $this->logger->verbose(sprintf("Added %s", $trigger));
    }

    /**
     * Adds a text trigger.
     *
     * @param control The control's position relative to the window.
     * @param text    The trigger's text.
     */
    protected function addTextTriggerBase(Region $control, $text) {
        if ($this->getIsDisabled()) {
            $this->logger->verbose(sprintf("Ignoring '%s' (disabled)", $text));
            return;
        }

        ArgumentGuard::notNull($control, "control");
        ArgumentGuard::notNull($text, "text");

        // We don't want to change the objects we received.
        $control = new Region($control);

        if ($this->lastScreenshot == null) {
            $this->logger->verbose(sprintf("Ignoring '%s' (no screenshot)", $text));
            return;
        }

        $control = $this->lastScreenshot->getIntersectedRegion($control,
                CoordinatesType::CONTEXT_RELATIVE,
                CoordinatesType::SCREENSHOT_AS_IS);
        if ($control->isEmpty()) {
            $this->logger->verbose(sprintf("Ignoring '%s' (out of bounds)", $text));
            return;
        }

        $trigger = new TextTrigger($control, $text);
        $this->addUserInput($trigger);

        $this->logger->verbose(sprintf("Added %s", $trigger));
    }

    /**
     * Adds a trigger to the current list of user inputs.
     *
     * @param trigger The trigger to add to the user inputs list.
     */
    protected function addUserInput(Trigger $trigger) {
        if ($this->isDisabled) {
            return;
        }
        ArgumentGuard::notNull($trigger, "trigger");
        $this->userInputs[] = $trigger;
    }


    /**
     * Clears the user inputs list.
     */
    protected function clearUserInputs() {
        if ($this->isDisabled) {
            return;
        }
        $this->userInputs = array();
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
        return $this->userInputs;
    }


    /**
     * @param baselineName If specified, determines the baseline to compare
     *                     with and disables automatic baseline inference.
     */
    public function setBaselineName($baselineName) {
        $this->logger->log("Baseline name: " . $baselineName);
        
        if($baselineName == null || $baselineName->isEmpty()) {
            $this->baselineName = null;
        }
        else {
            $this->baselineName = $baselineName;
        }
    }
    
    /**
     * @return The baseline name, if specified.
     */
    public function getBaselineName() {
        return $this->baselineName;
    }


    /**
     * @return The base agent id of the SDK.
     */
    protected abstract function getBaseAgentId();

    /**
     * @return The full agent id composed of both the base agent id and the
     * user given agent id.
     */
    protected function getFullAgentId()
    {
        $agentId = $this->getAgentId();
        if ($agentId == null) {
            return $this->getBaseAgentId();
        }
        return sprintf("%s [%s]", $agentId, $this->getBaseAgentId());
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
     * @return The currently set log handler.
     */
    public function getLogHandler() {
        return $this->logger->getLogHandler();
    }

    /**
     * Sets a handler of log messages generated by this API.
     *
     * @param logHandler Handles log messages generated by this API.
     */
    public function setLogHandler(LogHandler $logHandler) {
        $this->logger->setLogHandler($logHandler);
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
     * @return The viewport size of the AUT.
     */
    protected abstract function getViewportSize();

    /**
     * @param size The required viewport size.
     */
    protected abstract function setViewportSize(WebDriver $driver = null, RectangleSize $size);

    /**
     *
     * @return The name of the application under test.
     */
    public function getAppName()
    {
        return $this->currentAppName != null ? $this->currentAppName : $this->appName;
    }

    /**
     *
     * @param appName The name of the application under test.
     */
    public function setAppName($appName) {
        $this->appName = $appName;
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
    abstract protected function getInferredEnvironment();

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

    /**
     * @return An updated screenshot.
     */
    protected abstract function getScreenshot();

    /**
     *
     * @return The URI of the eyes server.
     */
    public function getServerUrl() {
        return $this->serverConnector->getServerUrl();
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

            $this->lastScreenshot = null;
            $this->clearUserInputs();

            if (empty($this->runningSession)) {
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
            $this->runningSession = null;
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
     * Manually set the scale ratio for the images being validated.
     * @param scaleRatio The scale ratio to use, or {@code null} to reset
     *                   back to automatic scaling.
     */
    public function setScaleRatio($scaleRatio) {
        if ($scaleRatio != null) {
            $this->scaleProviderHandler = new ReadOnlyPropertyHandler(
            $this->logger, new FixedScaleProvider($scaleRatio));
        } else {
            $this->scaleProviderHandler = new SimplePropertyHandler();
            $this->scaleProviderHandler->set(new NullScaleProvider());

        }
    }

    /**
     *
     * @return The ratio used to scale the images being validated.
     */
    public function getScaleRatio() {
        return $this->scaleProviderHandler->get()->getScaleRatio();
    }

    /**
     * Set whether or not failed tests are saved by default.
     *
     * @param saveFailedTests True if failed tests should be saved by
     *                        default, false otherwise.
     */
    public function setSaveFailedTests($saveFailedTests) {
        $this->saveFailedTests = $saveFailedTests;
    }

    /**
     * Set whether or not new tests are saved by default.
     *
     * @param saveNewTests True if new tests should be saved by default.
     *                     False otherwise.
     */
    public function setSaveNewTests($saveNewTests) {
        $this->saveNewTests = $saveNewTests;
    }

    /**
     * @return The current title of of the AUT.
     */
    protected abstract function getTitle();

    /**
     * @return True if failed tests are saved by default.
     */
    public function getSaveFailedTests() {
        return $this->saveFailedTests;
    }
    
    /**
     * @return True if new tests are saved by default.
     */
    public function getSaveNewTests() {
        return $this->saveNewTests;
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
    public function checkWindowBase($regionProvider, $tag = "", $ignoreMismatch = false, $retryTimeout = null)
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

            $this->matchWindowTask = new MatchWindowTask(
                $this->logger,
                $this->serverConnector,
                $this->runningSession,
                $this->matchTimeout,
                // A callback which will call getAppOutput
                $appOutputProviderRedeclared
            );
        }
        $this->logger->log("Calling match window...");

        $result = $this->matchWindowTask->matchWindow($this->getUserInputs(), $this->lastScreenshot, $regionProvider,
            $tag, $this->shouldMatchWindowRunOnceOnTimeout, $ignoreMismatch, $retryTimeout);
        $this->logger->log("MatchWindow Done!");

        if (!$result->getAsExpected()) {
            if (!$ignoreMismatch) {
                $this->clearUserInputs();
                $this->lastScreenshot = $result->getScreenshot();
            }

            $this->shouldMatchWindowRunOnceOnTimeout = true;

            if (!$this->runningSession->getIsNewSession()) {
                $this->logger->log(sprintf("Mismatch! (%s)", $tag));
            }

            if ($this->getFailureReports() == "FailureReports::IMMEDIATE") {
                throw new /*TestFailed*/Exception(sprintf("Mismatch found in '%s' of '%s'",
                    $this->sessionStartInfo->getScenarioIdOrName(), $this->sessionStartInfo->getAppIdOrName()));
            }
        } else { // Match successful
            $this->clearUserInputs();
            $this->lastScreenshot = $result->getScreenshot();
        }

        $this->logger->log("Done!");
        return $result;
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
     * @return The user given agent id of the SDK.
     */
    public function getAgentId() {
        return $this->agentId;
    }

    /**
     * Sets the user given agent id of the SDK. {@code null} is referred to
     * as no id.
     *
     * @param agentId The agent ID to set.
     */
    public function setAgentId($agentId) {
        $this->agentId = $agentId;
    }

    /**
     * Sets the batch in which context future tests will run or {@code null}
     * if tests are to run standalone.
     *
     * @param batch The batch info to set.
     */
    public function setBatch(BatchInfo $batch) {
        if ($this->isDisabled) {
            $this->logger->verbose("Ignored");
            return;
        }
        $this->logger->verbose("setBatch(" . json_encode($batch) . ")");
        $this->batch = $batch;
    }

    /**
     * @return The currently set batch info.
     */
    public function getBatch() {
        return $this->batch;
    }

    /**
     * Manually set the the sizes to cut from an image before it's validated.
     *
     * @param cutProvider the provider doing the cut. If {@code null}, Eyes
     *                     would automatically infer if cutting is needed.
     */
    public function setImageCut(CutProvider $cutProvider) {
        if ($cutProvider != null) {
            $this->cutProviderHandler = new ReadOnlyPropertyHandler($this->logger, $cutProvider);
        } else {
            $this->cutProviderHandler = new SimplePropertyHandler();
            $this->cutProviderHandler->set(new NullCutProvider());
        }
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
     * Runs a timing test.
     *
     * @param regionProvider    Returns the region to check or the empty
     *                          rectangle to check the entire window.
     * @param action            An action to run in parallel to starting the
     *                          test, or {@code null} if no such action is
     *                          required.
     * @param deadline          The expected amount of time until finding a
     *                          match. (Seconds)
     * @param timeout           The maximum amount of time to retry matching.
     *                          (Seconds)
     * @param matchInterval     The interval for testing for a match.
     *                          (Milliseconds)
     * @return The earliest match found, or {@code null} if no match was found.
     */
    protected function testResponseTimeBase(
        RegionProvider $regionProvider, Runnable $action, $deadline, $timeout, $matchInterval) {

        if ($this->getIsDisabled()) {
            $this->logger->verbose("Ignored");
            return null;
        }

        ArgumentGuard::isValidState($this->getIsOpen(), "Eyes not open");
        ArgumentGuard::notNull($regionProvider, "regionProvider");
        ArgumentGuard::greaterThanZero($deadline, "deadline");
        ArgumentGuard::greaterThanZero($timeout, "timeout");
        ArgumentGuard::greaterThanZero($matchInterval, "matchInterval");

        $this->logger->verbose(sprintf(
                "testResponseTimeBase(regionProvider, %d, %d, %d)",
                $deadline, $timeout, $matchInterval));

        if ($this->runningSession == null) {
            $this->logger->verbose("No running session, calling start session..");
            $this->startSession();
            $this->logger->verbose("Done!");
        }

        //If there's an action to do
        $actionThread = null;
        if ($action != null) {
            $this->logger->verbose("Starting webdriver action.");
            $actionThread = new Thread($action);
            $actionThread->start();
        }

        $startTime = System::currentTimeMillis(); // microtime()??

        // A callback which will call getAppOutput
        $appOutputProvider = new AppOutputProviderRedeclared(); //FIXME need to check

        if ($this->runningSession->getIsNewSession()) {
            ResponseTimeAlgorithm::runNewProgressionSession($this->logger,
                $this->serverConnector, $this->runningSession, $appOutputProvider,
                $regionProvider, $startTime, $deadline);
            // Since there's never a match for a new session..
            $result = null;
        } else {
            $result =
                ResponseTimeAlgorithm::runProgressionSessionForExistingBaseline(
                    $this->logger, $this->serverConnector, $this->runningSession,
                    $appOutputProvider, $regionProvider, $startTime,
                    $deadline, $timeout, $matchInterval);
        }

        if ($actionThread != null) {
            // FIXME - Replace join with wait to according to the parameters
            $this->logger->verbose("Making sure 'action' thread had finished...");
            try {
                $actionThread->join(30000);
            } catch (InterruptedException $e) {
                $this->logger->verbose(
                    "Got interrupted while waiting for 'action' to finish!");
            }
        }

        $this->logger->verbose("Done!");
        return $result;
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
            $this->setViewportSize(null, $this->viewportSize);
        }

        if ($this->batch == null) {
            $this->logger->log("No batch set");
            $testBatch = new BatchInfo(null);
        } else {
            $this->logger->log("Batch is " . json_encode($this->batch));
            $testBatch = $this->batch;
        }

        $appEnv = $this->getAppEnvironment();

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
            $this->shouldMatchWindowRunOnceOnTimeout = true;
        } else {
            $this->logger->log("--- Test started - " . $testInfo);
            $this->shouldMatchWindowRunOnceOnTimeout = false;
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
            $this->clearUserInputs();

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

                $this->logger->log("--- Failed test ended. See details at " . $sessionResultsUrl);

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
                    throw new /*NewTest*/Exception(/*$results, */$message);
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


    /**
     * Ends the test.
     *
     * @param isDeadlineExceeded If {@code true} the test will fail (unless
     *                           it's a new test).
     * @throws TestFailedException
     * @throws NewTestException
     */
    protected function closeResponseTime($isDeadlineExceeded) {
        try {
            if ($this->isDisabled) {
            $this->logger->verbose("Ignored");
        }

        $this->logger->verbose(sprintf("closeResponseTime(%b)",
                $isDeadlineExceeded));
        ArgumentGuard::isValidState($this->isOpen, "Eyes not open");

        $isOpen = false;

        if ($this->runningSession == null) {
            $this->logger->verbose("Server session was not started");
            $this->logger->log("--- Empty test ended.");
            return;
        }

        $isNewSession = $this->runningSession->getIsNewSession();
        $sessionResultsUrl = $this->runningSession->getUrl();

        $this->logger->verbose("Ending server session...");
        $save = ($isNewSession && $this->saveNewTests);

        $this->logger->verbose("Automatically save test? " . $save ? "Yes" : "No");
        $results = $this->serverConnector->stopSession($this->runningSession, false, $save);

        $results->setNew($isNewSession);
        $results->setUrl($sessionResultsUrl);
        $this->logger->verbose(json_encode($results));

        if ($isDeadlineExceeded && !$isNewSession) {

            $this->logger->log("--- Failed test ended. See details at "
                   . $sessionResultsUrl);

            $message = "'" . $this->sessionStartInfo->getScenarioIdOrName()
                    . "' of '"
                    . $this->sessionStartInfo->getAppIdOrName()
                    . "'. See details at " . $sessionResultsUrl;
                throw new /*TestFailed*/Exception($results, $message);
        }

        if ($isNewSession) {
            $instructions = "Please approve the new baseline at " . $sessionResultsUrl;

            $this->logger->log("--- New test ended. " . $instructions);

            $message = "'" . $this->sessionStartInfo->getScenarioIdOrName()
                    . "' of '" . $this->sessionStartInfo
                    . $this->getAppIdOrName()
                    . "'. " . $instructions;
                throw new /*NewTest*/Exception($results, $message);
        }

        // Test passed
        $this->logger->log("--- Test passed. See details at " . $sessionResultsUrl);

        } finally {
            // Making sure that we reset the running session even if an
            // exception was thrown during close.
            $runningSession = null;
            $currentAppName = null;
            $this->logger->getLogHandler()->close();
        }
    }
}
