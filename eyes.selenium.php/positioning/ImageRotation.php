<?php
namespace Applitools\Selenium;

/**
 * Encapsulates rotation data for images.
 */
class ImageRotation {
    private $rotation;

    /**
     *
     * @param float $rotation The degrees by which to rotate.
     */
    public function __construct($rotation) {
        $this->rotation = $rotation;
    }

    /**
     *
     * @return float The degrees by which to rotate.
     */
    public function getRotation() {
        return $this->rotation;
    }
}
