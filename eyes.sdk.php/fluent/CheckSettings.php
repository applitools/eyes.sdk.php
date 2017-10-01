<?php

namespace Applitools\fluent {

    use Applitools\MatchLevel;
    use Applitools\Region;

    class CheckSettings implements ICheckSettings, ICheckSettingsInternal
    {

        private $targetRegion;
        private $matchLevel;
        private $ignoreCaret;
        private $stitchContent = false;
        private $timeout = -1;

        /**
         * @var IGetRegion[]
         */
        private $ignoreRegions = [];

        /**
         * @var IGetFloatingRegion[]
         */
        private $floatingRegions = [];

        /**
         * Adds one or more ignore regions.
         * @param Region[] $regions One or more regions to ignore when validating the screenshot.
         * @return ICheckSettings This instance of the settings object.
         */
        function ignore(...$regions)
        {
            foreach ($regions as $region) {
                $this->ignoreRegions[] = new IgnoreRegionByRectangle($region);
            }
            return $this;
        }

        /**
         * Defines that the screenshot will contain the entire element or region, even if it's outside the view.
         * @return ICheckSettings This instance of the settings object.
         */
        function fully()
        {
            $this->stitchContent = true;
            return $this;
        }

        /**
         * Adds a floating region. A floating region is a a region that can be placed within the boundaries of a bigger region.
         * @param int $maxOffset How much each of the content rectangles can move in any direction.
         * @param Region[] $regions One or more content rectangles.
         * @return ICheckSettings This instance of the settings object.
         */
        function floating($maxOffset, ...$regions)
        {
            foreach ($regions as $r) {
                $this->addFloatingRegion($r, $maxOffset, $maxOffset, $maxOffset, $maxOffset);
            }
            return $this;
        }

        /**
         * Adds a floating region. A floating region is a a region that can be placed within the boundaries of a bigger region.
         * @param Region $region The content rectangle.
         * @param int $maxUpOffset How much the content can move up.
         * @param int $maxDownOffset How much the content can move down.
         * @param int $maxLeftOffset How much the content can move to the left.
         * @param int $maxRightOffset How much the content can move to the right.
         * @return ICheckSettings This instance of the settings object.
         */
        function addFloatingRegion($region, $maxUpOffset, $maxDownOffset, $maxLeftOffset, $maxRightOffset)
        {
            $this->floatingRegions[]=
                new FloatingRegionByRectangle(
                    new Region(
                        $region->getLeft(),
                        $region->getTop(),
                        $region->getLeft() + $region->getWidth(),
                        $region->getTop() + $region->getHeight()
                    ), $maxUpOffset, $maxDownOffset, $maxLeftOffset, $maxRightOffset);

            return $this;
        }

        /**
         * Defines the timeout to use when acquiring and comparing screenshots.
         * @param int $timeoutMilliseconds The timeout to use in milliseconds.
         * @return ICheckSettings This instance of the settings object.
         */
        function timeout($timeoutMilliseconds)
        {
            $this->timeout = $timeoutMilliseconds;
            return $this;
        }

        /**
         * Shortcut to set the match level to {@code MatchLevel.LAYOUT}.
         * @return ICheckSettings This instance of the settings object.
         */
        function layout()
        {
            $this->matchLevel = MatchLevel::LAYOUT;
            return $this;
        }

        /**
         * Shortcut to set the match level to {@code MatchLevel.EXACT}.
         * @return ICheckSettings This instance of the settings object.
         */
        function exact()
        {
            $this->matchLevel = MatchLevel::EXACT;
            return $this;
        }

        /**
         * Shortcut to set the match level to {@code MatchLevel.STRICT}.
         * @return ICheckSettings This instance of the settings object.
         */
        function strict()
        {
            $this->matchLevel = MatchLevel::STRICT;
            return $this;
        }

        /**
         * Shortcut to set the match level to {@code MatchLevel.CONTENT}.
         * @return ICheckSettings This instance of the settings object.
         */
        function content()
        {
            $this->matchLevel = MatchLevel::CONTENT;
            return $this;
        }

        /**
         * Set the match level by which to compare the screenshot.
         * @param MatchLevel $matchLevel The match level to use.
         * @return ICheckSettings This instance of the settings object.
         */
        function matchLevel($matchLevel)
        {
            $this->matchLevel = $matchLevel;
            return $this;
        }

        /**
         * Defines if to detect and ignore a blinking caret in the screenshot.
         * @param boolean $ignoreCaret Whether or not to detect and ignore a blinking caret in the screenshot.
         * @return ICheckSettings This instance of the settings object.
         */
        function ignoreCaret($ignoreCaret)
        {
            $this->ignoreCaret = $ignoreCaret;
            return $this;
        }

        /**
         * @return Region
         */
        function getTargetRegion()
        {
            return $this->targetRegion;
        }

        /**
         * @return int
         */
        function getTimeout()
        {
            return $this->timeout;
        }

        /**
         * @return bool
         */
        function getStitchContent()
        {
            return $this->stitchContent;
        }

        /**
         * @return MatchLevel
         */
        function getMatchLevel()
        {
            return $this->matchLevel;
        }

        /**
         * @return IGetRegion[]
         */
        function getIgnoreRegions()
        {
            return $this->ignoreRegions;
        }

        /**
         * @return IGetFloatingRegion[]
         */
        function getFloatingRegions()
        {
            return $this->floatingRegions;
        }

        /**
         * @return bool
         */
        function getIgnoreCaret()
        {
            return $this->ignoreCaret;
        }
    }
}