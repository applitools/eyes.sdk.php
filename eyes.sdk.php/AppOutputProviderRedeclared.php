<?php

namespace Applitools;

class AppOutputProviderRedeclared implements AppOutputProvider
{
    /** @var EyesBase */
    private $eyes;

    /** @var  Logger */
    private $logger;

    /** @var DebugScreenshotsProvider|NullDebugScreenshotProvider */
    private $debugScreenshotProvider;

    public function __construct(EyesBase $eyes, Logger $logger)
    {
        $this->eyes = $eyes;
        $this->logger = $logger;
        $this->debugScreenshotProvider = $eyes->getDebugScreenshotsProvider();
    }

    /** @inheritdoc */
    public function getAppOutput(RegionProvider $regionProvider, EyesScreenshot $lastScreenshot = null){
        return $this->getAppOutputWithScreenshot($regionProvider, $lastScreenshot);
    }

    private function getAppOutputWithScreenshot(RegionProvider $regionProvider, EyesScreenshot $lastScreenshot = null) {
        $this->logger->verbose("getting screenshot...");
        // Getting the screenshot (abstract function implemented by each SDK).
        $screenshot = $this->eyes->getScreenshot();
        $this->logger->verbose("Done getting screenshot!");

        // Cropping by region if necessary
        $region = $regionProvider->getRegion();

        if (!$region->isEmpty()) {
            $screenshot = $screenshot->getSubScreenshot($region, $regionProvider->getCoordinatesType(), false);
            $this->debugScreenshotProvider->save($screenshot->getImage(),"SUB_SCREENSHOT");
        }

        $this->logger->verbose("Compressing screenshot...");
        //FIXME
        //$compressResult = $screenshot;
        $compressResult = $this->eyes->compressScreenshot64($screenshot, $lastScreenshot);
        $this->logger->verbose("Done! Getting title...");
        $title = "";  //FIXME  $title = $this->eyes->getTitle();
        $this->logger->verbose("Done!");
        $result = new AppOutputWithScreenshot(new AppOutput($title, $compressResult), $screenshot);
        $this->logger->verbose("Done!");
        return $result;
    }
}