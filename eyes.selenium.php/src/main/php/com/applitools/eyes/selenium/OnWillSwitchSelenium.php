<?php
namespace Applitools;

use Facebook\WebDriver\Remote\RemoteWebElement;

class OnWillSwitchSelenium implements OnWillSwitch {

    /** @var FrameChain */
    private $frameChain;

    /** @var EyesWebDriver */
    private $driver;

    /** @var  Logger */
    private $logger;

    /** @var ScrollPositionProvider */
    private $scrollPositionProvider;

    public function __construct(FrameChain $frameChain, Logger $logger, EyesWebDriver $driver){
        $this->frameChain = $frameChain;
        $this->logger = $logger;
        $this->driver = $driver;
        $this->scrollPositionProvider = new ScrollPositionProvider($this->logger, $this->driver);
    }

    /**
     * @inheritdoc
     */
    public function willSwitchToFrame($targetType, RemoteWebElement $targetFrame = null) {
        $this->logger->verbose("willSwitchToFrame($targetType,...)");

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
    
                $frameId = $targetFrame->getId();

                /** @var EyesRemoteWebElement $eyesFrame */
                $eyesFrame = null;
                if ($targetFrame instanceof EyesRemoteWebElement) {
                    $eyesFrame = $targetFrame;
                } else {
                    $eyesFrame = new EyesRemoteWebElement($this->logger, $this->driver, $targetFrame);
                }

                $rect = $eyesFrame->getClientAreaBounds();
                $pl = $rect->getLocation();
                $innerSize = $rect->getSize();

                $frame = new Frame($this->logger, $targetFrame, $frameId, $pl, $innerSize, $pl);//$currentLocation);

                $this->frameChain->push($frame);
        }
        $this->logger->verbose("Done!");
    }
}