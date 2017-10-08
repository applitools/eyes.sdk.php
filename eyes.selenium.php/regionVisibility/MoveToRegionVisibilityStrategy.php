<?php

namespace Applitools\Selenium;
use Applitools\Location;
use Applitools\Logger;
use Applitools\PositionProvider;

/**
 * An implementation of {@link RegionVisibilityStrategy}, which tries to move
 * to the region.
 */
class MoveToRegionVisibilityStrategy implements RegionVisibilityStrategy
{

    /** @var Logger */
    private $logger;
    private $originalPosition; //PositionMemento

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
        $this->originalPosition = null;
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
