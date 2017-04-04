<?php
namespace Applitools;

/**
 * Factory implementation for creating {@link ContextBasedScaleProvider} instances.
 */
class ContextBasedScaleProviderFactory extends ScaleProviderFactory {

    private $topLevelContextEntireSize; //RectangleSize
    private $viewportSize; //RectangleSize
    private $scaleMethod; //ScaleMethod
    private $devicePixelRatio;

    /**
     *
     *
     * @param RectangleSize $topLevelContextEntireSize The total size of the top level
     *                                  context. E.g., for selenium this
     *                                  would be the document size of the top
     *                                  level frame.
     * @param RectangleSize $viewportSize The viewport size.
     * @param ScaleMethod $scaleMethod The method used for scaling.
     * @param float $devicePixelRatio The device pixel ratio of the
     *                                  platform on which the application is
     *                                  running.
     * @param PropertyHandler $scaleProviderHandler
     */
    public function __construct(RectangleSize $topLevelContextEntireSize, RectangleSize $viewportSize,
                                            ScaleMethod $scaleMethod, $devicePixelRatio,
                                            PropertyHandler $scaleProviderHandler) {
        //super(scaleProviderHandler); //FIXME need to check
        $this->scaleProviderHandler = $scaleProviderHandler;
        $this->topLevelContextEntireSize = $topLevelContextEntireSize;
        $this->viewportSize = $viewportSize;
        $this->scaleMethod = $scaleMethod;
        $this->devicePixelRatio = $devicePixelRatio;
    }

    protected function getScaleProviderImpl($imageToScaleWidth) {
        $scaleProvider = new ContextBasedScaleProvider($this->topLevelContextEntireSize, $this->viewportSize,
                $this->scaleMethod, $this->devicePixelRatio);
        $scaleProvider->updateScaleRatio($imageToScaleWidth);
        return $scaleProvider;
    }
}

?>