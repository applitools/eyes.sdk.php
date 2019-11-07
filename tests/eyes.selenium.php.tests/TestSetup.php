<?php

namespace Tests\Applitools\Selenium;

use Applitools\FileLogger;
use Applitools\PrintLogHandler;
use Applitools\Selenium\Eyes;
use Applitools\Selenium\StitchMode;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriver;
use PHPUnit\Framework\TestCase;

require_once('TestDataProvider.php');

abstract class TestSetup extends TestCase
{

    /** @var Eyes */
    protected $eyes;

    /** @var string */
    protected static $testSuitName;

    /** @var  DesiredCapabilities */
    protected $desiredCapabilities;

    /** @var bool */
    protected static $forceFullPageScreenshot;


    /** @var WebDriver */
    protected $driver;

    /** @var WebDriver */
    protected $webDriver;

    private $logsPath = ".";

    protected $viewportSize = null;

    protected $scaleRatio = 1.0;

    public static function setUpClass()
    {
    }

    public function oneTimeSetUp()
    {
        $eyes = new Eyes();
        if (isset($_SERVER['APPLITOOLS_SERVER_URL'])) {
            $eyes->setServerUrl($_SERVER['APPLITOOLS_SERVER_URL']);
        }
        $eyes->setHideScrollbars(true);
        $eyes->setStitchMode(StitchMode::CSS);
        $eyes->setForceFullPageScreenshot(self::$forceFullPageScreenshot);

        $this->eyes = $eyes;
    }

    public function init($testName)
    {
        try {
            $this->oneTimeSetUp();

            if (!isset($_SERVER["CI"])) {
                $date = date("Y_m_d H_i_s");

                if (isset($_SERVER["APPLITOOLS_LOGS_PATH"])) {
                    $this->logsPath = $_SERVER["APPLITOOLS_LOGS_PATH"];
                }

                $logPath = $this->logsPath . DIRECTORY_SEPARATOR . "PHP" . DIRECTORY_SEPARATOR . "$testName $date";
                $logFilename = $logPath . DIRECTORY_SEPARATOR . "log.log";
                $this->eyes->setLogHandler(new FileLogger($logFilename, false, true));
                $this->eyes->setSaveDebugScreenshots(true);
                $this->eyes->setDebugScreenshotsPath($logPath);
                $this->eyes->setDebugScreenshotsPrefix($testName);
            } else {
                $this->eyes->setLogHandler(new PrintLogHandler(true));
            }

            $this->eyes->getLogger()->log("Test $testName starting...");
            if (isset($_SERVER['SELENIUM_SERVER_URL'])) {
                $seleniumServerUrl = $_SERVER['SELENIUM_SERVER_URL'];
                if (strcasecmp($seleniumServerUrl, "http://ondemand.saucelabs.com/wd/hub") == 0) {
                    $this->desiredCapabilities->setCapability("name", "$testName ({$this->eyes->getFullAgentId()})");
                }
            } else {
                $seleniumServerUrl="http://127.0.0.1:4444/wd/hub";
            }
            $this->eyes->getLogger()->log("Creating remote web driver. Server URL: $seleniumServerUrl.");
            $this->driver = RemoteWebDriver::create($seleniumServerUrl, $this->desiredCapabilities);
          
            $this->eyes->setBatch(TestDataProvider::$BatchInfo);

            $this->eyes->setScaleRatio($this->scaleRatio);
            $this->webDriver = $this->eyes->open($this->driver, self::$testSuitName, $testName, $this->viewportSize);
            $this->webDriver->get('https://applitools.github.io/demo/TestPages/FramesTestPage/');
        } catch (\Facebook\WebDriver\Exception\SessionNotCreatedException $sncEx) {
            throw $sncEx;
        } catch(\Exception $ex) {
            print $ex->getMessage();
        }
    }

    /**
     * @throws \Applitools\Exceptions\NewTestException
     * @throws \Applitools\Exceptions\TestFailedException
     * @throws \Exception
     */
    protected function tearDown() : void
    {
        if ($this->driver == null) return;
        try {
            if ($this->eyes->getIsOpen()) {
                $this->eyes->close();
            }
        } finally {
            $this->eyes->abortIfNotClosed();
            $this->driver->quit();
        }
    }

}