<?php

namespace Applitools\Selenium;

use Applitools\Logger;

class RegionPositionCompensationFactory
{
    /**
     * @param UserAgent $userAgent
     * @param Eyes $eyes
     * @param Logger $logger
     * @return IRegionPositionCompensation
     */
    public static function getRegionPositionCompensation(UserAgent $userAgent = null, Eyes $eyes, Logger $logger)
    {
        if ($userAgent != null) {
            if ($userAgent->getBrowser() == BrowserNames::Firefox) {
                if (intval($userAgent->getBrowserMajorVersion()) >= 48) {
                    return new FirefoxRegionPositionCompensation($eyes, $logger);
                }
            } else if ($userAgent->getBrowser() == BrowserNames::Safari) {
                return new SafariRegionPositionCompensation();
            }
        }
        return new NullRegionPositionCompensation();
    }
}