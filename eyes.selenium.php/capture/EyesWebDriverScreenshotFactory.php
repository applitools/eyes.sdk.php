<?php
namespace Applitools\Selenium;
use Applitools\EyesScreenshotFactory;
use Applitools\Logger;

/**
 * Encapsulates the instantiation of an {@link EyesWebDriverScreenshot} .
 */
class EyesWebDriverScreenshotFactory implements EyesScreenshotFactory {
    private $logger; //Logger
    private $driver; //EyesWebDriver

    public function __construct(Logger $logger, EyesWebDriver $driver) {
        $this->logger = $logger;
        $this->driver = $driver;
    }

    public function makeScreenshot($image) {
        return new EyesWebDriverScreenshot($this->logger, $this->driver, $image);
    }
}

?>