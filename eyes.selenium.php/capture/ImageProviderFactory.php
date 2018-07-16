<?php

namespace Applitools\Selenium;


use Applitools\ImageProvider;
use Applitools\Logger;

class ImageProviderFactory
{
    /**
     * @param UserAgent $ua
     * @param Eyes $eyes
     * @param Logger $logger
     * @param EyesWebDriver $driver
     * @return ImageProvider
     */
    public static function getImageProvider(UserAgent $ua = null, Eyes $eyes, Logger $logger, EyesWebDriver $driver)
    {
        if ($ua != null) {
            if ($ua->getBrowser() == BrowserNames::Firefox) {
                if (intval($ua->getBrowserMajorVersion()) >= 48) {
                    return new FirefoxScreenshotImageProvider($eyes, $logger, $driver);
                }
            } else if ($ua->getBrowser() == BrowserNames::Safari) {
                return new SafariScreenshotImageProvider($eyes, $logger, $driver, $ua);
            }
        }
        return new TakesScreenshotImageProvider($logger, $driver);
    }
}