<?php
/*
 * Applitools software.
 */

namespace Applitools\Selenium;


use Facebook\WebDriver\Remote\RemoteWebDriver;


class WebDriverInfo
{
    private $remoteWebDriver;

    /**
     * WebDriverInfo constructor.
     * @param RemoteWebDriver $remoteWebDriver
     */
    public function __construct(RemoteWebDriver $remoteWebDriver)
    {
        $this->remoteWebDriver = $remoteWebDriver;
    }

    public function getName()
    {
        return $this->remoteWebDriver->getClass()->getName();
    }

    public function getCapabilities()
    {
        return $this->remoteWebDriver->getCapabilities();
    }


}