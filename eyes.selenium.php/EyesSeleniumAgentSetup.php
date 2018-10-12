<?php
/*
 * Applitools software.
 */

namespace Applitools\Selenium;

use JsonSerializable;

class EyesSeleniumAgentSetup implements JsonSerializable
{
    private $remoteWebDriver;
    private $eyes;
    private $webDriverInfo;

    public function __construct(Eyes $eyes, EyesWebDriver $driver)
    {
        $this->eyes = $eyes;
        $this->remoteWebDriver = $driver->getRemoteWebDriver();
        $this->webDriverInfo = new WebDriverInfo($this->remoteWebDriver);
    }

    public function getWebDriver()
    {
        return $this->webDriverInfo;
    }

    public function getCutProvider()
    {
        $this->eyes->getCutProvider();
    }

    public function getScaleProvider()
    {
        return $this->eyes->getScaleProvider();
    }
    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            "forceFullPageScreenshot" => $this->eyes->getForceFullPageScreenshot(),
            "stitchMode" => $this->eyes->getStitchMode(),
            "hideScrollbars" => $this->eyes->getHideScrollbars(),
            "devicePixelRatio" => $this->eyes->getDevicePixelRatio(),
            "scaleProvider" => $this->eyes->getScaleProvider(),
            "webDriver" => $this->getWebDriver(),
            "cutProvider" => $this->getCutProvider(),
            "seleniumSessionId" => $this->remoteWebDriver->getSessionID()
        ];
    }
}