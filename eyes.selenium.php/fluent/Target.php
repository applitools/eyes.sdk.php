<?php

namespace Applitools\Selenium\fluent;

use Applitools\Region;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;

class Target
{

    /**
     * @return SeleniumCheckSettings
     */
    public static function window()
    {
        return new SeleniumCheckSettings();
    }

    /**
     * @param Region $region
     * @return SeleniumCheckSettings
     */
    public static function region(Region $region)
    {
        return new SeleniumCheckSettings($region);
    }

    /**
     * @param WebDriverBy $by
     * @return SeleniumCheckSettings
     */
    public static function regionBySelector(WebDriverBy $by)
    {
        return new SeleniumCheckSettings($by);
    }

    /**
     * @param WebDriverElement $webElement
     * @return SeleniumCheckSettings
     */
    public static function regionByWebElement(WebDriverElement $webElement)
    {
        return new SeleniumCheckSettings($webElement);
    }

    /**
     * @param WebDriverBy $by
     * @return SeleniumCheckSettings
     */
    public static function frameBySelector(WebDriverBy $by)
    {
        $settings = new SeleniumCheckSettings();
        $settings->frameBySelector($by);
        return $settings;
    }

    /**
     * @param string $frameNameOrId
     * @return SeleniumCheckSettings
     */
    public static function frameByNameOrId($frameNameOrId)
    {
        $settings = new SeleniumCheckSettings();
        $settings->frameByNameOrId($frameNameOrId);
        return $settings;
    }

    /**
     * @param int $index
     * @return SeleniumCheckSettings
     */
    public static function frameByIndex($index)
    {
        $settings = new SeleniumCheckSettings();
        $settings->frameByIndex($index);
        return $settings;
    }
}