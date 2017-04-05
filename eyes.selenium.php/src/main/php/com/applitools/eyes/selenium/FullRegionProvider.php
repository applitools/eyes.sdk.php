<?php
namespace Applitools;

class FullRegionProvider extends RegionProvider{
    protected $element;
    public function __construct($element = null)
    {
        $this->element = $element;
    }

    /**
     *
     * @return A region with "as is" viewport coordinates.
     */
    public function getRegion()
    {//FIXME
        $p = $this->element->getLocation();
        $d = $this->element->getSize();
        return new Region($p->getX(), $p->getY(), $d->getWidth(),
                            $d->getHeight());
    }

    public function getCoordinatesType() {
        // If we're given a region, it is relative to the
        // frame's viewport.
        return CoordinatesType::CONTEXT_RELATIVE;
    }
}









