<?php
/**
 * Abstraction for instantiating scale providers.
 */
abstract class ScaleProviderFactory {

    protected $scaleProviderHandler; //PropertyHandler<ScaleProvider>

    /**
     *
     * @param scaleProviderHandler A handler to update once a {@link ScaleProvider} instance is created.
     */
    public function __construct(/*PropertyHandler<ScaleProvider>*/ $scaleProviderHandler) {
        $this->scaleProviderHandler = $scaleProviderHandler;
    }

    /**
     * The main API for this factory.
     *
     * @param imageToScaleWidth The width of the image to scale. This parameter CAN be by class implementing
     *                          the factory, but this is not mandatory.
     * @return A {@link ScaleProvider} instance.
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
     * @param imageToScaleWidth The width of the image to scale. This parameter CAN be by class implementing
     *                          the factory, but this is not mandatory.
     * @return The scale provider to be used.
     */
    protected abstract function getScaleProviderImpl($imageToScaleWidth);
}
