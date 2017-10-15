<?php

namespace Applitools\Selenium\fluent;

use Applitools\Exceptions\EyesException;
use Applitools\fluent\CheckSettings;
use Applitools\fluent\IgnoreRegionByRectangle;
use Applitools\Region;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;

class SeleniumCheckSettings extends CheckSettings implements ISeleniumCheckTarget
{

    /** @var WebDriverBy */
    private $targetSelector;

    /** @var WebDriverElement */
    private $targetElement;

    /** @var FrameLocator */
    private $frameChain = [];


    public function __construct($argument = null)
    {
        if (isset($argument) && $argument != null) {
            if ($argument instanceof Region) {
                parent::__construct($argument);
            } else if ($argument instanceof WebDriverBy) {
                $this->targetSelector = $argument;
            } else if ($argument instanceof WebDriverElement) {
                $this->targetElement = $argument;
            }
        }
    }

    /**
     * @param WebDriverBy $region
     * @param $maxUpOffset
     * @param $maxDownOffset
     * @param $maxLeftOffset
     * @param $maxRightOffset
     * @return $this
     */
    public function addFloatingRegionBySelector(WebDriverBy $region, $maxUpOffset, $maxDownOffset, $maxLeftOffset, $maxRightOffset)
    {
        $this->floatingRegions[] = new FloatingRegionBySelector($region, $maxUpOffset, $maxDownOffset, $maxLeftOffset, $maxRightOffset);

        return $this;
    }

    /**
     * @param WebDriverBy $by
     * @return $this
     */
    public function frameBySelector(WebDriverBy $by)
    {
        $fl = new FrameLocator();
        $fl->setFrameSelector($by);
        $this->frameChain[] = $fl;
        return $this;
    }

    /**
     * @param string $nameOrId
     * @return $this
     */
    public function frameByNameOrId($nameOrId)
    {
        $fl = new FrameLocator();
        $fl->setFrameNameOrId($nameOrId);
        $this->frameChain[] = $fl;
        return $this;
    }

    /**
     * @param int $index
     * @return $this
     */
    public function frameByIndex($index)
    {
        $fl = new FrameLocator();
        $fl->setFrameIndex($index);
        $this->frameChain[] = $fl;
        return $this;
    }

    /**
     * @param WebDriverBy|string|int $frame
     * @return SeleniumCheckSettings
     * @throws EyesException
     */
    public function frame($frame)
    {
        /** @var FrameLocator */
        $fl = new FrameLocator();

        if ($frame instanceof WebDriverBy) {
            $fl->setFrameSelector($frame);
        } else if (is_string($frame)) {
            $fl->setFrameNameOrId($frame);
        } else if (is_int($frame)) {
            $fl->setFrameIndex($frame);
        } else {
            throw new EyesException("frame locator not supported");
        }

        $this->frameChain[] = $fl;
        return $this;
    }

    /**
     * @param Region|WebDriverBy $region
     * @return $this
     */
    public function region($region)
    {
        if ($region instanceof Region) {
            parent::updateTargetRegion($region);
        } else {
            $this->targetSelector = $region;
        }
        return $this;
    }

    /**
     * @param WebDriverBy $by
     * @return $this
     */
    public function regionBySelector(WebDriverBy $by)
    {
        $this->targetSelector = $by;
        return $this;
    }

    /**
     * @param WebDriverBy[] $regionSelectors
     * @return $this;
     */
    public function ignoreBySelector(...$regionSelectors)
    {
        foreach ($regionSelectors as $selector) {
            parent::ignore(new IgnoreRegionBySelector($selector));
        }

        return $this;
    }

    /**
     * @param Region[] $regions
     * @return $this;
     */
    public function ignore(...$regions)
    {
        foreach ($regions as $region) {
            parent::ignore(new IgnoreRegionByRectangle($region));
        }

        return $this;
    }

    /**
     * @return WebDriverBy The target selector.
     */
    function getTargetSelector()
    {
        return $this->targetSelector;
    }

    /**
     * @return WebDriverElement
     */
    function getTargetElement()
    {
        return $this->targetElement;
    }

    /**
     * @return FrameLocator
     */
    function getFrameChain()
    {
        return $this->frameChain;
    }

    public function __toString()
    {
        return __CLASS__ . " - timeout: " . $this->getTimeout();
    }
}