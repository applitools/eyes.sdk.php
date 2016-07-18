<?php
/**
 * Encapsulates rotation data for images.
 */
class ImageRotation {
    private $rotation;

    /**
     *
     * @param rotation The degrees by which to rotate.
     */
    public function __construct($rotation) {
        $this->rotation = $rotation;
    }

    /**
     *
     * @return The degrees by which to rotate.
     */
    public function getRotation() {
        return $this->rotation;
    }
}
