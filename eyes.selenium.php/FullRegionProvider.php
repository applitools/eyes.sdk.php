<?php
namespace Applitools\Selenium;

use Applitools\CoordinatesType;
use Applitools\Region;
use Applitools\RegionProvider;

class FullRegionProvider extends RegionProvider{

    /**
     * @var EyesRemoteWebElement
     */
    protected $element;

    /**
     * FullRegionProvider constructor.
     * @param EyesRemoteWebElement $element
     */
    public function __construct(EyesRemoteWebElement $element = null)
    {
        parent::__construct();
        $this->element = $element;
    }

    /**
     *
     * @return Region A region with "as is" viewport coordinates.
     */
    public function getRegion()
    {
        $elementRegion = $this->element->getClientAreaBounds();
        return $elementRegion;
    }

    /**
     * @return string
     */
    public function getCoordinatesType() {
        // If we're given a region, it is relative to the frame's viewport.
        return CoordinatesType::CONTEXT_RELATIVE;
    }
}









