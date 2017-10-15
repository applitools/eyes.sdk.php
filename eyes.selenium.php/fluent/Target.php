<?php

namespace Applitools\Selenium\fluent;

use Applitools\Exceptions\EyesException;
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
     * @param Region|WebDriverBy|WebDriverElement $region
     * @return SeleniumCheckSettings
     */
    public static function region($region)
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

    /**
     * @param WebDriverBy|string|int $frame
     * @return SeleniumCheckSettings
     * @throws EyesException
     */
    public static function frame($frame)
    {
        $settings = new SeleniumCheckSettings();
        if ($frame instanceof WebDriverBy) {
            $settings->frameBySelector($frame);
        } else if (is_string($frame)) {
            $settings->frameByNameOrId($frame);
        } else if (is_int($frame)) {
            $settings->frameByIndex($frame);
        } else {
            throw new EyesException("frame selector not supported");
        }

        return $settings;
    }
}