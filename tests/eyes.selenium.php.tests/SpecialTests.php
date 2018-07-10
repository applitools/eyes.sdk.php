<?php

namespace Tests\Applitools\Selenium;

use Applitools\RectangleSize;
use Applitools\Selenium\fluent\Target;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;

require_once('TestSetup.php');

class SpecialTests extends TestSetup
{
    /** @beforeClass */
    public static function setUpClass()
    {
        self::$forceFullPageScreenshot = false;
        self::$testSuitName = "Eyes Selenium SDK - Fluent API";
        parent::setUpClass();
    }

    public function setUp()
    {
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function TestChromeIPhoneEmulator()
    {
        //$this->viewportSize = new RectangleSize(375, 667);
        $this->viewportSize = new RectangleSize(750, 1344);
        $this->scaleRatio = 2.0;
        //$this->viewportSize = new RectangleSize(981, 1744);

        $options = new ChromeOptions();
        $phoneModel ='iPhone 6';
        if (!is_null($phoneModel)){
            $mobile = ['deviceName' => $phoneModel];
            $options->setExperimentalOption('mobileEmulation', $mobile);
        }
        $caps = DesiredCapabilities::chrome();

        $caps->setCapability(ChromeOptions::CAPABILITY, $options);

        $options->addArguments(["disable-infobars"]);
        $this->desiredCapabilities = $options->toCapabilities();

        $this->init(__FUNCTION__);

        $this->eyes->check("Fluent - Window", Target::window());
    }
}