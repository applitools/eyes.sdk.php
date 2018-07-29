<?php

namespace Applitools\Selenium\fluent;

use Applitools\EyesBase;
use Applitools\EyesScreenshot;
use Applitools\fluent\IGetRegions;
use Applitools\Region;
use Applitools\Selenium\Eyes;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;

class RegionsBySelector implements IGetRegions
{
    /** @var WebDriverBy */
    private $selector;

    public function __construct(WebDriverBy $by)
    {
        $this->selector = $by;
    }

    /**
     * @param EyesBase $eyesBase
     * @param EyesScreenshot $screenshot
     * @return Region[]
     * @throws \Applitools\Exceptions\EyesException
     */
    function getRegions($eyesBase, $screenshot)
    {
        /** @var Region[] $results */
        $results = [];
        if ($eyesBase instanceof Eyes) {
            $elements = $eyesBase->getDriver()->findElements($this->selector);
            /** @var WebDriverElement $element */
            $element = null;
            foreach($elements as $element){
                $results[] = Region::CreateFromLTWH(
                    $element->getLocation()->getX(),
                    $element->getLocation()->getY(),
                    $element->getSize()->getWidth(),
                    $element->getSize()->getHeight());
            }
        }
        return $results;
    }
}