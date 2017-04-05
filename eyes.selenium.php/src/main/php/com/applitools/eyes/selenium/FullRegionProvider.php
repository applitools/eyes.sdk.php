<?php
namespace Applitools;

use Facebook\WebDriver\WebDriverElement;

class FullRegionProvider extends RegionProvider{

    /**
     * @var WebDriverElement
     */
    protected $element;

    /**
     * FullRegionProvider constructor.
     * @param WebDriverElement $element
     */
    public function __construct(WebDriverElement $element = null)
    {
        parent::__construct();
        $this->element = $element;
    }

    /**
     *
     * @return Region A region with "as is" viewport coordinates.
     */
    public function getRegion()
    {//FIXME
        $p = $this->element->getLocation();
        $d = $this->element->getSize();
        return new Region($p->getX(), $p->getY(), $d->getWidth(),
                            $d->getHeight());
    }

    /**
     * @return string
     */
    public function getCoordinatesType() {
        // If we're given a region, it is relative to the
        // frame's viewport.
        return CoordinatesType::CONTEXT_RELATIVE;
    }
}









