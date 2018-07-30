<?php
namespace Applitools\fluent {
    use Applitools\EyesBase;
    use Applitools\EyesScreenshot;
    use Applitools\Region;

    class RegionByRectangle implements IGetRegions
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
         * @param EyesScreenshot $screenshot
         * @return Region[]
         */
        function getRegions($eyesBase, $screenshot)
        {
            return array($this->region);
        }
    }
}