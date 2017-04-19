<?php

namespace Applitools;

/**
 * A base class for position related memento instances. This is intentionally
 * not an interface, since the mementos might vary in their interfaces.
 */
class PositionMemento { //FIXME should be Abstract
    private $transforms = array();

    public function getTransform() { //FIXME copy from CssTranslatePositionMemento
        return $this->transforms;
    }
}

?>