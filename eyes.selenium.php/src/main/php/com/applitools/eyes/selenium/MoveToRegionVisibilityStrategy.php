<?php
//require "RegionVisibilityStrategy.php";

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
        $this->logger->log("Getting current position state..");
        $this->originalPosition = $positionProvider->getState();
        $this->logger->log("Done! Setting position..");
        $positionProvider->setPosition($location);
        $this->logger->log("Done!");
    }

    public function returnToOriginalPosition(PositionProvider $positionProvider)
    {
        $this->logger->verbose("Returning to original position...");
        $positionProvider->restoreState($this->originalPosition);
        $this->logger->log("Done!");
    }
}
