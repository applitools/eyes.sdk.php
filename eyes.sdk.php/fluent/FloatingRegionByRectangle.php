<?php

namespace Applitools\fluent {

    use Applitools\EyesBase;
    use Applitools\FloatingMatchSettings;
    use Applitools\Region;

    class FloatingRegionByRectangle implements IGetFloatingRegion
    {
        /** @var Region */
        private $rect;

        /** @var int */
        private $maxUpOffset;

        /** @var int */
        private $maxDownOffset;

        /** @var int */
        private $maxLeftOffset;

        /** @var int */
        private $maxRightOffset;

        /**
         * FloatingRegionByRectangle constructor.
         * @param Region $rect
         * @param int $maxUpOffset
         * @param int $maxDownOffset
         * @param int $maxLeftOffset
         * @param int $maxRightOffset
         */
        public function __construct($rect, $maxUpOffset, $maxDownOffset, $maxLeftOffset, $maxRightOffset)
        {
            $this->rect = $rect;
            $this->maxUpOffset = $maxUpOffset;
            $this->maxDownOffset = $maxDownOffset;
            $this->maxLeftOffset = $maxLeftOffset;
            $this->maxRightOffset = $maxRightOffset;
        }

        /**
         * @param EyesBase $eyesBase
         * @return FloatingMatchSettings
         */
        function getRegion($eyesBase)
        {
            return new FloatingMatchSettings(
                $this->rect->getLeft(), $this->rect->getTop(), $this->rect->getWidth(), $this->rect->getHeight(),
                $this->maxUpOffset, $this->maxDownOffset, $this->maxLeftOffset, $this->maxRightOffset);
        }
    }
}