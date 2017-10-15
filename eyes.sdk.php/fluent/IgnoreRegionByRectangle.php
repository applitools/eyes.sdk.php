<?php
namespace Applitools\fluent {
    use Applitools\EyesBase;
    use Applitools\Region;

    class IgnoreRegionByRectangle implements IGetRegion
    {
        /**
         * @var Region
         */
        private $region;

        public function __construct($region)
        {
            $this->region = $region;
        }

        /**
         * @param EyesBase $eyesBase
         * @return Region
         */
        function getRegion($eyesBase)
        {
            return $this->region;
        }
    }
}