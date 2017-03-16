<?php

/**
 * Factory implementation which simply returns the scale provider it is given as an argument.
 */
class ScaleProviderIdentityFactory extends ScaleProviderFactory {

    private $scaleProvider; //ScaleProvider

    /**
     *
     * @param scaleProvider The {@link ScaleProvider}
     */
    public function __construct(ScaleProvider $scaleProvider,
                            /*PropertyHandler<ScaleProvider>*/ $scaleProviderHandler) { echo "SSSSSSSSSSSSSSSSS";
        parent::__construct($scaleProviderHandler);
        $this->scaleProvider = $scaleProvider;
    }

    protected function getScaleProviderImpl($imageToScaleWidth) {
        return $this->scaleProvider;
    }
}
