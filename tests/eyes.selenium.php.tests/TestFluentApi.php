<?php

namespace Tests\Applitools\Selenium;

use Applitools\BatchInfo;
use Applitools\Eyes;
use Applitools\fluent\Target;
use Applitools\PrintLogHandler;
use Applitools\RectangleSize;
use Applitools\Region;
use Applitools\StitchMode;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\TestCase;

class TestFluentApi extends TestCase
{
    /** @var Eyes */
    private static $eyes;

    /** @var RemoteWebDriver */
    private static $webDriver;

    /** @var BatchInfo */
    private static $batchInfo;

    /** @var string */
    private static $testSuitName;

    /**
     * @beforeClass
     */
    public static function setUpClass()
    {
        self::$testSuitName = "Eyes Selenium SDK - Fluent API";
        self::$batchInfo = new BatchInfo(self::$testSuitName);
    }

    public function setUp()
    {
        $eyes = new Eyes();
        $eyes->setServerUrl("https://localhost.applitools.com");
        $eyes->setApiKey($_SERVER['APPLITOOLS_API_KEY']);
        $eyes->setHideScrollbars(true);
        $eyes->setStitchMode(StitchMode::CSS);
        $eyes->setForceFullPageScreenshot(true);
        $eyes->setLogHandler(new PrintLogHandler(true));

        self::$webDriver = RemoteWebDriver::create($_SERVER['SELENIUM_SERVER_URL'], DesiredCapabilities::chrome());
        self::$eyes = $eyes;
        self::$eyes->setBatch(self::$batchInfo);
    }

    public function init($testName)
    {
        self::$eyes->open(self::$webDriver, self::$testSuitName, $testName, new RectangleSize(800, 599));
        self::$webDriver->get('http://applitools.github.io/demo/TestPages/FramesTestPage/');
    }

    public function tearDown()
    {
        try {
            if (self::$eyes->getIsOpen()) {
                self::$eyes->close();
            }
        } finally {
            self::$eyes->abortIfNotClosed();
            self::$webDriver->quit();
        }
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function TestCheckWindowWithIgnoreRegion_Fluent()
    {
        $this->init(__FUNCTION__);
        self::$eyes->check("Fluent - Window with Ignore region",
            Target::window()
                ->fully()
                ->timeout(5000)
                ->ignore(new Region(50, 50, 100, 100))
        );
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function TestCheckRegionWithIgnoreRegion_Fluent()
    {
        $this->init(__FUNCTION__);
        self::$eyes->check("Fluent - Region with Ignore region",
            Target::regionBySelector(WebDriverBy::id("overflowing-div"))
                ->ignore(new Region(50, 50, 100, 100))
        );
    }
    /*
    @Test
    public void TestCheckFrame_Fully_Fluent()
    {
    eyes . check("Fluent - Full Frame", Target . frame("frame1") . fully());
    }

    @Test
        public void TestCheckFrame_Fluent(){
    eyes . check("Fluent - Frame", Target . frame("frame1"));
        }

        @Test
        public void TestCheckFrameInFrame_Fully_Fluent(){
    eyes . check("Fluent - Full Frame in Frame", Target . frame("frame1")
        . frame("frame1-1")
        . fully());
        }

        @Test
        public void TestCheckRegionInFrame_Fluent(){
    eyes . check("Fluent - Region in Frame", Target . frame("frame1")
        . region(By . id("inner-frame-div"))
        . fully());
        }

        @Test
        public void TestCheckRegionInFrameInFrame_Fluent(){
    eyes . check("Fluent - Region in Frame in Frame", Target . frame("frame1")
        . frame("frame1-1")
        . region(By . tagName("img"))
        . fully());
        }

        @Test
        public void TestCheckRegionInFrame2_Fluent(){
    eyes . check("Fluent - Inner frame div 1", Target . frame("frame1")
        . region(By . id("inner-frame-div"))
        . fully()
        . timeout(5000)
        . ignore(new Region(50, 50, 100, 100)));

            eyes . check("Fluent - Inner frame div 2", Target . frame("frame1")
                . region(By . id("inner-frame-div"))
                . fully()
                . ignore(new Region(50, 50, 100, 100))
                . ignore(new Region(70, 170, 90, 90)));

            eyes . check("Fluent - Inner frame div 3", Target . frame("frame1")
                . region(By . id("inner-frame-div"))
                . fully()
                . timeout(5000));

            eyes . check("Fluent - Inner frame div 4", Target . frame("frame1")
                . region(By . id("inner-frame-div"))
                . fully());

            eyes . check("Fluent - Full frame with floating region", Target . frame("frame1")
                . fully()
                . layout()
                . floating(25, new Region(200, 200, 150, 150)));
        }

        @Test
        public void TestCheckFrameInFrame_Fully_Fluent2(){
    eyes . check("Fluent - Window with Ignore region 2", Target . window()
        . fully()
    );

            eyes . check("Fluent - Full Frame in Frame 2", Target . frame("frame1")
                . frame("frame1-1")
                . fully());
        }

        @Test
        public void TestCheckWindowWithIgnoreBySelector_Fluent(){
    eyes . check("Fluent - Window with ignore region by selector", Target . window()
        . ignore(By . id("overflowing-div")));
        }

        @Test
        public void TestCheckWindowWithFloatingBySelector_Fluent()
    {
    eyes . check("Fluent - Window with floating region by selector", Target . window()
        . floating(By . id("overflowing-div"), 3, 3, 20, 30));
        }

        @Test
        public void TestCheckElementFully_Fluent()
    {
    WebElement element = webDriver . findElement(By . id("overflowing-div-image"));
            eyes . check("Fluent - Region by element - fully", Target . region(element) . fully());
        }

        @Test
        public void TestCheckElement_Fluent()
    {
    WebElement element = webDriver . findElement(By . id("overflowing-div-image"));
            eyes . check("Fluent - Region by element", Target . region(element));
        }*/

}
