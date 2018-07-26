<?php

namespace Applitools;

class AppOutputProviderRedeclared implements AppOutputProvider
{
    /** @var EyesBase */
    private $eyes;

    /** @var  Logger */
    private $logger;

    /** @var DebugScreenshotsProvider|NullDebugScreenshotProvider */
    private $debugScreenshotsProvider;

    public function __construct(EyesBase $eyes, Logger $logger)
    {
        $this->eyes = $eyes;
        $this->logger = $logger;
        $this->debugScreenshotsProvider = $eyes->getDebugScreenshotsProvider();
    }

    /** @inheritdoc */
    public function getAppOutput(Region $region, EyesScreenshot $lastScreenshot = null){
        return $this->getAppOutputWithScreenshot($region, $lastScreenshot);
    }

    /**
     * @param Region $region
     * @param EyesScreenshot|null $lastScreenshot
     * @return AppOutputWithScreenshot
     */
    private function getAppOutputWithScreenshot(Region $region, EyesScreenshot $lastScreenshot = null) {
        $this->logger->verbose("getting screenshot...");

        // Getting the screenshot (abstract function implemented by each SDK).
        /** @var EyesScreenshot $screenshot */
        $screenshot = $this->eyes->getScreenshot();

        $this->logger->verbose("Done getting screenshot!");

        // Cropping by region if necessary
        if (!$region->isEmpty()) {
            $screenshot = $screenshot->getSubScreenshot($region, false);
            $subScreenshot = $screenshot->getImage();
            $this->debugScreenshotsProvider->save($subScreenshot, "SUB_SCREENSHOT");
        }

        $this->logger->verbose("Compressing screenshot...");
        $compressResult = $this->eyes->compressScreenshot64($screenshot, $lastScreenshot);
        $this->logger->verbose("Done! Getting title...");

        $title = $this->eyes->getTitle();

        $this->logger->verbose("Done!");
        $result = new AppOutputWithScreenshot(new AppOutput($title, $compressResult), $screenshot);
        $this->logger->verbose("Done!");
        return $result;
    }
}