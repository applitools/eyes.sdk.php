<?php
namespace Applitools;

/**
 * Encapsulates state for {@link ScrollPositionProvider} instances.
 */
class ScrollPositionMemento extends PositionMemento {
    private $position; //Location

    /**
     *
     * @param l The current location to be saved.
     */
    public function __construct(Location $l) {
        $this->position = new Location(null, null, $l);
    }

    public function getX() {
        return $this->position->getX();
    }

    public function getY() {
        return $this->position->getY();
    }
}
