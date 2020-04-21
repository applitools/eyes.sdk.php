<?php

namespace Tests\Applitools\Selenium;

use Applitools\FileLogger;
use Applitools\PrintLogHandler;
use Facebook\WebDriver\Remote\RemoteWebDriver;

require_once('TestSetup.php');
require_once('TestDataProvider.php');

abstract class TestAppiumSetup extends TestSetup
{

    private $logsPath = ".";

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
            } else {
                $seleniumServerUrl="http://ondemand.saucelabs.com/wd/hub";
            }
            $this->desiredCapabilities->setCapability("name", "$testName ({$this->eyes->getFullAgentId()})");
            $this->eyes->getLogger()->log("Creating remote web driver. Server URL: $seleniumServerUrl.");
            $this->driver = RemoteWebDriver::create($seleniumServerUrl, $this->desiredCapabilities,null, 240000);
          
            $this->eyes->setBatch(TestDataProvider::$BatchInfo);

            $this->eyes->setScaleRatio($this->scaleRatio);
            $this->eyes->open($this->driver, self::$testSuitName, $testName);
        } catch (\Facebook\WebDriver\Exception\SessionNotCreatedException $sncEx) {
            throw $sncEx;
        } catch(\Exception $ex) {
            print $ex->getMessage();
        }
    }
}