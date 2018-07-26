<?php

namespace Applitools;

/**
 * Scale provider based on a fixed scale ratio.
 */
class FixedScaleProvider implements ScaleProvider
{
    private $scaleRatio;

    /**
     *
     * @param $scaleRatio float The scale ratio to use.
     */
    public function __construct($scaleRatio)
    {
        ArgumentGuard::greaterThanZero($scaleRatio, "scaleRatio");
        $this->scaleRatio = $scaleRatio;
    }

    /**
     *
     * {@inheritDoc}
     */
    public function getScaleRatio()
    {
        return $this->scaleRatio;
    }

    /**
     *
     * {@inheritDoc}
     */
    public function scaleImage($image)
    {
        return ImageUtils::scaleImage($image, $this->scaleRatio);
    }
}
