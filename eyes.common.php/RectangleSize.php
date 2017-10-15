<?php

namespace Applitools;

class RectangleSize
{
    private $width;
    private $height;

    /**
     * Creates a new RectangleSize instance.
     * @param $width int The width of the rectangle.
     * @param $height int The height of the rectangle.
     */
    public function __construct($width, $height)
    {
        ArgumentGuard::greaterThanOrEqualToZero($width, "width");
        ArgumentGuard::greaterThanOrEqualToZero($height, "height");

        $this->width = $width;
        $this->height = $height;
    }

    /**
     * @return int The rectangle's width.
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return int The rectangle's height.
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Parses a string into a {link RectangleSize} instance.
     * @param string $size A string representing width and height separated by "x".
     * @return RectangleSize An instance representing the input size.
     */
    public static function parse($size)
    {
        ArgumentGuard::notNull($size, "size");
        $parts = explode('x', $size);
        if ($parts->length != 2) {
            throw new \InvalidArgumentException("Not a valid size string: $size");
        }

        return new RectangleSize(intval($parts[0]), intval($parts[1]));
    }

    /**
     * @param object $other A {@link com.applitools.eyes.RectangleSize} instance to be
     *            checked for equality with the current instance.
     * @return bool {@code true} if and only if the input objects are equal by
     *          value, {@code false} otherwise.
     */
    public function equals($other)
    { //FIXME
        if ($this === $other) {
            return true;
        }

        if (!($other instanceof RectangleSize)) {
            return false;
        }

        return (($this->width == $other->width) && ($this->height == $other->height));
    }


    /**
     * Get a scaled version of the current size.
     *
     * @param double $scaleRatio The ratio by which to scale.
     * @return RectangleSize A scaled version of the current size.
     */
    public function scale($scaleRatio)
    {
        return new RectangleSize((int)ceil($this->width * $scaleRatio),
            (int)ceil($this->height * $scaleRatio));
    }


    public function hashCode()
    {
        return $this->width ^ $this->height;
    }

    public function __toString()
    {
        return $this->width . "x" . $this->height;
    }
}