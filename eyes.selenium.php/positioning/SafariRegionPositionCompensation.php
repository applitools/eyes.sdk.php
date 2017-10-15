<?php
/**
 * Applitools software
 */

namespace Applitools\Selenium;

use Applitools\Region;

class SafariRegionPositionCompensation implements IRegionPositionCompensation
{
    /**
     * @param Region $region
     * @param double $pixelRatio
     * @return Region
     */
    function compensateRegionPosition(Region $region, $pixelRatio)
    {
        if ($pixelRatio == 1.0) {
            return $region;
        }

        if ($region->getWidth() <= 0 || $region->getHeight() <= 0) {
            return Region::$empty;
        }

        return $region->offset(0, (int) ceil($pixelRatio));
    }
}