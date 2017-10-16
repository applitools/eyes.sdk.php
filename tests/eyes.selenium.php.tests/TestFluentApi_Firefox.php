<?php

namespace Tests\Applitools\Selenium;

require_once ('TestFluentApi.php');

use Facebook\WebDriver\Remote\DesiredCapabilities;

class TestFluentApi_Firefox extends TestFluentApi
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
        $this->desiredCapabilities = DesiredCapabilities::firefox();
    }
}