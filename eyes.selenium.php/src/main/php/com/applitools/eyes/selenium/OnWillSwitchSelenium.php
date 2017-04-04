<?php

use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriver;

class OnWillSwitchSelenium implements OnWillSwitch {
    private $frameChain; //FrameChain FIXME

    public function __construct(FrameChain $frameChain){
        $this->frameChain = $frameChain; //FIXME need to check
    }

    public function willSwitchToFrame($targetType, RemoteWebElement $targetFrame = null, Logger $logger, WebDriver $driver) {
        $logger->verbose("willSwitchToFrame()");

        switch($targetType) {
            case TargetType::DEFAULT_CONTENT:
                $logger->verbose("Default content.");
                $this->frameChain->clear();
                break;
            case TargetType::PARENT_FRAME:
                $logger->verbose("Parent frame.");
                $this->frameChain->pop();
                break;
            default: // Switching into a frame
                $logger->verbose("Frame");
    
                $frameId = /*(EyesRemoteWebElement)*/$targetFrame->getId();
                $pl = $targetFrame->getLocation();
                $ds = $targetFrame->getSize();

                // Get the frame's content location.
                $bordersAwareElement = new BordersAwareElementContentLocationProvider();
                $contentLocation = $bordersAwareElement->getLocation($logger, $targetFrame, new Location($pl->getX(), $pl->getY()));
                
                $scrollPositionProvider = new ScrollPositionProvider($logger, $driver);

                $rectangleSize = new RectangleSize($ds->getWidth(), $ds->getHeight());

                $frame = new Frame($logger, $targetFrame, $frameId, $contentLocation, $rectangleSize, $scrollPositionProvider->getCurrentPosition());


                $this->frameChain->push($frame);
        }
        $logger->verbose("Done!");
    }

}