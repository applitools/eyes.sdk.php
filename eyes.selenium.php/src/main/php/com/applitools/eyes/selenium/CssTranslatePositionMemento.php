<?php
namespace Applitools;

/**
 * Encapsulates state for {@link CssTranslatePositionProvider} instances.
 */
class CssTranslatePositionMemento extends PositionMemento {
    private $transforms = array(); // Map<String, String>

    /**
     *
     * @param transforms The current transforms. The keys are the style keys
     *                   from which each of the transforms were taken.
     */
    public function __construct(/*Map<String, String> */$transforms) {
        $this->transforms = $transforms;
    }

    /**
     *
     * @return The current transforms. The keys are the style keys from
     * which each of the transforms were taken.
     */
    public function getTransform() {
        return $this->transforms;
    }
}
