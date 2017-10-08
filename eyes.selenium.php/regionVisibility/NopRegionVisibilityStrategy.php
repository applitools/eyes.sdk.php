<?php
namespace Applitools\Selenium;
use Applitools\Location;
use Applitools\Logger;
use Applitools\PositionProvider;

/**
 * An implementation of {@link RegionVisibilityStrategy} which does nothing.
 */
class NopRegionVisibilityStrategy implements RegionVisibilityStrategy {

    /** @var Logger */
    private $logger;

    public function __construct($logger) {
        $this->logger = $logger;
    }

    public function moveToRegion(PositionProvider $positionProvider, Location $location) {
        $this->logger->verbose("Ignored (no op).");
    }

    public function returnToOriginalPosition(PositionProvider $positionProvider) {
        $this->logger->verbose("Ignored (no op).");
    }
}
