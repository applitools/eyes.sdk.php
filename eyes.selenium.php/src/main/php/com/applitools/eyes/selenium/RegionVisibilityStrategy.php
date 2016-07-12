<?php

/**
 * Encapsulates implementations for providing region visibility during
 * checkRegion.
 */
interface RegionVisibilityStrategy
{
    function moveToRegion(PositionProvider $positionProvider, Location $location);

    function returnToOriginalPosition(PositionProvider $positionProvider);
}