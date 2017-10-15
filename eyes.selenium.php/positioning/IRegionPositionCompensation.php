<?php

namespace Applitools\Selenium;

use Applitools\Region;

interface IRegionPositionCompensation
{
    /**
     * @param Region $region
     * @param double $pixelRatio
     * @return Region
     */
    function compensateRegionPosition(Region $region, $pixelRatio);
}