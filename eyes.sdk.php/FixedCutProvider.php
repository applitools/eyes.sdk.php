<?php

namespace Applitools;

/**
 * Cut provider based on fixed cut values.
 */
class FixedCutProvider implements CutProvider
{

    private $top;
    private $bottom;
    private $left;
    private $right;

    public function __construct($top, $bottom, $left, $right)
    {
        $this->top = $top;
        $this->bottom = $bottom;
        $this->left = $left;
        $this->right = $right;
    }

    /**
     * @param resource $image
     * @return resource
     */
    public function cut($image)
    {
        $imageWidth = imagesx($image);
        $imageHeight = imagesx($image);
        return imagecrop($image, [
            'x' => $this->left,
            'y' => $this->top,
            'width' => $imageWidth - $this->left - $this->right,
            'height' => $imageHeight - $this->top - $this->bottom]);
    }

    public function scale($scaleRatio)
    {
        $scaledTop = (int)ceil($this->top * $scaleRatio);
        $scaledBottom = (int)ceil($this->bottom * $scaleRatio);
        $scaledLeft = (int)ceil($this->left * $scaleRatio);
        $scaledRight = (int)ceil($this->right * $scaleRatio);

        return new FixedCutProvider($scaledTop, $scaledBottom, $scaledLeft, $scaledRight);
    }
}
