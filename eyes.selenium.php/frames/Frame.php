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
    protected $logger; //Logger
    protected $reference; //WebElement
    protected $id; //
    protected $location; //Location
    protected $size; //RectangleSize
    protected $parentScrollPosition; //Location

    /**
     * @param Logger $logger A Logger instance.
     * @param RemoteWebElement $reference The web element for the frame, used as a reference to
     *                  switch into the frame.
     * @param mixed $frameId The id of the frame. Can be used later for comparing
     *                two frames.
     * @param Location $location The location of the frame within the current frame.
     * @param RectangleSize $size The frame element size (i.e., the size of the frame on the
     *             screen, not the internal document size).
     * @param Location $parentScrollPosition The scroll position the frame's parent was
     *                             in when the frame was switched to.
     */
    public function __construct(Logger $logger, RemoteWebElement $reference, $frameId, Location $location, RectangleSize $size, Location $parentScrollPosition) {
        ArgumentGuard::notNull($logger, "logger");
        ArgumentGuard::notNull($reference, "reference");
        ArgumentGuard::notNull($frameId, "frameId");
        ArgumentGuard::notNull($location, "location");
        ArgumentGuard::notNull($size, "size");
        ArgumentGuard::notNull($parentScrollPosition, "parentScrollPosition");

        $logger->verbose("Frame(logger, reference, $frameId, $location, $size, $parentScrollPosition)");

        $this->logger = $logger;
        $this->reference = $reference;
        $this->id = $frameId;
        $this->parentScrollPosition = $parentScrollPosition;
        $this->size = $size;
        $this->location = $location;
    }

    public function getReference() {
        return $this->reference;
    }

    public function getId() {
        return $this->id;
    }

    public function getLocation() {
        return $this->location;
    }

    public function getSize() {
        return $this->size;
    }

    public function getParentScrollPosition() {
        return $this->parentScrollPosition;
    }

    public function __toString()
    {
        return $this->reference->getID();
    }
}
