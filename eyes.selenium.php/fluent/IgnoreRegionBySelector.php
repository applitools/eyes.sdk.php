<?php

namespace Applitools\Selenium\fluent;

use Applitools\EyesBase;
use Applitools\fluent\IGetRegion;
use Applitools\Region;
use Applitools\Selenium\Eyes;
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
            return Region::CreateFromLTWH(
                $element->getLocation()->getX(),
                $element->getLocation()->getY(),
                $element->getSize()->getWidth(),
                $element->getSize()->getHeight());
        }
    }
}