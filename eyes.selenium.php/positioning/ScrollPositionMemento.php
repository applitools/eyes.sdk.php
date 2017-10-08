<?php
namespace Applitools\Selenium;
use Applitools\Location;
use Applitools\PositionMemento;

/**
 * Encapsulates state for {@link ScrollPositionProvider} instances.
 */
class ScrollPositionMemento extends PositionMemento {
    private $position; //Location

    /**
     *
     * @param Location $l The current location to be saved.
     */
    public function __construct(Location $l) {
        $this->position = clone $l;
    }

    public function getX() {
        return $this->position->getX();
    }

    public function getY() {
        return $this->position->getY();
    }
}
