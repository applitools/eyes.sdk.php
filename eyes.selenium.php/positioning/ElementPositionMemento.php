<?php
namespace Applitools\Selenium;
use Applitools\Location;
use Applitools\PositionMemento;

/**
 * Encapsulates state for {@link ElementPositionProvider} instances.
 */
class ElementPositionMemento extends PositionMemento {
    private $position; //Location

    /**
     *
     * @param $l Location The current location to be saved.
     */
    public function __construct(Location $l) {
        $this->position = new Location($l->getX(),$l->getY());
    }

    public function getX() {
        return $this->position->getX();
    }

    public function getY() {
        return $this->position->getY();
    }
}
