<?php

namespace Applitools\Selenium;

use Applitools\ImageProvider;
use Applitools\Logger;
use Gregwar\Image\Image;

class FirefoxScreenshotImageProvider implements ImageProvider
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

        $this->eyes->getDebugScreenshotsProvider()->save($image, "FIREFOX_FRAME");

        $frameChain = $this->tsInstance->getFrameChain();
        if ($frameChain->size() > 0) {

            $screenshot = new EyesWebDriverScreenshot($this->logger, $this->tsInstance, $image);

            $loc = $screenshot->getFrameWindow()->getLocation();
            $this->logger->verbose("frame.getLocation(): $loc");

            $scaleRatio = $this->eyes->getDevicePixelRatio();
            $viewportSize = $this->eyes->getViewportSize();
            $viewportSize = $viewportSize->scale($scaleRatio);
            $loc = $loc->scale($scaleRatio);

            $fullImage = new Image(null,
                $viewportSize->getWidth(),
                $viewportSize->getHeight());

            $fullImage->merge($image, $loc->getX(), $loc->getY());

            return $fullImage;
        }

        return $image;
    }
}