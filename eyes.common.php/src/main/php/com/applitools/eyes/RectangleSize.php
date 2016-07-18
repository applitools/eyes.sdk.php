<?php

class RectangleSize {
    private $width;
    private $height;

    /**
     * Creates a new RectangleSize instance.
     * @param width The width of the rectangle.
     * @param height The height of the rectangle.
     */
    public function __construct($width, $height) {
        ArgumentGuard::greaterThanOrEqualToZero($width, "width");
        ArgumentGuard::greaterThanOrEqualToZero($height, "height");

        $this->width = $width;
        $this->height = $height;
    }

    /**
     * @return The rectangle's width.
     */
    public function getWidth() {
        return $this->width;
    }

    /**
     * @return The rectangle's height.
     */
    public function getHeight() {
        return $this->height;
    }

    /**
     * Parses a string into a {link RectangleSize} instance.
     * @param size A string representing width and height separated by "x".
     * @return An instance representing the input size.
     */
    public function parse($size) {
        ArgumentGuard::notNull($size, "size");
        $parts = $size->split("x"); //FIXME
        if ($parts->length != 2) {
            throw new IllegalArgumentException(
                    "Not a valid size string: " . $size);
        }

        return new RectangleSize($parts[0], $parts[1]); //FIXME
    }

    /**
     * @param obj A {@link com.applitools.eyes.RectangleSize} instance to be
     *            checked for equality with the current instance.
     * @return {@code true} if and only if the input objects are equal by
     *          value, {@code false} otherwise.
     */

    public function equals($obj) { //FIXME
        if ($this == $obj) {
            return true;
        }

        if (!($obj instanceof RectangleSize)) {
            return false;
        }

        $other = /*(RectangleSize)*/ $obj;
        return (($this->width == $other->width) && ($this->height == $other->height));
    }

    public function hashCode() {
        return $this->width ^ $this->height;
    }

    public function toString() {
        return $this->width . "x" . $this->height;
    }
}