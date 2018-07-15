<?php
namespace Applitools\Selenium;

use Applitools\PropertyHandler;
use Applitools\RectangleSize;
use Applitools\ScaleMethod;
use Applitools\ScaleProviderFactory;

/**
 * Factory implementation for creating {@link ContextBasedScaleProvider} instances.
 */
class ContextBasedScaleProviderFactory extends ScaleProviderFactory {

    private $topLevelContextEntireSize; //RectangleSize
    private $viewportSize; //RectangleSize
    private $devicePixelRatio;

    /**
     *
     *
     * @param RectangleSize $topLevelContextEntireSize The total size of the top level
     *                                  context. E.g., for selenium this
     *                                  would be the document size of the top
     *                                  level frame.
     * @param RectangleSize $viewportSize The viewport size.
     * @param float $devicePixelRatio The device pixel ratio of the
     *                                  platform on which the application is
     *                                  running.
     * @param PropertyHandler $scaleProviderHandler
     */
    public function __construct(RectangleSize $topLevelContextEntireSize, RectangleSize $viewportSize,
                                            $devicePixelRatio, PropertyHandler $scaleProviderHandler) {
        parent::__construct($scaleProviderHandler);
        $this->topLevelContextEntireSize = $topLevelContextEntireSize;
        $this->viewportSize = $viewportSize;
        $this->devicePixelRatio = $devicePixelRatio;
    }

    protected function getScaleProviderImpl($imageToScaleWidth) {
        $scaleProvider = new ContextBasedScaleProvider($this->topLevelContextEntireSize, $this->viewportSize, $this->devicePixelRatio);
        $scaleProvider->updateScaleRatio($imageToScaleWidth);
        return $scaleProvider;
    }
}

?>