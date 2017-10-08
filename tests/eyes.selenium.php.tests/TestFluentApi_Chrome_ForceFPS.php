<?php

namespace Tests\Applitools\Selenium;

require_once ('TestFluentApi.php');

use Facebook\WebDriver\Chrome\ChromeOptions;

class TestFluentApi_Chrome_ForceFPS extends TestFluentApi
{
    /** @beforeClass */
    public static function setUpClass()
    {
        self::$forceFullPageScreenshot = true;
        parent::setUpClass();
    }

    public function setUp()
    {
        $options = new ChromeOptions();
        $options->addArguments(["disable-infobars"]);
        $this->desiredCapabilities = $options->toCapabilities();
    }
}