<?php
namespace Applitools;
/**
 * An implementation of {@link RegionVisibilityStrategy} which does nothing.
 */
class NopRegionVisibilityStrategy implements RegionVisibilityStrategy {

    private $logger; //Logger

    public function __construct($logger) {
        $this->logger = $logger;
    }

    public function moveToRegion(PositionProvider $positionProvider,
                             Location $location) {
        $this->logger->verbose("Ignored (no op).");
    }

    public function returnToOriginalPosition(PositionProvider $positionProvider) {
        $this->logger->verbose("Ignored (no op).");
    }
}
