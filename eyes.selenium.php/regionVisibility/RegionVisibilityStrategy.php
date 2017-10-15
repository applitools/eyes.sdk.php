<?php

namespace Applitools\Selenium;

use Applitools\Location;
use Applitools\PositionProvider;

/**
 * Encapsulates implementations for providing region visibility during
 * checkRegion.
 */
interface RegionVisibilityStrategy
{
    function moveToRegion(PositionProvider $positionProvider, Location $location);

    function returnToOriginalPosition(PositionProvider $positionProvider);
}