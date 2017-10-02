<?php

namespace Applitools\fluent;

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
     * @param Region $region
     * @return $this
     */
    public function region(Region $region)
    {
        parent::updateTargetRegion($region);
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
    public function ignore(...$regions){
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