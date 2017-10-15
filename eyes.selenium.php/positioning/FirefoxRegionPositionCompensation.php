<?php
/**
 * Applitools software
 */

namespace Applitools\Selenium;

use Applitools\Logger;
use Applitools\Region;

class FirefoxRegionPositionCompensation implements IRegionPositionCompensation
{
    /** @var Eyes */
    private $eyes;

    /** @var Logger */
    private $logger;

    /**
     * FirefoxRegionPositionCompensation constructor.
     * @param Eyes $eyes
     * @param Logger $logger
     */
    public function __construct($eyes, $logger)
    {
        $this->eyes = $eyes;
        $this->logger = $logger;
    }

    /**
     * @param Region $region
     * @param double $pixelRatio
     * @return Region
     */
    function compensateRegionPosition(Region $region, $pixelRatio)
    {
        if ($pixelRatio == 1.0) {
            return $region;
        }

        if ($this->eyes instanceof EyesWebDriver) {

            $eyesWebDriver = $this->eyes->getDriver();
            $frameChain = $eyesWebDriver->getFrameChain();
            if ($frameChain->size() > 0) {
                return $region;
            }
        }

        $region = $region->offset(0, -(int)ceil($pixelRatio / 2));

        if ($region->getWidth() <= 0 || $region->getHeight() <= 0) {
            return Region::$empty;
        }

        return $region;
    }
}