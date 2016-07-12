<?php
require "RegionVisibilityStrategy.php";

/**
 * An implementation of {@link RegionVisibilityStrategy}, which tries to move
 * to the region.
 */
class MoveToRegionVisibilityStrategy implements RegionVisibilityStrategy
{

    private $logger; //Logger
    private $originalPosition; //PositionMemento

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function moveToRegion(PositionProvider $positionProvider, Location $location)
    {
        Logger::log("Getting current position state..");
        $this->originalPosition = $positionProvider->getState();
        Logger::log("Done! Setting position..");
        $positionProvider->setPosition($location);
        Logger::log("Done!");
    }

    public function returnToOriginalPosition(PositionProvider $positionProvider)
    {
        Logger::verbose("Returning to original position...");
        $positionProvider->restoreState($this->originalPosition);
        Logger::log("Done!");
    }
}
