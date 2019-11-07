<?php

namespace Applitools;

use Applitools\Exceptions\DiffsFoundException;
use Applitools\Exceptions\EyesException;
use Applitools\Exceptions\OutOfBoundsException;
use Applitools\Exceptions\TestFailedException;
use Applitools\Exceptions\NewTestException;
use Applitools\fluent\ICheckSettings;
use Applitools\fluent\ICheckSettingsInternal;

abstract class EyesBase
{

    const SEQUENTIAL = "aaa";  ///Session type FIXME
    const DEFAULT_MATCH_TIMEOUT = 5; // Seconds
    const USE_DEFAULT_TIMEOUT = -1;

    private $isDisabled;
    private $isOpen;
    private $serverConnector;

    /** @var RunningSession */
    private $runningSession;

    /** @var RectangleSize */
    protected $viewportSize;

    /** @var  BatchInfo */
    private $batch;

    private $sessionType;/*it should be class to*/
    private $currentAppName;
    private $appName;
    private $testName;

    /** @var ImageMatchSettings */
    private $defaultMatchSettings;

    private $baselineName;
    private $branchName;
    private $parentBranchName;
    private $failureReports;
    private $hostApp;
    private $hostOS;
    private $userInputs = array(); //new ArrayDeque<Trigger>();
    private $shouldMatchWindowRunOnceOnTimeout;

    /** @var  EyesScreenshot */
    protected $lastScreenshot;

    /** @var  DebugScreenshotsProvider */
    protected $debugScreenshotsProvider;

    /** @var SimplePropertyHandler */
    protected $scaleProviderHandler;

    /** @var SimplePropertyHandler */
    protected $cutProviderHandler;

    /** @var Logger */
    protected $logger;

    /** @var SessionStartInfo */
    private $sessionStartInfo;

    /** @var MatchWindowTask */
    private $matchWindowTask;

    /** @var PositionProvider */
    protected $positionProvider;

    /** @var PropertyData[] */
    private $properties = [];

    /** @var bool */
    private $saveNewTests = true;

    /** @var bool */
    private $saveFailedTests;

    /** @var string */
    private $agentId;

    /** @var boolean */
    private $isViewportSizeSet;

    /** @var ScaleMethod */
    private $scaleMethod;

    /** @var int */
    private $matchTimeout;

    public function __construct($serverUrl)
    {
        if ($this->getIsDisabled()) {
            $this->userInputs = array();
            return;
        }

        ArgumentGuard::notNull($serverUrl, "serverUrl");

        $this->logger = new Logger();
        ImageUtils::initLogger($this->logger);
        Region::initLogger($this->logger);

        $this->scaleProviderHandler = new SimplePropertyHandler();
        $scaleProvider = new NullScaleProvider();
        $this->scaleProviderHandler->set($scaleProvider);

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
        $this->lastScreenshot = null;
        $this->debugScreenshotsProvider = new NullDebugScreenshotProvider();
    }

    /**
     * @param string $hostOS The host OS running the AUT.
     */
    public function setHostOS($hostOS)
    {
        $this->logger->log("Host OS: $hostOS");

        if (empty($hostOS)) {
            $this->hostOS = null;
        } else {
            $this->hostOS = trim($hostOS);
        }
    }

    /**
     * @return string get the host OS running the AUT.
     */
    public function getHostOS()
    {
        return $this->hostOS;
    }

    /**
     * @return string The application name running the AUT.
     */
    public function getHostApp()
    {
        return $this->hostApp;
    }

    /**
     * @param string $hostApp The application running the AUT (e.g., Chrome).
     */
    public function setHostApp($hostApp)
    {
        $this->logger->log("Host App: " . $hostApp);

        if ($hostApp == null || $hostApp == '') {
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
     * @param string $branchName Branch name or {@code null} to specify the default branch.
     */
    public function setBranchName($branchName)
    {
        $this->branchName = $branchName;
    }

    /**
     *
     * @return string The current branch (see {@link #setBranchName(String)}).
     */
    public function getBranchName()
    {
        return $this->branchName;
    }

    /**
     * Sets the branch under which new branches are created. (see {@link
     * #setBranchName(String)}.
     *
     * @param string $branchName Branch name or {@code null} to specify the default branch.
     */
    public function setParentBranchName($branchName)
    {
        $this->parentBranchName = $branchName;
    }

    /**
     *
     * @return string The name of the current parent branch under which new branches
     * will be created. (see {@link #setParentBranchName(String)}).
     */
    public function getParentBranchName()
    {
        return $this->parentBranchName;
    }

    /**
     * @return DebugScreenshotsProvider|NullDebugScreenshotProvider
     */
    public function getDebugScreenshotsProvider()
    {
        return $this->debugScreenshotsProvider;
    }

    /**
     * @return PositionProvider The currently set position provider.
     */
    protected function getPositionProvider()
    {
        return $this->positionProvider;
    }

    /**
     *
     * @return ProxySettings The current proxy settings used by the server connector,
     * or {@code null} if no proxy is set.
     */
    public function getProxy()
    {
        return $this->serverConnector->getProxy();
    }

    /**
     * Sets the proxy settings to be used by the rest client.
     * @param ProxySettings $proxySettings The proxy settings to be used by the rest client.
     * If {@code null} then no proxy is set.
     */
    public function setProxy(ProxySettings $proxySettings)
    {
        $this->serverConnector->setProxy($proxySettings);
    }

    /**
     * @param FailureReports $failureReports The failure reports setting.
     * @see FailureReports
     */
    public function setFailureReports(FailureReports $failureReports)
    {
        $this->failureReports = $failureReports;
    }

    /**
     * @return string The failure reports setting.
     */
    public function getFailureReports()
    {
        return $this->failureReports;
    }

    /**
     * Updates the match settings to be used for the session.
     *
     * @param ImageMatchSettings $defaultMatchSettings The match settings to be used for the session.
     */
    public function setDefaultMatchSettings(ImageMatchSettings $defaultMatchSettings)
    {
        ArgumentGuard::notNull($defaultMatchSettings, "defaultMatchSettings");
        $this->defaultMatchSettings = $defaultMatchSettings;
    }

    /**
     *
     * @return ImageMatchSettings The match settings used for the session.
     */
    public function getDefaultMatchSettings()
    {
        return $this->defaultMatchSettings;
    }

    /**
     * This function is deprecated. Please use
     * {@link #setDefaultMatchSettings} instead.
     * The test-wide match level to use when checking application screenshot
     * with the expected output.
     *
     * @param string $matchLevel The match level setting.
     * @see MatchLevel
     */
    public function setMatchLevel($matchLevel)
    {
        $this->defaultMatchSettings->setMatchLevel($matchLevel);
    }

    /**
     * @deprecated  Please use{@link #getDefaultMatchSettings} instead.
     * @return string The test-wide match level.
     */
    public function getMatchLevel()
    {
        return $this->defaultMatchSettings->getMatchLevel();
    }

    /**
     * @return int The maximum time in seconds {@link #checkWindowBase
     * (RegionProvider, String, boolean, int)} waits for a match.
     */
    public function getMatchTimeout()
    {
        return $this->matchTimeout;
    }

    /**
     * Sets the maximal time (in seconds) a match operation tries to perform a match.
     *
     * @param int $seconds Total number of seconds to wait for a match.
     */
    public function setMatchTimeout($seconds)
    {
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
     * @param string $action Mouse action.
     * @param Region $control The control on which the trigger is activated
     *                (location is relative to the window).
     * @param Location $cursor The cursor's position relative to the control.
     */
    protected function addMouseTriggerBase($action, Region $control, Location $cursor)
    {
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
        $cursorInScreenshot = clone $cursor;
        // First we need to getting the cursor's coordinates relative to the
        // context (and not to the control).
        $loc = $control->getLocation();
        $cursorInScreenshot->offset($loc->getX(), $loc->getY());
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
        $this->addUserInput($trigger);

        $this->logger->verbose(sprintf("Added %s", json_encode($trigger)));
    }

    /**
     * Adds a text trigger.
     *
     * @param Region $control The control's position relative to the window.
     * @param string $text The trigger's text.
     */
    protected function addTextTriggerBase(Region $control, $text)
    {
        if ($this->getIsDisabled()) {
            $this->logger->verbose(sprintf("Ignoring '%s' (disabled)", $text));
            return;
        }

        ArgumentGuard::notNull($control, "control");
        ArgumentGuard::notNull($text, "text");

        // We don't want to change the objects we received.
        $control = clone $control;

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
     * @param Trigger $trigger The trigger to add to the user inputs list.
     */
    protected function addUserInput(Trigger $trigger)
    {
        if ($this->isDisabled) {
            return;
        }
        ArgumentGuard::notNull($trigger, "trigger");
        $this->userInputs[] = $trigger;
    }


    /**
     * Clears the user inputs list.
     */
    protected function clearUserInputs()
    {
        if ($this->isDisabled) {
            return;
        }
        $this->userInputs = array();
    }

    /**
     * @return Trigger[] User inputs collected between {@code checkWindowBase} invocations.
     */
    protected function getUserInputs()
    {
        if ($this->isDisabled) {
            return null;
        }
        return $this->userInputs;
    }


    /**
     * @param string $baselineName If specified, determines the baseline to compare with and disables automatic baseline inference.
     */
    public function setBaselineName($baselineName)
    {
        $this->logger->log("Baseline name: " . $baselineName);

        if ($baselineName == null || $baselineName == '') {
            $this->baselineName = null;
        } else {
            $this->baselineName = $baselineName;
        }
    }

    /**
     * @return string The baseline name, if specified.
     */
    public function getBaselineName()
    {
        return $this->baselineName;
    }

    /**
     * @return string The base agent id of the SDK.
     */
    protected abstract function getBaseAgentId();

    /**
     * @return string The SDK version.
     */
    protected function getVersion()
    {
        return "1.3.1";
    }

    /**
     * @return string The full agent id composed of both the base agent id and the user given agent id.
     */
    public function getFullAgentId()
    {
        $agentId = $this->getAgentId();
        if ($agentId == null) {
            return $this->getBaseAgentId();
        }
        return "$agentId [{$this->getBaseAgentId()}]";
    }

    /**
     * @param bool $isDisabled If true, all interactions with this API will be silently ignored.
     */
    public function setIsDisabled($isDisabled)
    {
        $this->isDisabled = $isDisabled;
    }

    /**
     * @return bool Whether eyes is disabled.
     */
    public function getIsDisabled()
    {
        return $this->isDisabled;
    }

    /**
     * @return string The currently set API key or {@code null} if no key is set.
     */
    public function getApiKey()
    {
        return $this->serverConnector->getApiKey();
    }

    /**
     * Sets the API key of your applitools Eyes account.
     *
     * @param $apiKey string The api key to set.
     */
    public function setApiKey($apiKey)
    {
        ArgumentGuard::notNull($apiKey, "apiKey");
        $this->serverConnector->setApiKey($apiKey);
    }

    /**
     * @return LogHandler The currently set log handler.
     */
    public function getLogHandler() : LogHandler
    {
        return $this->logger->getLogHandler();
    }

    /**
     * @return Logger The logger.
     */
    public function getLogger() : Logger
    {
        return $this->logger;
    }

    /**
     * Sets a handler of log messages generated by this API.
     *
     * @param LogHandler $logHandler Handles log messages generated by this API.
     */
    public function setLogHandler(LogHandler $logHandler)
    {
        $this->logger->setLogHandler($logHandler);
    }

    /**
     * @return bool Whether a session is open.
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
     * @return RectangleSize The viewport size of the AUT.
     */
    protected abstract function getViewportSize();

    /**
     * @param RectangleSize $size The required viewport size.
     */
    protected abstract function setViewportSize(RectangleSize $size);

    /**
     *
     * @return string The name of the application under test.
     */
    public function getAppName()
    {
        return $this->currentAppName != null ? $this->currentAppName : $this->appName;
    }

    /**
     *
     * @param string $appName The name of the application under test.
     */
    public function setAppName($appName)
    {
        $this->appName = $appName;
    }

    /**
     * @return string The inferred environment string
     * or {@code null} if none is available. The inferred string is in the
     * format "source:info" where source is either "useragent" or "pos".
     * Information associated with a "useragent" source is a valid browser user
     * agent string. Information associated with a "pos" source is a string of
     * the format "process-name;os-name" where "process-name" is the name of the
     * main module of the executed process and "os-name" is the OS name.
     */
    abstract protected function getInferredEnvironment();

    /**
     * Application environment is the environment (e.g., the host OS) which runs the application under test.
     * @return AppEnvironment The current application environment.
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
     * @param string $serverUrl The URI of the rest server, or {@code null} to use the default server.
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
     * @return EyesScreenshot An updated screenshot.
     */
    public abstract function getScreenshot();

    /**
     *
     * @return string The URI of the eyes server.
     */
    public function getServerUrl()
    {
        return $this->serverConnector->getServerUrl();
    }

    public static function getDefaultServerUrl()
    {
        return "https://eyesapi.applitools.com";
    }

    /** Superseded by {@link #setHostOS(String)} and {@link #setHostApp (String)}.
     * Sets the OS (e.g., Windows) and application (e.g., Chrome) that host the
     * application under test.
     *
     * @param string $hostOS The name of the OS hosting the application under test or {@code null} to auto-detect.
     * @param string $hostApp The name of the application hosting the application under test or {@code null} to auto-detect.
     */
    public function setAppEnvironment($hostOS, $hostApp)
    {
        if ($this->isDisabled) {
            $this->logger->verbose("Ignored");
            return;
        }

        $this->logger->log("Warning: SetAppEnvironment is deprecated! Please use 'setHostOS' and 'setHostApp'");

        $this->logger->verbose("setAppEnvironment($hostOS, $hostApp)");
        $this->setHostOS($hostOS);
        $this->setHostApp($hostApp);
    }

    /**
     * If a test is running, aborts it. Otherwise, does nothing.
     * @throws \Exception
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
            } catch (\Exception $ex) {
                $this->logger->log("Failed to abort server session: " . $ex->getMessage());
            }
        } finally {
            $this->runningSession = null;
            $this->logger->getLogHandler()->close();
        }
    }

    /**
     * @param $appName
     * @param $testName
     * @param RectangleSize|null $viewportSize
     * @param SessionType|null $sessionType
     * @throws EyesException
     * @throws \Exception
     */
    public function openBase($appName, $testName, RectangleSize $viewportSize = null, SessionType $sessionType = null)
    {
        $this->logger->getLogHandler()->open();

        try {
            if ($this->isDisabled) {
                $this->logger->verbose("Ignored");
                return;
            }

            // If there's no default application name, one must be provided for the current test.
            if ($appName == null) {
                ArgumentGuard::notNull($this->appName, "appName");
            }

            ArgumentGuard::notNull($testName, "testName");

            $this->logger->log("Agent = " . $this->getFullAgentId());
            $this->logger->verbose("openBase('$appName', '$testName', '$viewportSize')");

            $this->validateApiKey();
            $this->logOpenBase();
            $this->validateNoSession();

            $this->currentAppName = $appName != null ? $appName : $this->appName;
            $this->testName = $testName;
            $this->viewportSize = $viewportSize;
            $this->sessionType = $sessionType != null ? $sessionType : SessionType::SEQUENTIAL;

            $scaleProvider = new NullScaleProvider();
            $this->scaleProviderHandler->set($scaleProvider);
            $this->setScaleMethod(ScaleMethod::getDefault());

            $this->ensureRunningSession();

            $this->isOpen = true;
        } catch (EyesException $e) {
            $this->logger->log($e->getMessage());
            $this->logger->getLogHandler()->close();
            throw $e;
        }
    }

    private function logOpenBase()
    {
        $this->logger->log("Eyes server URL is '{$this->serverConnector->getServerUrl()}'");
        $this->logger->verbose("Timeout = {$this->serverConnector->getTimeout()}");
        $this->logger->log("matchTimeout = '{$this->matchTimeout}' ");
        $this->logger->log("Default match settings = '{$this->defaultMatchSettings}'");
        $this->logger->log("FailureReports = '{$this->failureReports}'");
    }

    /**
     * @throws EyesException
     */
    private function validateApiKey()
    {
        if ($this->getApiKey() == null) {
            $errMsg = "API key is missing! Please set it using setApiKey()";
            $this->logger->log($errMsg);
            throw new EyesException($errMsg);
        }
    }

    /**
     * @throws EyesException
     * @throws \Exception
     */
    private function validateNoSession()
    {
        if ($this->isOpen) {
            $this->abortIfNotClosed();
            $errMsg = "A test is already running";
            $this->logger->log($errMsg);
            throw new EyesException($errMsg);
        }
    }

    /**
     * @param PositionProvider $positionProvider The position provider to be used.
     */
    protected function setPositionProvider(PositionProvider $positionProvider)
    {
        $this->positionProvider = $positionProvider;
    }

    /**
     *
     * @param string $method The method used to perform scaling.
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
     * @param float $scaleRatio The scale ratio to use, or {@code null} to reset back to automatic scaling.
     */
    public function setScaleRatio($scaleRatio)
    {
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
     * @return float The ratio used to scale the images being validated.
     */
    public function getScaleRatio()
    {
        return $this->scaleProviderHandler->get()->getScaleRatio();
    }

    /**
     * @param bool $saveDebugScreenshots If true, will save all screenshots to local directory.
     */
    public function setSaveDebugScreenshots($saveDebugScreenshots)
    {
        $prev = $this->debugScreenshotsProvider;
        if ($saveDebugScreenshots) {
            $this->debugScreenshotsProvider = new FileDebugScreenshotsProvider();
        } else {
            $this->debugScreenshotsProvider = new NullDebugScreenshotProvider();
        }
        $this->debugScreenshotsProvider->setPrefix($prev->getPrefix());
        $this->debugScreenshotsProvider->setPath($prev->getPath());
    }

    /**
     *
     * @return True if screenshots saving enabled.
     */
    public function getSaveDebugScreenshots()
    {
        return !($this->debugScreenshotsProvider instanceof NullDebugScreenshotProvider);
    }


    /**
     * @param string $pathToSave Path where you want to save the debug screenshots.
     */

    public function setDebugScreenshotsPath($pathToSave)
    {
        $this->debugScreenshotsProvider->setPath($pathToSave);
    }

    /**
     *
     * @return string The path where you want to save the debug screenshots.
     */
    public function getDebugScreenshotsPath()
    {
        return $this->debugScreenshotsProvider->getPath();
    }

    /**
     * @param string $prefix The prefix for the screenshots' names.
     */
    public function setDebugScreenshotsPrefix($prefix)
    {
        $this->debugScreenshotsProvider->setPrefix($prefix);
    }

    /**
     *
     * @return string The prefix for the screenshots' names.
     */
    public function getDebugScreenshotsPrefix()
    {
        return $this->debugScreenshotsProvider->getPrefix();
    }

    /**
     * Set whether or not failed tests are saved by default.
     *
     * @param bool $saveFailedTests True if failed tests should be saved by default, false otherwise.
     */
    public function setSaveFailedTests($saveFailedTests)
    {
        $this->saveFailedTests = $saveFailedTests;
    }

    /**
     * Set whether or not new tests are saved by default.
     *
     * @param bool $saveNewTests True if new tests should be saved by default. False otherwise.
     */
    public function setSaveNewTests($saveNewTests)
    {
        $this->saveNewTests = $saveNewTests;
    }

    /**
     * @return string The current title of of the AUT.
     */
    public abstract function getTitle();

    /**
     * @return True if failed tests are saved by default.
     */
    public function getSaveFailedTests()
    {
        return $this->saveFailedTests;
    }

    /**
     * @return True if new tests are saved by default.
     */
    public function getSaveNewTests()
    {
        return $this->saveNewTests;
    }

    /**
     * Takes a snapshot of the application under test and matches it with the expected output.
     *
     * @param RegionProvider $regionProvider Returns the region to check or an empty rectangle to check the entire window.
     * @param string $tag An optional tag to be associated with the snapshot.
     * @param bool $ignoreMismatch Whether to ignore this check if a mismatch is found.
     * @param ICheckSettings $checkSettings The check settings to use.
     * @return MatchResult The result of matching the output with the expected output.
     * @throws TestFailedException
     */
    public function checkWindowBase(RegionProvider $regionProvider, $tag, $ignoreMismatch, ICheckSettings $checkSettings = null)
    {
        if ($this->getIsDisabled()) {
            $this->logger->log("Ignored");
            $result = new MatchResult();
            $result->setAsExpected(true);
            return $result;
        }

        ArgumentGuard::isValidState($this->getIsOpen(), "Eyes not open");
        ArgumentGuard::notNull($regionProvider, "regionProvider");

        $this->logger->log("CheckWindowBase(regionProvider, $tag, $ignoreMismatch, $checkSettings)");

        $this->logger->log("Calling match window...");

        $result = $this->matchWindow($regionProvider, $tag, $ignoreMismatch, $checkSettings);

        $this->logger->log("MatchWindow Done!");

        if (!$ignoreMismatch) {
            $this->clearUserInputs();
            $this->lastScreenshot = $result->getScreenshot();
        }

        $this->validateResult($tag, $result);

        $this->logger->log("Done!");
        return $result;
    }

    /**
     * @throws \Exception
     */
    private function ensureRunningSession()
    {
        if ($this->runningSession != null) {
            return;
        }

        $this->logger->log("No running session, calling start session...");
        $this->startSession();
        $this->logger->log("Done!");

        $appOutputProviderRedeclared = new AppOutputProviderRedeclared($this, $this->logger);

        $this->matchWindowTask = new MatchWindowTask(
            $this->logger,
            $this->serverConnector,
            $this->runningSession,
            $this->matchTimeout,
            // A callback which will call getAppOutput
            $appOutputProviderRedeclared
        );
    }

    /**
     * @return string The user given agent id of the SDK.
     */
    public function getAgentId()
    {
        return $this->agentId;
    }

    /**
     * Sets the user given agent id of the SDK. {@code null} is referred to as no id.
     *
     * @param string %agentId The agent ID to set.
     */
    public function setAgentId($agentId)
    {
        $this->agentId = $agentId;
    }

    /**
     * Sets the batch in which context future tests will run or {@code null}
     * if tests are to run standalone.
     *
     * @param BatchInfo $batch The batch info to set.
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
     * @return BatchInfo The currently set batch info.
     */
    public function getBatch()
    {
        return $this->batch;
    }

    /**
     * Compresses a given screenshot.
     *
     * @param EyesScreenshot $screenshot The screenshot to compress.
     * @param EyesScreenshot $lastScreenshot The previous screenshot, or null.
     * @return string A base64 encoded compressed screenshot.
     */
    public function compressScreenshot64(EyesScreenshot $screenshot, EyesScreenshot $lastScreenshot = null)
    {
        ArgumentGuard::notNull($screenshot, "screenshot");

        $screenshotImage = $screenshot->getImage();
        ob_start();
        imagepng($screenshotImage, null);
        $uncompressed = ob_get_clean();

        $source = ($lastScreenshot != null) ? $lastScreenshot->getImage() : null;

        // Compressing the screenshot
        try {
            $compressedScreenshot = ImageDeltaCompressor::compressByRawBlocks($screenshotImage, $uncompressed, $source);
        } catch (\Exception $e) {
            $this->logger->log("Failed to compress screenshot! {$e->getMessage()}");
            return base64_encode($uncompressed);
        }

        return base64_encode($compressedScreenshot); //FIXME just need to check
    }

    /**
     * Start eyes session on the eyes server.
     * @throws \Exception
     */
    protected function startSession()
    {
        $this->logger->log("startSession()");

        $this->ensureViewportSize();

        if ($this->batch == null) {
            $this->logger->log("No batch set");
            $testBatch = new BatchInfo(null);
        } else {
            $this->logger->log("Batch is $this->batch");
            $testBatch = $this->batch;
        }

        $appEnv = $this->getAppEnvironment();

        $this->logger->log("Application environment is $appEnv");

        $this->sessionStartInfo = new SessionStartInfo($this->getBaseAgentId(), $this->sessionType,
            $this->getAppName(), null, $this->testName, $testBatch,
            isset($this->baselineName) ? $this->baselineName : (isset($_SERVER["APPLITOOLS_BASELINE_BRANCH"]) ? $_SERVER["APPLITOOLS_BASELINE_BRANCH"] : null),
            $appEnv,
            $this->defaultMatchSettings,
            isset($this->branchName) ? $this->branchName : (isset($_SERVER["APPLITOOLS_BRANCH"]) ? $_SERVER["APPLITOOLS_BRANCH"] : null),
            isset($this->parentBranchName) ? $this->parentBranchName : (isset($_SERVER["APPLITOOLS_PARENT_BRANCH"]) ? $_SERVER["APPLITOOLS_PARENT_BRANCH"] : null),
            $this->properties);

        $this->logger->log("Starting server session...");
        $this->runningSession = $this->serverConnector->startSession($this->sessionStartInfo);
        $this->logger->log("Server session ID is " . $this->runningSession->getId());

        $testInfo = "'{$this->testName}' of '{$this->getAppName()}' $appEnv";

        if ($this->runningSession->getIsNewSession()) {
            $this->logger->log("--- New test started - " . $testInfo);
            $this->shouldMatchWindowRunOnceOnTimeout = true;
        } else {
            $this->logger->log("--- Test started - " . $testInfo);
            $this->shouldMatchWindowRunOnceOnTimeout = false;
        }
    }

    private function ensureViewportSize()
    {
        if (!$this->isViewportSizeSet) {
            try {
                if ($this->viewportSize == null) {
                    $this->viewportSize = $this->getViewportSize();
                } else {
                    $this->setViewportSize($this->viewportSize);
                }
                $this->isViewportSizeSet = true;
            } catch (\Exception $e) {
                $this->isViewportSizeSet = false;
            }
        }
    }

    /**
     * Ends the test.
     *
     * @param bool $throwEx If true, an exception will be thrown for failed/new tests.
     * @return TestResults
     * @throws TestFailedException if a mismatch was found and throwEx is true.
     * @throws NewTestException    if this is a new test was found and throwEx is true.
     * @throws \Exception
     */
    public function close($throwEx = true)
    {
        try {
            if ($this->isDisabled) {
                $this->logger->verbose("Ignored");
                return null;
            }
            $this->logger->verbose("close($throwEx)");
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

            if ($results->getStatus() == TestResultsStatus::Unresolved) {
                if ($results->isNew()) {
                    $instructions = "Please approve the new baseline at " . $sessionResultsUrl;
                    $this->logger->log("--- New test ended. " . $instructions);
                    if ($throwEx) {
                        $message = "'{$this->sessionStartInfo->getScenarioIdOrName()}'" .
                            "' of '{$this->sessionStartInfo->getAppIdOrName()}" .
                            "'. $instructions";

                        throw new NewTestException($results, $message);
                    }
                } else {
                    $this->logger->log("--- Differences found. See details at $sessionResultsUrl");
                    if ($throwEx) {
                        $instructions = "See details at: " . $sessionResultsUrl;
                        $message = "Test '{$this->sessionStartInfo->getScenarioIdOrName()}'" .
                            " of '{$this->sessionStartInfo->getAppIdOrName()}'" .
                            " detected differences! " . $instructions;

                        throw new DiffsFoundException($results, $message);
                    }
                }
            } else if ($results->getStatus() == TestResultsStatus::Failed) {
                $this->logger->log("--- Failed test ended. See details at $sessionResultsUrl");

                if ($throwEx) {
                    $message = "'{$this->sessionStartInfo->getScenarioIdOrName()}'" .
                        " of '{$this->sessionStartInfo->getAppIdOrName()}'." .
                        " See details at $sessionResultsUrl";

                    throw new TestFailedException($results, $message);
                }

            } else {
                // Test passed
                $this->logger->verbose("--- Test passed. See details at " . $sessionResultsUrl);
            }

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
     * @param bool $isDeadlineExceeded If {@code true} the test will fail (unless it's a new test).
     * @throws TestFailedException
     * @throws NewTestException
     * @throws \Exception
     */
    protected function closeResponseTime($isDeadlineExceeded)
    {
        try {
            if ($this->isDisabled) {
                $this->logger->verbose("Ignored");
            }

            $this->logger->verbose(sprintf("closeResponseTime(%b)",
                $isDeadlineExceeded));
            ArgumentGuard::isValidState($this->isOpen, "Eyes not open");

            $this->isOpen = false;

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
                throw new TestFailedException($results, $message);
            }

            if ($isNewSession) {
                $instructions = "Please approve the new baseline at " . $sessionResultsUrl;

                $this->logger->log("--- New test ended. " . $instructions);

                $message = "'" . $this->sessionStartInfo->getScenarioIdOrName()
                    . "' of '" . $this->sessionStartInfo->getAppIdOrName()
                    . "'. " . $instructions;
                throw new NewTestException($results, $message);
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

    /**
     * Adds a property to be sent to the server.
     *
     * @param string $name The property name.
     * @param string $value The property value.
     */
    public function addProperty($name, $value)
    {
        $pd = new PropertyData($name, $value);
        $this->properties[] = $pd;
    }

    /**
     * Clears the list of custom properties.
     */
    public function clearProperties()
    {
        $this->properties = [];
    }

    public function getIsCutProviderExplicitlySet()
    {
        return $this->cutProviderHandler != null && !($this->cutProviderHandler->get() instanceof NullCutProvider);
    }

    /**
     * @param string $tag
     * @param MatchResult $result
     * @throws TestFailedException
     */
    private function validateResult($tag, $result)
    {
        if (!$result->getAsExpected()) {
            $this->shouldMatchWindowRunOnceOnTimeout = true;

            if (!$this->runningSession->getIsNewSession()) {
                $this->logger->log("Mismatch! ($tag)");
            }

            if ($this->getFailureReports() == "FailureReports::IMMEDIATE") {
                throw new TestFailedException("Mismatch found in '{$this->sessionStartInfo->getScenarioIdOrName()}' of '{$this->sessionStartInfo->getAppIdOrName()}'");
            }
        } else { // Match successful
            $this->clearUserInputs();
            $this->lastScreenshot = $result->getScreenshot();
        }
    }


    /**
     * @param RegionProvider $regionProvider
     * @param string $tag
     * @param bool $ignoreMismatch
     * @param ICheckSettings $checkSettings
     * @return MatchResult
     */
    private function matchWindow(RegionProvider $regionProvider, $tag, $ignoreMismatch, ICheckSettings $checkSettings = null)
    {
        $retryTimeout = -1;
        $imageMatchSettings = null;
        $checkSettingsInternal = null;
        if ($checkSettings instanceof ICheckSettingsInternal) {
            $checkSettingsInternal = $checkSettings;
            $retryTimeout = $checkSettings->getTimeout();

            $matchLevel = $checkSettings->getMatchLevel();
            $matchLevel = ($matchLevel == null) ? $this->getDefaultMatchSettings()->getMatchLevel() : $matchLevel;

            $imageMatchSettings = new ImageMatchSettings($matchLevel, null);

            $ignoreCaret = $checkSettings->getIgnoreCaret();
            $imageMatchSettings->setIgnoreCaret(($ignoreCaret == null) ? $this->getDefaultMatchSettings()->isIgnoreCaret() : $ignoreCaret);
        }

        $this->logger->verbose("Calling match window...");

        $result = $this->matchWindowTask->matchWindow($this->getUserInputs(), $regionProvider->getRegion(), $tag,
            $this->shouldMatchWindowRunOnceOnTimeout, $ignoreMismatch, $checkSettingsInternal, $this, $retryTimeout);

        return $result;
    }

    public function getAgentSetup(){
        return null;
    }

    public function getScaleProvider()
    {
        if (!empty($this->scaleProviderHandler)) {
            return get_class($this->scaleProviderHandler);
        }
        return "";
    }

    public function getCutProvider()
    {
        if (!empty($this->cutProviderHandler)) {
            return get_class($this->cutProviderHandler);
        }
        return "";
    }
}
