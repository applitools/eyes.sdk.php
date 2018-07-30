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
         * @return IGetRegions[]
         */
        function getIgnoreRegions();

        /**
         * @return IGetFloatingRegions[]
         */
        function getFloatingRegions();

        /**
         * @return IGetRegions[]
         */
        function getLayoutRegions();

        /**
         * @return IGetRegions[]
         */
        function getStrictRegions();

//        /**
//         * @return IGetRegions[]
//         */
//        function getExactRegions();

        /**
         * @return IGetRegions[]
         */
        function getContentRegions();

        /**
         * @return bool
         */
        function getIgnoreCaret();

    }
}