<?php

namespace Applitools\fluent {

    use Applitools\MatchLevel;
    use Applitools\Region;

    interface ICheckSettings
    {
        /**
         * Adds one or more ignore regions.
         * @param Region[] $regions One or more regions to ignore when validating the screenshot.
         * @return ICheckSettings This instance of the settings object.
         */
        function ignore(...$regions);

        /**
         * Adds one or more layout regions.
         * @param Region[] $regions One or more regions to match using the Layout method.
         * @return ICheckSettings This instance of the settings object.
         */
        function layoutRegions(...$regions);

//        /**
//         * Adds one or more exact regions.
//         * @param Region[] $regions One or more regions to match using the Exact method.
//         * @return ICheckSettings This instance of the settings object.
//         */
//        function exactRegions(...$regions);

        /**
         * Adds one or more strict regions.
         * @param Region[] $regions One or more regions to match using the Strict method.
         * @return ICheckSettings This instance of the settings object.
         */
        function strictRegions(...$regions);

        /**
         * Adds one or more content regions.
         * @param Region[] $regions One or more regions to match using the Content method.
         * @return ICheckSettings This instance of the settings object.
         */
        function contentRegions(...$regions);

        /**
         * Defines that the screenshot will contain the entire element or region, even if it's outside the view.
         * @return ICheckSettings This instance of the settings object.
         */
        function fully();

        /**
         * Adds a floating region. A floating region is a a region that can be placed within the boundaries of a bigger region.
         * @param int $maxOffset How much each of the content rectangles can move in any direction.
         * @param Region[] $regions One or more content rectangles.
         * @return ICheckSettings This instance of the settings object.
         */
        function floating($maxOffset, ...$regions);

        /**
         * Adds a floating region. A floating region is a a region that can be placed within the boundaries of a bigger region.
         * @param Region $region The content rectangle.
         * @param int $maxUpOffset How much the content can move up.
         * @param int $maxDownOffset How much the content can move down.
         * @param int $maxLeftOffset How much the content can move to the left.
         * @param int $maxRightOffset How much the content can move to the right.
         * @return ICheckSettings This instance of the settings object.
         */
        function addFloatingRegion(Region $region, $maxUpOffset, $maxDownOffset, $maxLeftOffset, $maxRightOffset);

        /**
         * Defines the timeout to use when acquiring and comparing screenshots.
         * @param int $timeoutMilliseconds The timeout to use in milliseconds.
         * @return ICheckSettings This instance of the settings object.
         */
        function timeout($timeoutMilliseconds);

        /**
         * Shortcut to set the match level to {@code MatchLevel.LAYOUT}.
         * @return ICheckSettings This instance of the settings object.
         */
        function layout();

        /**
         * Shortcut to set the match level to {@code MatchLevel.EXACT}.
         * @return ICheckSettings This instance of the settings object.
         */
        function exact();

        /**
         * Shortcut to set the match level to {@code MatchLevel.STRICT}.
         * @return ICheckSettings This instance of the settings object.
         */
        function strict();

        /**
         * Shortcut to set the match level to {@code MatchLevel.CONTENT}.
         * @return ICheckSettings This instance of the settings object.
         */
        function content();

        /**
         * Set the match level by which to compare the screenshot.
         * @param MatchLevel $matchLevel The match level to use.
         * @return ICheckSettings This instance of the settings object.
         */
        function matchLevel($matchLevel);

        /**
         * Defines if to detect and ignore a blinking caret in the screenshot.
         * @param boolean $ignoreCaret Whether or not to detect and ignore a blinking caret in the screenshot.
         * @return ICheckSettings This instance of the settings object.
         */
        function ignoreCaret($ignoreCaret);
    }
}