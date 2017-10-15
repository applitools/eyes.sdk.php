<?php
/*
 * Applitools software.
 */

namespace Applitools\Selenium;

use Applitools\ArgumentGuard;
use Applitools\Location;
use Applitools\Logger;
use Applitools\RectangleSize;
use Facebook\WebDriver\Remote\RemoteWebElement;

/**
 * Encapsulates a frame/iframe. This is a generic type class,
 * and it's actual type is determined by the reference used by the user in
 * order to switch into the frame.
 */
class Frame {
    // A user can switch into a frame by either its name,
    // index or by passing the relevant web element.

    /** @var Logger */
    protected $logger;

    /** @var RemoteWebElement */
    protected $reference;

    /** @var Location */
    protected $location;

    /** @var RectangleSize */
    protected $size;

    /** @var RectangleSize */
    protected $innerSize;

    /** @var Location */
    protected $parentScrollPosition;

    /** @var Location */
    protected $originalLocation;

    /**
     * @param Logger $logger A Logger instance.
     * @param RemoteWebElement $reference The web element for the frame, used as a reference to
     *                  switch into the frame.
     * @param Location $location The location of the frame within the current frame.
     * @param RectangleSize $size The frame element size (i.e., the size of the frame on the
     *             screen, not the internal document size).
     * @param RectangleSize $innerSize
     * @param Location $parentScrollPosition The scroll position the frame's parent was
     *                             in when the frame was switched to.
     * @param Location $originalLocation
     */
    public function __construct(Logger $logger, RemoteWebElement $reference,
                                Location $location, RectangleSize $size, RectangleSize $innerSize,
                                Location $parentScrollPosition, Location $originalLocation) {
        ArgumentGuard::notNull($logger, "logger");
        ArgumentGuard::notNull($reference, "reference");
        ArgumentGuard::notNull($location, "location");
        ArgumentGuard::notNull($size, "size");
        ArgumentGuard::notNull($innerSize, "innerSize");
        ArgumentGuard::notNull($parentScrollPosition, "parentScrollPosition");
        ArgumentGuard::notNull($originalLocation, "originalLocation");

        $logger->verbose("Frame(logger, reference, $location, $size, $parentScrollPosition)");

        $this->logger = $logger;
        $this->reference = $reference;
        $this->parentScrollPosition = $parentScrollPosition;
        $this->size = $size;
        $this->innerSize = $innerSize;
        $this->location = $location;
        $this->originalLocation = $originalLocation;
    }

    public function getReference() {
        return $this->reference;
    }

    public function getLocation() {
        return $this->location;
    }

    public function getSize() {
        return $this->size;
    }

    public function getInnerSize() {
        return $this->innerSize;
    }

    public function getParentScrollPosition() {
        return $this->parentScrollPosition;
    }

    public function getOriginalLocation() {
        return $this->originalLocation;
    }

    public function __toString()
    {
        return $this->reference->getID();
    }
}
