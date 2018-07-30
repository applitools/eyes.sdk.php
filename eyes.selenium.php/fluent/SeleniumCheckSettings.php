<?php

namespace Applitools\Selenium\fluent;

use Applitools\Exceptions\EyesException;
use Applitools\fluent\CheckSettings;
use Applitools\fluent\ICheckSettings;
use Applitools\fluent\IGetRegions;
use Applitools\fluent\RegionByRectangle;
use Applitools\Region;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;

class SeleniumCheckSettings extends CheckSettings implements ISeleniumCheckTarget
{

    /** @var WebDriverBy */
    private $targetSelector;

    /** @var WebDriverElement */
    private $targetElement;

    /** @var FrameLocator[] */
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
     * @return SeleniumCheckSettings An updated copy of the settings object.
     */
    public function addFloatingRegionBySelector(WebDriverBy $region, $maxUpOffset, $maxDownOffset, $maxLeftOffset, $maxRightOffset)
    {
        $this->floatingRegions[] = new FloatingRegionsBySelector($region, $maxUpOffset, $maxDownOffset, $maxLeftOffset, $maxRightOffset);

        return clone $this;
    }

    /**
     * @param WebDriverBy $by
     * @return SeleniumCheckSettings An updated copy of the settings object.
     */
    public function frameBySelector(WebDriverBy $by)
    {
        $fl = new FrameLocator();
        $fl->setFrameSelector($by);
        $this->frameChain[] = $fl;
        return clone $this;
    }

    /**
     * @param string $nameOrId
     * @return SeleniumCheckSettings An updated copy of the settings object.
     */
    public function frameByNameOrId($nameOrId)
    {
        $fl = new FrameLocator();
        $fl->setFrameNameOrId($nameOrId);
        $this->frameChain[] = $fl;
        return clone $this;
    }

    /**
     * @param int $index
     * @return SeleniumCheckSettings An updated copy of the settings object.
     */
    public function frameByIndex($index)
    {
        $fl = new FrameLocator();
        $fl->setFrameIndex($index);
        $this->frameChain[] = $fl;
        return clone $this;
    }

    /**
     * @param WebDriverBy|string|int $frame
     * @return SeleniumCheckSettings An updated copy of the settings object.
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
        return clone $this;
    }

    /**
     * @param Region|WebDriverBy $region
     * @return SeleniumCheckSettings An updated copy of the settings object.
     */
    public function region($region)
    {
        if ($region instanceof Region) {
            parent::updateTargetRegion($region);
        } else {
            $this->targetSelector = $region;
        }
        return clone $this;
    }

    /**
     * @param WebDriverBy $by
     * @return SeleniumCheckSettings An updated copy of the settings object.
     */
    public function regionBySelector(WebDriverBy $by)
    {
        $this->targetSelector = $by;
        return clone $this;
    }

    /**
     * @param WebDriverBy[] $regionSelectors
     * @return SeleniumCheckSettings An updated copy of the settings object.
     */
    public function ignoreBySelector(...$regionSelectors)
    {
        foreach ($regionSelectors as $selector) {
            parent::ignore(new RegionsBySelector($selector));
        }

        return clone $this;
    }

    /**
     * @param $region
     * @return IGetRegions|RegionByRectangle|RegionByElement|RegionsBySelector
     */
    private function getRegionsProvider($region)
    {
        /** @var IGetRegions $regionProvider */
        $regionProvider = null;
        if ($region instanceof Region) {
            $regionProvider = new RegionByRectangle($region);
        } else if ($region instanceof WebDriverBy) {
            $regionProvider = new RegionsBySelector($region);
        } else if ($region instanceof WebDriverElement) {
            $regionProvider = new RegionByElement($region);
        }
        return $regionProvider;
    }

    /**
     * @param array $regions
     * @return SeleniumCheckSettings An updated copy of the settings object.
     */
    public function ignore(...$regions)
    {
        foreach ($regions as $region) {
            $regionsProvider = $this->getRegionsProvider($region);
            if ($regionsProvider != null) {
                $this->ignoreRegions[] = $regionsProvider;
            }
        }

        return clone $this;
    }

    /**
     * @param array $regions
     * @return SeleniumCheckSettings An updated copy of the settings object.
     */
    public function layoutRegions(...$regions)
    {
        foreach ($regions as $region) {
            $regionsProvider = $this->getRegionsProvider($region);
            if ($regionsProvider != null) {
                $this->layoutRegions[] = $regionsProvider;
            }
        }

        return clone $this;
    }

//    /**
//     * @param array $regions
//     * @return SeleniumCheckSettings An updated copy of the settings object.
//     */
//    public function exactRegions(...$regions)
//    {
//        foreach ($regions as $region) {
//            $regionsProvider = $this->getRegionsProvider($region);
//            if ($regionsProvider != null) {
//                $this->exactRegions[] = $regionsProvider;
//            }
//        }
//
//        return clone $this;
//    }

    /**
     * @param array $regions
     * @return SeleniumCheckSettings An updated copy of the settings object.
     */
    public function strictRegions(...$regions)
    {
        foreach ($regions as $region) {
            $regionsProvider = $this->getRegionsProvider($region);
            if ($regionsProvider != null) {
                $this->strictRegions[] = $regionsProvider;
            }
        }

        return clone $this;
    }

    /**
     * @param array $regions
     * @return SeleniumCheckSettings An updated copy of the settings object.
     */
    public function contentRegions(...$regions)
    {
        foreach ($regions as $region) {
            $regionsProvider = $this->getRegionsProvider($region);
            if ($regionsProvider != null) {
                $this->contentRegions[] = $regionsProvider;
            }
        }

        return clone $this;
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
     * @return FrameLocator[]
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