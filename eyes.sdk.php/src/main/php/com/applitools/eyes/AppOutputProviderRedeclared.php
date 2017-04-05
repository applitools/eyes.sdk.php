<?php

namespace Applitools;

class AppOutputProviderRedeclared implements AppOutputProvider
{
    private $eyes; //EyesBase

    public function __construct(EyesBase $eyes)
    {
        $this->eyes = $eyes;
    }

    public function getAppOutput(RegionProvider $regionProvider_, EyesScreenshot $lastScreenshot_){
        return $this->getAppOutputWithScreenshot($regionProvider_, $lastScreenshot_);
    }

//FIXME this functionality from EyesBase
    private function getAppOutputWithScreenshot(RegionProvider $regionProvider, EyesScreenshot $lastScreenshot) {
        $this->eyes->logger->verbose("getting screenshot...");
        // Getting the screenshot (abstract function implemented by each SDK).
        $screenshot = $this->eyes->getScreenshot();
        $this->eyes->logger->verbose("Done getting screenshot!");

        // Cropping by region if necessary
        $region = $regionProvider->getRegion();

        /*if (!$region->isEmpty()) {
            $screenshot = $screenshot->getSubScreenshot($region,
                $regionProvider->getCoordinatesType(), false);
        }*/

        $this->eyes->logger->verbose("Compressing screenshot...");
        //FIXME
        $compressResult = $screenshot;
        //$compressResult = $this->eyes->compressScreenshot64($screenshot, $lastScreenshot);
        $this->eyes->logger->verbose("Done! Getting title...");
        $title = "";  //FIXME  $title = $this->eyes->getTitle();
        $this->eyes->logger->verbose("Done!");
        $result = new AppOutputWithScreenshot(new AppOutput($title, $compressResult), $screenshot);
        $this->eyes->logger->verbose("Done!");
        return $result;
    }
}