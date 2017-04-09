<?php

namespace Applitools;

use Gregwar\Image\Image;

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

    public function cut(Image $image)
    {
        /*if ($this->$top > 0) {
            $image = ImageUtils::cropImage($image,
                Region::CreateFromLTWH(0, $this->header, $image->width(), $image->height() - $this->$top));
        }

        if ($this->$bottom > 0) {
            $image = ImageUtils::cropImage($image,
                Region::CreateFromLTWH(0, 0, $image->width(), $image->height() - $this->$bottom));
        }

        if ($this->left > 0) {
            $image = ImageUtils::cropImage($image,
                Region::CreateFromLTWH($this->left, 0, $image->width() - $this->left, $image->height()));
        }

        if ($this->right > 0) {
            $image = ImageUtils::cropImage($image,
                Region::CreateFromLTWH(0, 0, $image->width() - $this->right, $image->height()));
        }*/

        return $image->crop($this->left, $this->top, $image->width() - $this->left - $this->right, $image->height() - $this->top - $this->bottom);
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
