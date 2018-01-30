<?php

namespace Tests\Applitools\Selenium;

require_once ('TestFluentApi.php');

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

    public function setUp()
    {
        $this->viewportSize = new RectangleSize(800, 599);

        $options = new ChromeOptions();
        $options->addArguments(["disable-infobars"]);
        $this->desiredCapabilities = $options->toCapabilities();
    }
}