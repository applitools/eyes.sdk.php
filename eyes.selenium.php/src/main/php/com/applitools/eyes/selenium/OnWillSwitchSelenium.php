<?php
namespace Applitools;

use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriver;

class OnWillSwitchSelenium implements OnWillSwitch {
    private $frameChain; //FrameChain FIXME

    public function __construct(FrameChain $frameChain){
        $this->frameChain = $frameChain; //FIXME need to check
    }

    public function willSwitchToFrame($targetType, RemoteWebElement $targetFrame = null, Logger $logger, EyesWebDriver $driver) {
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
    
                $frameId = $targetFrame->getId();

                /** @var EyesRemoteWebElement $eyesFrame */
                $eyesFrame = null;
                if ($targetFrame instanceof EyesRemoteWebElement) {
                    $eyesFrame = $targetFrame;
                } else {
                    $eyesFrame = new EyesRemoteWebElement($logger, $driver, $targetFrame);
                }

                $rect = $eyesFrame->getClientAreaBounds();
                $pl = $rect->getLocation();
                $innerSize = $rect->getSize();

                $scrollPositionProvider = new ScrollPositionProvider($logger, $driver);

                $frame = new Frame($logger, $targetFrame, $frameId, $pl, $innerSize, $scrollPositionProvider->getCurrentPosition());

                $this->frameChain->push($frame);
        }
        $logger->verbose("Done!");
    }
}