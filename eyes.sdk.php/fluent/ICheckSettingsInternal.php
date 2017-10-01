<?php

namespace Applitools\fluent {

    use Applitools\MatchLevel;
    use Applitools\Region;

    interface ICheckSettingsInternal
    {
        /**
         * @return Region
         */
        function getTargetRegion();

        /**
         * @return int
         */
        function getTimeout();

        /**
         * @return bool
         */
        function getStitchContent();

        /**
         * @return MatchLevel
         */
        function getMatchLevel();

        /**
         * @return IGetRegion[]
         */
        function getIgnoreRegions();

        /**
         * @return IGetFloatingRegion[]
         */
        function getFloatingRegions();

        /**
         * @return bool
         */
        function getIgnoreCaret();

    }
}