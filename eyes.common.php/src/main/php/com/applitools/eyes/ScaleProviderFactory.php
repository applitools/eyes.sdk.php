<?php

namespace Applitools;

/**
 * Abstraction for instantiating scale providers.
 */
abstract class ScaleProviderFactory {

    /** @var PropertyHandler */
    protected $scaleProviderHandler;

    /**
     *
     * @param PropertyHandler $scaleProviderHandler A handler to update once a {@link ScaleProvider} instance is created.
     */
    public function __construct(PropertyHandler $scaleProviderHandler) {
        $this->scaleProviderHandler = $scaleProviderHandler;
    }

    /**
     * The main API for this factory.
     *
     * @param int $imageToScaleWidth The width of the image to scale. This parameter CAN be by class implementing
     *                          the factory, but this is not mandatory.
     * @return ScaleProvider A {@link ScaleProvider} instance.
     */
    public function getScaleProvider($imageToScaleWidth) {
        $scaleProvider = $this->getScaleProviderImpl($imageToScaleWidth);
        $this->scaleProviderHandler->set($scaleProvider);

        return $scaleProvider;
    }

    /**
     * The implementation of getting/creating the scale provider, should be implemented by child classes.
     *
     *
     * @param int $imageToScaleWidth The width of the image to scale. This parameter CAN be by class implementing
     *                          the factory, but this is not mandatory.
     * @return ScaleProvider The scale provider to be used.
     */
    protected abstract function getScaleProviderImpl($imageToScaleWidth);
}

?>