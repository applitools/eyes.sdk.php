<?php

namespace Applitools;

/**
 * Encapsulates match settings for the a session.
 */
class ImageMatchSettings
{
    /** @var null|string */
    private $matchLevel; //MatchLevel

    /** @var ExactMatchSettings */
    private $exact;

    /** @var bool */
    private $ignoreCaret;

    /** @var Region[] */
    private $ignoreRegions = [];

    /** @var FloatingMatchSettings[] */
    private $floatingMatchSettings = [];


    public function __construct($matchLevel = null, ExactMatchSettings $exact = null)
    {
        if (empty($matchLevel)) {
            $matchLevel = MatchLevel::STRICT;
        }
        $this->matchLevel = $matchLevel;
        $this->exact = $exact;
        $this->ignoreCaret = null;
    }

    /**
     *
     * @return string The "strictness" level of the match.
     */
    public function getMatchLevel()
    {
        return $this->matchLevel;
    }

    /**
     *
     * @param string $matchLevel The "strictness" level of the match.
     */
    public function setMatchLevel($matchLevel)
    {
        $this->matchLevel = $matchLevel;
    }

    /**
     *
     * @return ExactMatchSettings The parameters for the "Exact" match settings.
     */
    public function getExact()
    {
        return $this->exact;
    }

    /**
     *
     * @param ExactMatchSettings $exact The parameters for the "exact" match settings.
     */
    public function setExact(ExactMatchSettings $exact)
    {
        $this->exact = $exact;
    }

    public function __toString()
    {
        return "Match level: {$this->matchLevel}, Exact match settings: {$this->exact}";
    }

    /**
     * @return bool
     */
    public function isIgnoreCaret()
    {
        return $this->ignoreCaret;
    }

    /**
     * @param bool $ignoreCaret
     */
    public function setIgnoreCaret($ignoreCaret)
    {
        $this->ignoreCaret = $ignoreCaret;
    }

    /**
     * @return Region[]
     */
    public function getIgnoreRegions()
    {
        return $this->ignoreRegions;
    }

    /**
     * @param Region[] $ignoreRegions
     */
    public function setIgnoreRegions($ignoreRegions)
    {
        $this->ignoreRegions = $ignoreRegions;
    }

    /**
     * @return FloatingMatchSettings[]
     */
    public function getFloatingMatchSettings()
    {
        return $this->floatingMatchSettings;
    }

    /**
     * @param FloatingMatchSettings[] $floatingMatchSettings
     */
    public function setFloatingMatchSettings($floatingMatchSettings)
    {
        $this->floatingMatchSettings = $floatingMatchSettings;
    }

    public function getIgnoreRegionsAsFormattedArray()
    {
        $retVal = [];

        foreach ($this->ignoreRegions as $r) {
            $retVal[] = [
                "left" => $r->getLeft(),
                "top" => $r->getTop(),
                "width" => $r->getWidth(),
                "height" => $r->getHeight()
            ];
        }

        return $retVal;
    }

    public function getFloatingMatchSettingsAsFormattedArray()
    {
        $retVal = [];

        foreach ($this->floatingMatchSettings as $r) {
            $retVal[] = [
                "left" => $r->left,
                "top" => $r->top,
                "width" => $r->width,
                "height" => $r->height,
                "maxUpOffset" => $r->maxUpOffset,
                "maxDownOffset" => $r->maxDownOffset,
                "maxLeftOffset" => $r->maxLeftOffset,
                "maxRightOffset" => $r->maxRightOffset
            ];
        }

        return $retVal;
    }
}
