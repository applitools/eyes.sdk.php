<?php

namespace Tests\Applitools\Selenium;

use Applitools\BatchInfo;
use Applitools\PrintLogHandler;
use Applitools\RectangleSize;
use Applitools\Selenium\Eyes;
use Applitools\Selenium\StitchMode;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriver;
use PHPUnit\Framework\TestCase;

abstract class TestSetup extends TestCase
{

    /** @var Eyes */
    protected $eyes;

    /** @var BatchInfo */
    protected static $batchInfo;

    /** @var string */
    protected static $testSuitName;

    /** @var  DesiredCapabilities */
    protected $desiredCapabilities;

    /** @var bool */
    protected static $forceFullPageScreenshot;

    /** @var WebDriver */
    protected $webDriver;

    public static function setUpClass()
    {
        self::$batchInfo = new BatchInfo(self::$testSuitName);
        if (isSet($_SERVER['APPLITOOLS_BATCH_ID'])) {
            self::$batchInfo->setId($_SERVER['APPLITOOLS_BATCH_ID']);
        }
    }

    public function oneTimeSetUp()
    {
        $eyes = new Eyes();
        $eyes->setServerUrl($_SERVER['APPLITOOLS_SERVER_URL']);
        $eyes->setApiKey($_SERVER['APPLITOOLS_API_KEY']);
        $eyes->setHideScrollbars(true);
        $eyes->setStitchMode(StitchMode::CSS);
        $eyes->setForceFullPageScreenshot(self::$forceFullPageScreenshot);
        $eyes->setLogHandler(new PrintLogHandler(true));

        $eyes->setDebugScreenshotsPath('c:/temp/logs');
        //$eyes->setSaveDebugScreenshots(true);

        $this->eyes = $eyes;
    }

    public function init($testName)
    {
        $this->oneTimeSetUp();

        $this->eyes->setDebugScreenshotsPrefix($testName);

        $webDriver = RemoteWebDriver::create($_SERVER['SELENIUM_SERVER_URL'], $this->desiredCapabilities);
        $this->eyes->setBatch(self::$batchInfo);

        $this->webDriver = $this->eyes->open($webDriver, self::$testSuitName, $testName, new RectangleSize(800, 599));
        $this->webDriver->get('http://applitools.github.io/demo/TestPages/FramesTestPage/');
    }

    public function tearDown()
    {
        try {
            if ($this->eyes->getIsOpen()) {
                $this->eyes->close();
            }
        } finally {
            $this->eyes->abortIfNotClosed();
            $this->webDriver->quit();
        }
    }

}