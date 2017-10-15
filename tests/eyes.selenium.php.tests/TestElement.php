<?php

namespace Tests\Applitools\Selenium;

use Applitools\Selenium\Eyes;
use Applitools\MatchLevel;
use Applitools\PrintLogHandler;
use Applitools\RectangleSize;
use Applitools\Selenium\StitchMode;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\TestCase;

class TestElement extends TestCase
{
    /** @var Eyes */
    private static $eyes;

    /** @var RemoteWebDriver */
    private static $webDriver;

    /**
     * @beforeClass
     */
    public static function setUpClass()
    {
        self::$webDriver = RemoteWebDriver::create($_SERVER['SELENIUM_SERVER_URL'], DesiredCapabilities::chrome());

        $eyes = new Eyes();
        //https://localhost.applitools.com
        if (isset($_SERVER['APPLITOOLS_SERVER_URL'])) {
            $eyes->setServerUrl($_SERVER['APPLITOOLS_SERVER_URL']);
        }
        //$eyes->setProxy(new \Applitools\ProxySettings("127.0.0.1:8888"));
        $eyes->setApiKey($_SERVER['APPLITOOLS_API_KEY']);
        $eyes->setHideScrollbars(true);
        $eyes->setStitchMode(StitchMode::CSS);
        $eyes->setMatchLevel(MatchLevel::LAYOUT);
        $eyes->setForceFullPageScreenshot(true);
        $eyes->setLogHandler(new PrintLogHandler(true) );

        self::$eyes = $eyes;
        self::$eyes->open(self::$webDriver, "Eyes Selenium SDK - PHP", "FramesElementsTest", new RectangleSize(1024, 635));

        self::$webDriver->get('http://applitools.github.io/demo/TestPages/FramesTestPage/');
    }

    /**
     * @afterClass
     */
    public static function tearDownClass()
    {
        $results = self::$eyes->close(false);
        self::$eyes->getLogHandler()->onMessage(false, "Mismatches: {$results->getMismatches()}");
        self::$eyes->abortIfNotClosed();
        self::$webDriver->close();
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function testCheckWindow()
    {
        self::$eyes->checkWindow("Window");
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function testCheckRegion()
    {
        self::$eyes->checkRegion(WebDriverBy::id("overflowing-div"), -1,"Region", true);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function testCheckFrame()
    {
        self::$eyes->checkFrame("frame1",-1, "frame1");
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function testCheckRegionInFrame()
    {
        self::$eyes->checkRegionInFrame("frame1", "inner-frame-div", "Inner frame div", true);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function testCheckRegion2()
    {
        //self::$eyes->checkFrame("frame1",-1, "frame1");

        //self::$eyes->checkRegionInFrame("frame1", "inner-frame-div", "Inner frame div", true);

        self::$eyes->checkRegion(WebDriverBy::id("overflowing-div-image"), -1, "minions", true);
    }
}