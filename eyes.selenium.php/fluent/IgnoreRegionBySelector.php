<?php

namespace Applitools\fluent;

use Applitools\Eyes;
use Applitools\EyesBase;
use Applitools\Region;
use Facebook\WebDriver\WebDriverBy;

class IgnoreRegionBySelector implements IGetRegion
{
    /** @var WebDriverBy */
    private $selector;

    public function __construct(WebDriverBy $by)
    {
        $this->selector = $by;
    }

    /**
     * @param EyesBase $eyesBase
     * @return Region
     */
    function getRegion($eyesBase)
    {
        if ($eyesBase instanceof Eyes) {
            $element = $eyesBase->getDriver()->findElement($this->selector);
            return new Region(
                $element->getLocation()->getX(),
                $element->getLocation()->getY(),
                $element->getSize()->getWidth(),
                $element->getSize()->getHeight());
        }
    }
}