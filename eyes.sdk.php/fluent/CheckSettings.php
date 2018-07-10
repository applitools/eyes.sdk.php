<?php

namespace Applitools\fluent {

    use Applitools\MatchLevel;
    use Applitools\Region;
    use Applitools\Selenium\fluent\IgnoreRegionBySelector;
    use Facebook\WebDriver\WebDriverBy;

    class CheckSettings implements ICheckSettings, ICheckSettingsInternal
    {

        /** @var Region */
        private $targetRegion;

        /** @var MatchLevel */
        private $matchLevel;

        /** @var bool */
        private $ignoreCaret;

        /** @var bool */
        private $stitchContent = false;

        /** @var int */
        private $timeout = -1;

        /**
         * @var IGetRegion[]
         */
        protected $ignoreRegions = [];

        /**
         * @var IGetFloatingRegion[]
         */
        protected $floatingRegions = [];

        /**
         * CheckSettings constructor.
         * @param Region $region
         */
        public function __construct(Region $region = null)
        {
            $this->targetRegion = $region;
        }

        /**
         * Adds one or more ignore regions.
         * @param Region[] $regions One or more regions to ignore when validating the screenshot.
         * @return ICheckSettings This instance of the settings object.
         */
        public function ignore(...$regions)
        {
            foreach ($regions as $region) {
                if ($region instanceof Region) {
                    $this->ignoreRegions[] = new IgnoreRegionByRectangle($region);
                }
            }
            return $this;
        }

        /**
         * Defines that the screenshot will contain the entire element or region, even if it's outside the view.
         * @param bool $stitch
         * @return ICheckSettings This instance of the settings object.
         */
        public function fully($stitch = true)
        {
            $this->stitchContent = $stitch;
            return $this;
        }

        /**
         * Adds a floating region. A floating region is a a region that can be placed within the boundaries of a bigger region.
         * @param int $maxOffset How much each of the content rectangles can move in any direction.
         * @param Region[] $regions One or more content rectangles.
         * @return ICheckSettings This instance of the settings object.
         */
        public function floating($maxOffset, ...$regions)
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
        public function addFloatingRegion(Region $region, $maxUpOffset, $maxDownOffset, $maxLeftOffset, $maxRightOffset)
        {
            $this->floatingRegions[] =
                new FloatingRegionByRectangle(
                    Region::CreateFromLTWH(
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
        public function timeout($timeoutMilliseconds)
        {
            $this->timeout = $timeoutMilliseconds / 1000.0;
            return $this;
        }

        /**
         * Shortcut to set the match level to {@code MatchLevel.LAYOUT}.
         * @return ICheckSettings This instance of the settings object.
         */
        public function layout()
        {
            $this->matchLevel = MatchLevel::LAYOUT;
            return $this;
        }

        /**
         * Shortcut to set the match level to {@code MatchLevel.EXACT}.
         * @return ICheckSettings This instance of the settings object.
         */
        public function exact()
        {
            $this->matchLevel = MatchLevel::EXACT;
            return $this;
        }

        /**
         * Shortcut to set the match level to {@code MatchLevel.STRICT}.
         * @return ICheckSettings This instance of the settings object.
         */
        public function strict()
        {
            $this->matchLevel = MatchLevel::STRICT;
            return $this;
        }

        /**
         * Shortcut to set the match level to {@code MatchLevel.CONTENT}.
         * @return ICheckSettings This instance of the settings object.
         */
        public function content()
        {
            $this->matchLevel = MatchLevel::CONTENT;
            return $this;
        }

        /**
         * Set the match level by which to compare the screenshot.
         * @param MatchLevel $matchLevel The match level to use.
         * @return ICheckSettings This instance of the settings object.
         */
        public function matchLevel($matchLevel)
        {
            $this->matchLevel = $matchLevel;
            return $this;
        }

        /**
         * Defines if to detect and ignore a blinking caret in the screenshot.
         * @param boolean $ignoreCaret Whether or not to detect and ignore a blinking caret in the screenshot.
         * @return ICheckSettings This instance of the settings object.
         */
        public function ignoreCaret($ignoreCaret)
        {
            $this->ignoreCaret = $ignoreCaret;
            return $this;
        }

        /**
         * @return Region
         */
        public function getTargetRegion()
        {
            return $this->targetRegion;
        }

        /**
         * @return int
         */
        public function getTimeout()
        {
            return $this->timeout;
        }

        /**
         * @return bool
         */
        public function getStitchContent()
        {
            return $this->stitchContent;
        }

        /**
         * @return MatchLevel
         */
        public function getMatchLevel()
        {
            return $this->matchLevel;
        }

        /**
         * @return IGetRegion[]
         */
        public function getIgnoreRegions()
        {
            return $this->ignoreRegions;
        }

        /**
         * @return IGetFloatingRegion[]
         */
        public function getFloatingRegions()
        {
            return $this->floatingRegions;
        }

        /**
         * @return bool
         */
        public function getIgnoreCaret()
        {
            return $this->ignoreCaret;
        }

        /**
         * @param Region $region
         */
        protected function updateTargetRegion(Region $region)
        {
            $this->targetRegion = $region;
        }

        public function __toString()
        {
            return __CLASS__ . " - timeout: " . $this->getTimeout();
        }
    }
}