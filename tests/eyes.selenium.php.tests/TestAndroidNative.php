<?php

namespace Tests\Applitools\Selenium;

require_once('TestAppiumSetup.php');

use Applitools\Region;
use Applitools\Selenium\fluent\Target;
use Facebook\WebDriver\Remote\DesiredCapabilities;

class TestAndroidNative extends TestAppiumSetup
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
        $capabilities->setCapability("deviceName", "Samsung Galaxy S9 WQHD GoogleAPI Emulator");
        $capabilities->setCapability("platformName", "Android");
        $capabilities->setCapability("platformVersion", "8.1");

        // The original app from Appium github project.
        $capabilities->setCapability("app", "http://appium.s3.amazonaws.com/ContactManager.apk");

        $capabilities->setCapability("username", $_SERVER["SAUCE_USERNAME"]);
        $capabilities->setCapability("accesskey", $_SERVER["SAUCE_ACCESS_KEY"]);

        $this->desiredCapabilities = $capabilities;
    }

    /**
     * @test
     * @doesNotPerformAssertions
     * @throws \Exception
     */
    public function AndroidNativeApp_checkWindow()
    {
        $this->init("AndroidNativeApp checkWindow");
        $this->eyes->check("", Target::window()->ignore(Region::CreateFromLTWH(1271, 0, 158, 100)));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     * @throws \Exception
     */
    public function AndroidNativeApp_checkRegion()
    {
        $this->init("AndroidNativeApp checkRegionFloating");
        $this->eyes->check("",
            Target::region(Region::CreateFromLTWH(0, 100, 1400, 2000))
                ->addFloatingRegion(Region::CreateFromLTWH(10, 10, 20, 20), 3, 3, 20, 30 )
        );
    }

}
