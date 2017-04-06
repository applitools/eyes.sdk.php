<?php

namespace Applitools;

use Gregwar\Image\Image;

/**
 * Cut provider based on fixed cut values.
 */
class FixedCutProvider implements CutProvider
{

    private $header;
    private $footer;
    private $left;
    private $right;

    public function __construct($header, $footer, $left, $right)
    {
        $this->header = $header;
        $this->footer = $footer;
        $this->left = $left;
        $this->right = $right;
    }

    public function cut(Image $image)
    {
        if ($this->header > 0) {
            $image = ImageUtils::cropImage($image,
                new Region(0, $this->header, $image->width(), $image->height() - $this->header));
        }

        if ($this->footer > 0) {
            $image = ImageUtils::cropImage($image,
                new Region(0, 0, $image->width(), $image->height() - $this->footer));
        }

        if ($this->left > 0) {
            $image = ImageUtils::cropImage($image,
                new Region($this->left, 0, $image->width() - $this->left, $image->height()));
        }

        if ($this->right > 0) {
            $image = ImageUtils::cropImage($image,
                new Region(0, 0, $image->width() - $this->right, $image->height()));
        }

        return $image;
    }

    public function scale($scaleRatio)
    {
        $scaledHeader = (int)ceil($this->header * $scaleRatio);
        $scaledFooter = (int)ceil($this->footer * $scaleRatio);
        $scaledLeft = (int)ceil($this->left * $scaleRatio);
        $scaledRight = (int)ceil($this->right * $scaleRatio);

        return new FixedCutProvider($scaledHeader, $scaledFooter, $scaledLeft, $scaledRight);
    }
}
