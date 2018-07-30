<?php
/**
 * Applitools software
 */

namespace Applitools\Selenium\fluent;


use Applitools\EyesBase;
use Applitools\EyesScreenshot;
use Applitools\fluent\IGetRegions;
use Applitools\Region;
use Applitools\Selenium\Eyes;
use Facebook\WebDriver\WebDriverElement;

class RegionByElement implements IGetRegions
{
    /** @var WebDriverElement */
    private $element;

    /**
     * RegionByElement constructor.
     * @param WebDriverElement $element
     */
    public function __construct($element)
    {
        $this->element = $element;
    }

    /**
     * @param EyesBase $eyesBase
     * @param EyesScreenshot $screenshot
     * @return Region[]
     */
    function getRegions($eyesBase, $screenshot)
    {
        /** @var Region[] $results */
        $results = [];
        if ($eyesBase instanceof Eyes) {
            $location = $this->element->getLocation();
            $size = $this->element->getSize();
            $results[] = Region::CreateFromLTWH($location->getX(), $location->getY(), $size->getWidth(), $size->getHeight());
        }
        return $results;
    }
}