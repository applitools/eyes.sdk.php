<?php
/*
 * Applitools software.
 */

namespace Applitools\Selenium;

use Applitools\Selenium\WebDriverInfo;

class EyesSeleniumAgentSetup
{
    private $remoteWebDriver;
    private $eyes;

    public function __construct(Eyes $eyes, EyesWebDriver $driver)
    {
        $this->eyes = $eyes;
        $this->remoteWebDriver = $driver->getRemoteWebDriver();
    }

    public function EyesSeleniumAgentSetup()
    {
        return $this->remoteWebDriver;
    }

    public function getSeleniumSessionId()
    {
        return $this->remoteWebDriver->getSessionId()->toString();
    }

    public function getWebDriver()
    {
        return new WebDriverInfo($this->remoteWebDriver);
    }

    public function getDevicePixelRatio()
    {
        return $this->eyes->getDevicePixelRatio();
    }

    public function getStitchMode()
    {
        return $this->eyes->getStitchMode();
    }

    public function getHideScrollbars()
    {
        return $this->eyes->getHideScrollbars();
    }

    public function getForceFullPageScreenshot()
    {
        return $this->eyes->getForceFullPageScreenshot();
    }
}