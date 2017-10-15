<?php

namespace Applitools\Selenium\fluent;

use Applitools\Exceptions\EyesException;
use Applitools\EyesBase;
use Applitools\FloatingMatchSettings;
use Applitools\fluent\IGetFloatingRegion;
use Applitools\Selenium\Eyes;
use Facebook\WebDriver\WebDriverBy;

class FloatingRegionBySelector implements IGetFloatingRegion
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
     * @return FloatingMatchSettings
     * @throws EyesException
     */
    function getRegion(EyesBase $eyesBase)
    {
        if ($eyesBase instanceof Eyes) {
            $element = $eyesBase->getDriver()->findElement($this->selector);

            return new FloatingMatchSettings(
                $element->getLocation()->getX(),
                $element->getLocation()->getY(),
                $element->getSize()->getWidth(),
                $element->getSize()->getHeight(),
                $this->maxUpOffset, $this->maxDownOffset, $this->maxLeftOffset, $this->maxRightOffset);
        } else {
            throw new EyesException("\$eyesBase must be of type Applitools\\Selenium\\Eyes");
        }
    }
}