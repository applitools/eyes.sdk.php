<?php

namespace Applitools;

/**
 * Factory implementation which simply returns the scale provider it is given as an argument.
 */
class ScaleProviderIdentityFactory extends ScaleProviderFactory {

    private $scaleProvider; //ScaleProvider

    /**
     *
     * @param ScaleProvider $scaleProvider The {@link ScaleProvider}
     * @param PropertyHandler $scaleProviderHandler
     */
    public function __construct(ScaleProvider $scaleProvider, PropertyHandler $scaleProviderHandler) {
        parent::__construct($scaleProviderHandler);
        $this->scaleProvider = $scaleProvider;
    }

    protected function getScaleProviderImpl($imageToScaleWidth) {
        return $this->scaleProvider;
    }
}

?>