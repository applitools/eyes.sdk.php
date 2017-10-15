<?php

namespace Applitools\Selenium;

use Applitools\Region;

class NullRegionPositionCompensation implements IRegionPositionCompensation
{
    /**
     * @param Region $region
     * @param double $pixelRatio
     * @return Region
     */
    function compensateRegionPosition(Region $region, $pixelRatio)
    {
        return $region;
    }
}