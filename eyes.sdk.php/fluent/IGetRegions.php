<?php

namespace Applitools\fluent {

    use Applitools\EyesBase;
    use Applitools\EyesScreenshot;
    use Applitools\Region;

    interface IGetRegions
    {
        /**
         * @param EyesBase $eyesBase
         * @param EyesScreenshot $screenshot
         * @return Region[]
         */
        function getRegions($eyesBase, $screenshot);
    }
}