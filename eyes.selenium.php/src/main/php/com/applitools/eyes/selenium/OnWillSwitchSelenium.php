<?php
//require "../../eyes/eyes.php/eyes.common.php/src/main/php/com/applitools/eyes/OnWillSwitch.php";

class OnWillSwitchSelenium implements OnWillSwitch{
    public function willSwitchToFrame(TargetType $targetType, WebElement $targetFrame) {
        $this->logger->verbose("willSwitchToFrame()");
        switch($targetType) {
            case TargetType::DEFAULT_CONTENT:
                $this->logger->verbose("Default content.");
                $this->frameChain->clear();
                break;
            case TargetType::PARENT_FRAME:
                $this->logger->verbose("Parent frame.");
                $this->frameChain->pop();
                break;
            default: // Switching into a frame
                $this->logger->verbose("Frame");
    
                $frameId = /*(EyesRemoteWebElement)*/$targetFrame->getId();
                $pl = $targetFrame->getLocation();
                $ds = $targetFrame->getSize();
                // Get the frame's content location.
                $bordersAwareElement = new BordersAwareElementContentLocationProvider();
                $contentLocation = $bordersAwareElement->getLocation($this->logger, $targetFrame, new Location($pl->getX(), $pl->getY()));
                $scrollPositionProvider = new ScrollPositionProvider($this->logger, $this->driver);
                $rectangleSize = new RectangleSize($ds->getWidth(), $ds->getHeight());
                $frame = new Frame($this->logger, $targetFrame, $frameId, $contentLocation, $rectangleSize, $scrollPositionProvider->getCurrentPosition());
                    $this->frameChain->push($frame);
        }
        $this->logger->verbose("Done!");
    }
    
    public function willSwitchToWindow($nameOrHandle) {
        $this->logger->verbose("willSwitchToWindow()");
        $this->frameChain->clear();
        $this->logger->verbose("Done!");
    }
}