<?php

namespace Tests\Applitools\Selenium;

require_once('TestFluentApi.php');

use Applitools\RectangleSize;
use Facebook\WebDriver\Chrome\ChromeOptions;

class TestFluentApi_Chrome extends TestFluentApi
{
    /** @beforeClass */
    public static function setUpClass()
    {
        self::$forceFullPageScreenshot = false;
        self::$testSuitName = "Eyes Selenium SDK - Fluent API";
        parent::setUpClass();
    }

    protected function setUp() : void
    {
        $this->viewportSize = new RectangleSize(800, 600);

        $options = new ChromeOptions();

        if (strcasecmp("TRUE", $_SERVER["APPLITOOLS_RUN_HEADLESS"]) == 0) {
            $options->addArguments(["headless"]);
        }

        $this->desiredCapabilities = $options->toCapabilities();
    }
}