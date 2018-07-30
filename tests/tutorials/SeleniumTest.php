<?php

require_once('../../vendor/autoload.php');

use Applitools\RectangleSize;
use Applitools\Selenium\Eyes;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\WebDriverBy;

class HelloWorld
{

    protected $url = 'https://applitools.com/helloworld';
    protected $webDriver;

    public function demo()
    {

        // Open a chrome browser.
        $capabilities = array(WebDriverCapabilityType::BROWSER_NAME => 'chrome');
        $this->webDriver = RemoteWebDriver::create('http://localhost:4444/wd/hub', $capabilities);

        $this->webDriver->get($this->url);

        // Initialize the eyes SDK and set your private API key.
        $eyes = new Eyes();

        try {

            $appName = 'Hello World!';
            $testName = 'My first PHP test!';

            // Start the test and set the browser's viewport size to 800x600
            $eyes->open($this->webDriver, $appName, $testName,
                new RectangleSize(800, 600));

            // Visual checkpoint #1.
            $eyes->checkWindow("Hello!");

            // Click the "Click me!" button
            $this->webDriver->findElement(WebDriverBy::tagName("button"))->click();

            // Visual checkpoint #2.
            $eyes->checkWindow("Click!");

            // End the test.
            $eyes->close();

        } finally {

            // Close the browser.
            $this->webDriver->quit();

            // If the test was aborted before eyes->close was called,
            // ends the test as aborted.
            $eyes->abortIfNotClosed();

        }
    }
}

$test = new HelloWorld();
$test->demo();