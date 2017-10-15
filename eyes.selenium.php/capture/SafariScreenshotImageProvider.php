<?php

namespace Applitools\Selenium;

use Applitools\ImageProvider;
use Applitools\ImageUtils;
use Applitools\Logger;
use Applitools\Region;
use Gregwar\Image\Image;

class SafariScreenshotImageProvider implements ImageProvider
{
    /** @var Eyes */
    private $eyes;

    /** @var Logger */
    private $logger;

    /** @var EyesWebDriver */
    private $tsInstance;

    function __construct(Eyes $eyes, Logger $logger, EyesWebDriver $tsInstance)
    {
        $this->eyes = $eyes;
        $this->logger = $logger;
        $this->tsInstance = $tsInstance;
    }

    /**
     * @return Image
     */
    function getImage()
    {
        $this->logger->verbose("Getting screenshot...");
        $image = $this->tsInstance->getScreenshot();

        $this->eyes->getDebugScreenshotsProvider()->save($image, "SAFARI");

        if (!$this->eyes->getForceFullPageScreenshot()) {

            $currentFrameChain = $this->tsInstance->getFrameChain();

            if ($currentFrameChain->size() == 0) {
                $positionProvider = new ScrollPositionProvider($this->logger, $this->tsInstance);
                $loc = $positionProvider->getCurrentPosition();
            } else {
                $loc = $currentFrameChain->getDefaultContentScrollPosition();
            }

            $scaleRatio = $this->eyes->getDevicePixelRatio();
            $viewportSize = $this->eyes->getViewportSize();
            $viewportSize = $viewportSize->scale($scaleRatio);
            $loc = $loc->scale($scaleRatio);

            $cutImage = ImageUtils::getImagePart($image, Region::CreateFromLocationAndSize($loc,$viewportSize));

            return $cutImage;
        }

        return $image;
    }
}