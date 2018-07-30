<?php

namespace Applitools\Selenium\fluent;

use Applitools\Exceptions\EyesException;
use Applitools\EyesBase;
use Applitools\EyesScreenshot;
use Applitools\FloatingMatchSettings;
use Applitools\fluent\IGetFloatingRegions;
use Applitools\Selenium\Eyes;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;

class FloatingRegionsBySelector implements IGetFloatingRegions
{

    /** @var WebDriverBy */
    private $selector;

    /** @var  int */
    private $maxUpOffset;

    /** @var  int */
    private $maxDownOffset;

    /** @var  int */
    private $maxLeftOffset;

    /** @var  int */
    private $maxRightOffset;

    /**
     * FloatingRegionBySelector constructor.
     * @param WebDriverBy $regionSelector
     * @param int $maxUpOffset
     * @param int $maxDownOffset
     * @param int $maxLeftOffset
     * @param int $maxRightOffset
     */
    public function __construct(WebDriverBy $regionSelector, $maxUpOffset, $maxDownOffset, $maxLeftOffset, $maxRightOffset)
    {
        $this->selector = $regionSelector;
        $this->maxUpOffset = $maxDownOffset;
        $this->maxDownOffset = $maxDownOffset;
        $this->maxLeftOffset = $maxLeftOffset;
        $this->maxRightOffset = $maxRightOffset;
    }

    /**
     * @param EyesBase $eyesBase
     * @param EyesScreenshot $screenshot
     * @return FloatingMatchSettings[]
     * @throws EyesException
     */
    function getRegions(EyesBase $eyesBase, EyesScreenshot $screenshot)
    {
        $result = [];
        if ($eyesBase instanceof Eyes) {

            $elements = $eyesBase->getDriver()->findElements($this->selector);

            /** @var WebDriverElement $element */
            $element = null;

            foreach($elements as $element) {
                $result[] = new FloatingMatchSettings(
                    $element->getLocation()->getX(),
                    $element->getLocation()->getY(),
                    $element->getSize()->getWidth(),
                    $element->getSize()->getHeight(),
                    $this->maxUpOffset, $this->maxDownOffset, $this->maxLeftOffset, $this->maxRightOffset);
            }
        } else {
            throw new EyesException("\$eyesBase must be of type Applitools\\Selenium\\Eyes");
        }

        return $result;
    }
}