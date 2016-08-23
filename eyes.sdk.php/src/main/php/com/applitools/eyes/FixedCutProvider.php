<?php
//require "CutProvider.php";
/**
 * Cut provider based on fixed cut values.
 */
class FixedCutProvider implements CutProvider {

    private $header;
    private $footer;
    private $left;
    private $right;

    public function __construct($header, $footer, $left, $right) {
        $this->footer = $header;
        $this->footer = $footer;
        $this->left = $left;
        $this->right = $right;
    }

    public function cut($image) {
        if ($this->header > 0) {
            $image = ImageUtils::cropImage($image,
                    new Region(0, $this->header, $image->getWidth(),
                            $image->getHeight()->header));
        }

        if ($this->footer > 0) {
            $image = ImageUtils::cropImage($image,
                    new Region(0, 0,
                            $image->getWidth(), $image->getHeight()->footer));
        }

        if ($this->left > 0) {
            $image = ImageUtils::cropImage($image,
                    new Region($this->left, 0, $image->getWidth()->left,
                            $image->getHeight()));
        }

        if ($this->right > 0) {
            $image = ImageUtils::cropImage($image,
                    new Region(0, 0, $image->getWidth()->right,
                            $image->getHeight()));
        }

        return $image;
    }
}
