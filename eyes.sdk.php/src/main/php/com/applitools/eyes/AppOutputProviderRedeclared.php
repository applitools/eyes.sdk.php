<?php

namespace Applitools;

class AppOutputProviderRedeclared implements AppOutputProvider
{
    /** @var EyesBase */
    private $eyes;

    /** @var  Logger */
    private $logger;

    public function __construct(EyesBase $eyes, Logger $logger)
    {
        $this->eyes = $eyes;
        $this->logger = $logger;
    }

    public function getAppOutput(RegionProvider $regionProvider_, EyesScreenshot $lastScreenshot_){
        return $this->getAppOutputWithScreenshot($regionProvider_, $lastScreenshot_);
    }

//FIXME this functionality from EyesBase
    private function getAppOutputWithScreenshot(RegionProvider $regionProvider, EyesScreenshot $lastScreenshot) {
        $this->logger->verbose("getting screenshot...");
        // Getting the screenshot (abstract function implemented by each SDK).
        $screenshot = $this->eyes->getScreenshot();
        $this->logger->verbose("Done getting screenshot!");

        // Cropping by region if necessary
        $region = $regionProvider->getRegion();

        /*if (!$region->isEmpty()) {
            $screenshot = $screenshot->getSubScreenshot($region,
                $regionProvider->getCoordinatesType(), false);
        }*/

        $this->logger->verbose("Compressing screenshot...");
        //FIXME
        $compressResult = $screenshot;
        //$compressResult = $this->eyes->compressScreenshot64($screenshot, $lastScreenshot);
        $this->logger->verbose("Done! Getting title...");
        $title = "";  //FIXME  $title = $this->eyes->getTitle();
        $this->logger->verbose("Done!");
        $result = new AppOutputWithScreenshot(new AppOutput($title, $compressResult), $screenshot);
        $this->logger->verbose("Done!");
        return $result;
    }
}