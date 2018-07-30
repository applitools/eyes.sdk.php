<?php

namespace Applitools\fluent {

    use Applitools\EyesBase;
    use Applitools\EyesScreenshot;
    use Applitools\FloatingMatchSettings;

    interface IGetFloatingRegions
    {
        /**
         * @param EyesBase $eyesBase
         * @param EyesScreenshot $screenshot
         * @return FloatingMatchSettings[]
         */
        function getRegions (EyesBase $eyesBase, EyesScreenshot $screenshot);
    }
}