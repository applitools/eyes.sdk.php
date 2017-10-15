<?php

namespace Applitools;

class FloatingMatchSettings
{
    /** @var int */
    public $top;

    /** @var int */
    public $left;

    /** @var int */
    public $width;

    /** @var int */
    public $height;

    /** @var int */
    public $maxUpOffset;

    /** @var int */
    public $maxDownOffset;

    /** @var int */
    public $maxLeftOffset;

    /** @var int */
    public $maxRightOffset;

    /**
     * FloatingMatchSettings constructor.
     * @param int $left
     * @param int $top
     * @param int $width
     * @param int $height
     * @param int $maxUpOffset
     * @param int $maxDownOffset
     * @param int $maxLeftOffset
     * @param int $maxRightOffset
     */
    public function __construct($left, $top, $width, $height, $maxUpOffset, $maxDownOffset, $maxLeftOffset, $maxRightOffset)
    {
        $this->top = $top;
        $this->left = $left;
        $this->width = $width;
        $this->height = $height;
        $this->maxUpOffset = $maxUpOffset;
        $this->maxDownOffset = $maxDownOffset;
        $this->maxLeftOffset = $maxLeftOffset;
        $this->maxRightOffset = $maxRightOffset;
    }

}