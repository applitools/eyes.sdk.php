<?php

namespace Applitools\Selenium;

use Applitools\ImageProvider;
use Applitools\Logger;

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
     * @return resource
     */
    function getImage()
    {
        $this->logger->verbose("Getting screenshot...");

        $frameChain = clone $this->tsInstance->getFrameChain();
        $this->eyes->getDriver()->switchTo()->defaultContent();

        $image = $this->tsInstance->getScreenshot();
        $this->eyes->getDebugScreenshotsProvider()->save($image, "FIREFOX_FRAME");

        $this->eyes->getDriver()->switchTo()->frames($frameChain);

        return $image;
    }
}