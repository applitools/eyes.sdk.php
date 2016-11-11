<?php
/**
 * Scale provider which determines the scale ratio according to the context.
 */
class ContextBasedScaleProvider implements ScaleProvider {

    // Allowed deviations for viewport size and default content entire size.
    const ALLOWED_VS_DEVIATION = 1;
    const ALLOWED_DCES_DEVIATION = 10;
    const UNKNOWN_SCALE_RATIO = 0;

    private $scaleMethod; //ScaleMethod
    private $scaleRatio;
    private $devicePixelRatio;
    private $topLevelContextEntireSize; //RectangleSize
    private $viewportSize; //RectangleSize

    /**
     *
     * @param topLevelContextEntireSize The total size of the top level
     *                                  context. E.g., for selenium this
     *                                  would be the document size of the top
     *                                  level frame.
     * @param viewportSize              The viewport size.
     * @param devicePixelRatio          The device pixel ratio of the
     *                                  platfrom on which the application is
     *                                  running.
     */
    public function __construct(
            RectangleSize $topLevelContextEntireSize, RectangleSize $viewportSize,
            ScaleMethod $scaleMethod, $devicePixelRatio) {

        $this->topLevelContextEntireSize = $topLevelContextEntireSize;
        $this->viewportSize = $viewportSize;
        $this->scaleMethod = $scaleMethod;
        $this->devicePixelRatio = $devicePixelRatio;

        // Since we need the image size to decide what the scale ratio is.
        $this->scaleRatio = self::UNKNOWN_SCALE_RATIO;
    }

    public function getScaleRatio() {
        ArgumentGuard::isValidState($this->scaleRatio != self::UNKNOWN_SCALE_RATIO,
                "scaleRatio not defined yet");
        return $this->scaleRatio;
    }

    public function scaleImage(Gregwar\Image\Image $image) {
        // First time an image is given we determine the scale ratio.

        if ($this->scaleRatio == self::UNKNOWN_SCALE_RATIO) {

            $imageWidth = $image->width();
            $viewportWidth = $this->viewportSize->getWidth();
            $dcesWidth = $this->topLevelContextEntireSize->getWidth();
            // If the image's width is the same as the viewport's width or the
            // top level context's width, no scaling is necessary.
            if ((($imageWidth >= $viewportWidth - self::ALLOWED_VS_DEVIATION)
                        && ($imageWidth <= $viewportWidth + self::ALLOWED_VS_DEVIATION))
                    || (($imageWidth >= $dcesWidth - self::ALLOWED_DCES_DEVIATION)
                        && $imageWidth <= $dcesWidth + self::ALLOWED_DCES_DEVIATION)) {
                $this->scaleRatio = 1;
            } else {
                $this->scaleRatio = 1 / $this->devicePixelRatio;
            }
        }
        return ImageUtils::scaleImage($image, $this->scaleMethod, $this->scaleRatio);
    }
}
