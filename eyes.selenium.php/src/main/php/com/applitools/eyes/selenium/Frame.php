<?php
/*
 * Applitools software.
 */

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
     * @param logger A Logger instance.
     * @param reference The web element for the frame, used as a reference to
     *                  switch into the frame.
     * @param frameId The id of the frame. Can be used later for comparing
     *                two frames.
     * @param location The location of the frame within the current frame.
     * @param size The frame element size (i.e., the size of the frame on the
     *             screen, not the internal document size).
     * @param parentScrollPosition The scroll position the frame's parent was
     *                             in when the frame was switched to.
     */
    public function __construct(Logger $logger, /*FIXME need to check*/RemoteWebElement $reference,
                 $frameId, Location $location, RectangleSize $size,
                 Location $parentScrollPosition) {
        ArgumentGuard::notNull($logger, "logger");
        ArgumentGuard::notNull($reference, "reference");
        ArgumentGuard::notNull($frameId, "frameId");
        ArgumentGuard::notNull($location, "location");
        ArgumentGuard::notNull($size, "size");
        ArgumentGuard::notNull($parentScrollPosition, "parentScrollPosition");

        $logger->verbose(sprintf(
                "Frame(logger, reference, %s, %s, %s, %s)", $frameId,
                json_encode($location), json_encode($size), json_encode($parentScrollPosition)));

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
}
