<?php
namespace Applitools\Selenium;

use Applitools\PositionMemento;

/**
 * Encapsulates state for {@link CssTranslatePositionProvider} instances.
 */
class CssTranslatePositionMemento extends PositionMemento {

    private $transforms = array();

    /**
     *
     * @param array $transforms The current transforms. The keys are the style keys from which each of the transforms were taken.
     */
    public function __construct($transforms) {
        $this->transforms = $transforms;
    }

    /**
     *
     * @return array The current transforms. The keys are the style keys from which each of the transforms were taken.
     */
    public function getTransform() {
        return $this->transforms;
    }
}
