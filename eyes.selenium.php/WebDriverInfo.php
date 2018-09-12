<?php
/*
 * Applitools software.
 */

namespace Applitools\Selenium;


use Facebook\WebDriver\Remote\RemoteWebDriver;
use JsonSerializable;


class WebDriverInfo implements JsonSerializable
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


    public function getCapabilities()
    {
        return $this->remoteWebDriver->getCapabilities();
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
            "name" => get_class($this->remoteWebDriver),
            "capabilities" => [
                "platform" => $this->getCapabilities()->getPlatform(),
                "version" => $this->getCapabilities()->getVersion(),
                "javascriptEnabled" => $this->getCapabilities()->isJavascriptEnabled(),
                "browserName" => $this->getCapabilities()->getBrowserName()
            ]
        ];
    }
}