<?php

namespace Applitools\Selenium;

use Applitools\CoordinatesType;
use Applitools\ImageProvider;
use Applitools\Location;
use Applitools\Logger;
use Applitools\Region;
use Applitools\RegionProvider;

class FullFrameOrElementRegionProvider extends RegionProvider
{
    /** @var Eyes */
    private $eyes;

    /** @var Logger */
    private $logger;

    /** @var ImageProvider */
    private $imageProvider;

    /**
     * FullRegionProvider constructor.
     * @param Logger $logger
     * @param Eyes $eyes
     * @param ImageProvider $imageProvider
     */
    public function __construct(Logger $logger, Eyes $eyes, ImageProvider $imageProvider)
    {
        parent::__construct();
        $this->logger = $logger;
        $this->eyes = $eyes;
        $this->imageProvider = $imageProvider;
    }

    /**
     * @return Region
     * @throws \Applitools\Exceptions\EyesException
     */
    public function getRegion()
    {
        if ($this->eyes->getCheckFrameOrElement()) {
            $spp = new ScrollPositionProvider($this->logger, $this->eyes->getDriver());
            $spp->setPosition(Location::getZero());

            // FIXME - Scaling should be handled in a single place instead
            $scaleProviderFactory = $this->eyes->updateScalingParams();

            /** @var resource $screenshotImage */
            $screenshotImage = $this->imageProvider->getImage();

            $this->eyes->getDebugScreenshotsProvider()->save($screenshotImage, "checkFulFrameOrElement");

            $scaleProviderFactory->getScaleProvider(imagesx($screenshotImage));

            $screenshot = new EyesWebDriverScreenshot($this->logger, $this->eyes->getDriver(), $screenshotImage);

            $this->logger->verbose("replacing regionToCheck");
            $this->eyes->setRegionToCheck($screenshot->getFrameWindow());
        }

        return Region::getEmpty();
    }

    /**
     * @return string
     */
    public function getCoordinatesType()
    {
        // If we're given a region, it is relative to the frame's viewport.
        return CoordinatesType::CONTEXT_RELATIVE;
    }
}









