<?php

namespace Applitools\fluent {

    use Applitools\EyesBase;
    use Applitools\FloatingMatchSettings;

    interface IGetFloatingRegion
    {
        /**
         * @param EyesBase $eyesBase
         * @return FloatingMatchSettings
         */
        function getRegion (EyesBase $eyesBase);
    }
}