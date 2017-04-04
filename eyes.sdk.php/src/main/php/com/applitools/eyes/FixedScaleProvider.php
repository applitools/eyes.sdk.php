<?php

/**
 * Scale provider based on a fixed scale ratio.
 */
class FixedScaleProvider implements ScaleProvider
{
    private $scaleRatio;
    private $scaleMethod; //ScaleMethod

    /**
     *
     * @param $scaleRatio int The scale ratio to use.
     * @param $method ScaleMethod The method used for scaling the image.
     */
    public function __construct($scaleRatio, ScaleMethod $method = null)
    {
        if (empty($method)) {
            $method = ScaleMethod::getDefault();
        }
        ArgumentGuard::greaterThanZero($scaleRatio, "scaleRatio");
        ArgumentGuard::notNull($method, "method");
        $this->scaleRatio = $scaleRatio;
        $this->scaleMethod = $method;
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
    public function scaleImage(Gregwar\Image\Image $image)
    {
        return ImageUtils::scaleImage($image, $this->scaleMethod, $this->scaleRatio);
    }
}
