<?php

require_once('../../vendor/autoload.php');

use Applitools\Selenium\Eyes;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;

class HelloWorld
{

    protected $webDriver;

    public function demo()
    {
        // Initialize the eyes SDK and set your private API key.
        $eyes = new Eyes();

        // Setup appium - Make sure the capabilities meets your environment.
        // Refer to http://appium.io documentation if required.
        $capabilities = new DesiredCapabilities();
        $capabilities->setCapability("deviceName", "iPhone 5s Simulator");
        $capabilities->setCapability("platformName", "iOS");
        $capabilities->setCapability("platformVersion", "10.0");

        // The original app from Appium github project.
        $capabilities->setCapability("app", "https://store.applitools.com/download/iOS.TestApp.app.zip");

        $capabilities->setCapability("username", $_SERVER["SAUCE_USERNAME"]);
        $capabilities->setCapability("accesskey", $_SERVER["SAUCE_ACCESS_KEY"]);


        //$driver = RemoteWebDriver::create("http://0.0.0.0:4723/wd/hub", $capabilities);
        $driver = RemoteWebDriver::create("http://ondemand.saucelabs.com/wd/hub", $capabilities,null, 240000);
        $driver->manage()->timeouts()->implicitlyWait(60);

        try {
            // Start visual UI testing
            $eyes->open($driver, "iOS test application", "test");

            // Visual validation point #1
            $eyes->checkWindow("Initial view");
            $driver->findElement(WebDriverBy::name("TextField1"))->sendKeys("3");
            $driver->findElement(WebDriverBy::name("TextField2"))->sendKeys("5");
            $driver->findElement(WebDriverBy::name("ComputeSumButton"))->click();
            // Visual validation point #2
            $eyes->checkWindow("After compute");

            // End visual UI testing. Validate visual correctness.
            $eyes->close();
        } finally {
            $eyes->abortIfNotClosed();
            $driver->quit();
        }
    }
}

$test = new HelloWorld();
$test->demo();