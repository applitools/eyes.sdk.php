<?php

namespace Tests\Applitools\Selenium;

require_once('TestAppiumSetup.php');

use Applitools\Region;
use Applitools\Selenium\fluent\Target;
use Facebook\WebDriver\Remote\DesiredCapabilities;

class TestIOSNative extends TestAppiumSetup
{

    /** @beforeClass */
    public static function setUpClass()
    {
        self::$forceFullPageScreenshot = false;
        self::$testSuitName = "AndroidNativeApp";
        parent::setUpClass();
    }

    protected function setUp() : void
    {
        $capabilities = new DesiredCapabilities();
        $capabilities->setCapability("browserName", "");
        $capabilities->setCapability("deviceName", "iPhone XS Simulator");
        $capabilities->setCapability("platformName", "iOS");
        $capabilities->setCapability("platformVersion", "12.2");

        // The original app from Appium github project.
        $capabilities->setCapability("app", "https://applitools.bintray.com/Examples/HelloWorldiOS_1_0.zip");

        $capabilities->setCapability("username", $_SERVER["SAUCE_USERNAME"]);
        $capabilities->setCapability("accesskey", $_SERVER["SAUCE_ACCESS_KEY"]);

        $this->desiredCapabilities = $capabilities;
    }

    /**
     * @test
     * @doesNotPerformAssertions
     * @throws \Exception
     */
    public function IOSNativeApp_checkWindow()
    {
        $this->init("iOSNativeApp checkWindow");
        $this->eyes->check("", Target::window()->ignore(Region::CreateFromLTWH(0, 0, 300, 100)));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     * @throws \Exception
     */
    public function IOSNativeApp_checkRegion()
    {
        $this->init("iOSNativeApp checkRegionFloating");
        $this->eyes->check("",
            Target::region(Region::CreateFromLTWH(0, 100, 375, 712))
                ->addFloatingRegion(Region::CreateFromLTWH(10, 10, 20, 20), 3, 3, 20, 30 )
        );
    }

}
