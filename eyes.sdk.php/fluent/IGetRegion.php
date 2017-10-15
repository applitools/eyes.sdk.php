<?php

namespace Applitools\fluent {

    use Applitools\EyesBase;
    use Applitools\Region;

    interface IGetRegion
    {
        /**
         * @param EyesBase $eyesBase
         * @return Region
         */
        function getRegion($eyesBase);
    }
}