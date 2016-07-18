<?php
require "TargetType.php";
/**
 * Wraps a target locator so we can keep track of which frames have been
 * switched to.
 */
class EyesTargetLocator implements WebDriverTargetLocator {

    private $logger; //Logger
    private $driver; //EyesWebDriver
    private $scrollPosition; //ScrollPositionProvider
    private $onWillSwitch; //OnWillSwitch
    private $targetLocator; //WebDriver.TargetLocator
    protected $targetType; // TargetType

    /**
     * Initialized a new EyesTargetLocator object.
     * @param driver The WebDriver from which the targetLocator was received.
     * @param targetLocator The actual TargetLocator object.
     * @param onWillSwitch A delegate to be called whenever a relavant switch
     *                     is about to be performed.
     */
    public function __construct(Logger $logger, EyesWebDriver $driver,
                             WebDriverTargetLocator $targetLocator, OnWillSwitch $onWillSwitch) {
        $this->targetType = new TargetType();
        ArgumentGuard::notNull($logger, "logger");
        ArgumentGuard::notNull($driver, "driver");
        ArgumentGuard::notNull($targetLocator, "targetLocator");
        ArgumentGuard::notNull($onWillSwitch, "onWillSwitch");
        $this->logger = $logger;
        $this->driver = $driver;
        $this->targetLocator = $targetLocator;
        $this->onWillSwitch = $onWillSwitch;
        $this->scrollPosition = new ScrollPositionProvider($logger, $driver);
    }

    public function frame($selector) {
        if($selector instanceof WebElement){
            $frameElement = $selector;
            $this->logger->verbose("EyesTargetLocator.frame(element)");
            $this->logger->verbose("Making preparations..");
            $this->onWillSwitch->willSwitchToFrame(TargetType::FRAME, $frameElement);
            $this->logger->verbose("Done! Switching to frame...");
            $this->targetLocator->frame($frameElement);
            $this->logger->verbose("Done!");
            return $this->driver;
        }
        else if(is_integer($selector)){
            $index = $selector;
            $this->logger->verbose(sprintf("EyesTargetLocator.frame(%d)", $index));
            // Finding the target element so and reporting it using onWillSwitch.
            $this->logger->verbose("Getting frames list...");
            $frames = $this->driver->findElementsByCssSelector(
                "frame, iframe");
            if ($index > $this->frames->size()) {
                throw new NoSuchFrameException(sprintf("Frame index [%d] is invalid!", $index));
            }
            $this->logger->verbose("Done! getting the specific frame...");
            $targetFrame = $frames->get($index);
            $this->logger->verbose("Done! Making preparations...");
            $this->onWillSwitch->willSwitchToFrame(TargetType::FRAME, $targetFrame);
            $this->logger->verbose("Done! Switching to frame...");
            $this->targetLocator->frame($index);
            $this->logger->verbose("Done!");
            return $this->driver;
        } else{
            $nameOrId = $selector;
            $this->logger->verbose(sprintf("EyesTargetLocator.frame('%s')", $nameOrId));
            // Finding the target element so we can report it.
            // We use find elements(plural) to avoid exception when the element
            // is not found.
            $this->logger->verbose("Getting frames by name...");
            $frames = $this->driver->findElementsByName($nameOrId);
            if ($frames->count() == 0) {
                $this->logger->verbose("No frames Found! Trying by id...");
                // If there are no frames by that name, we'll try the id
                $frames = $this->driver->findElementsById($nameOrId);
                if ($frames->size() == 0 ) {
                    // No such frame, bummer
                    throw new NoSuchFrameException(sprintf("No frame with name or id '%s' exists!", $nameOrId));
                }
            }
            $this->logger->verbose("Done! Making preparations..");
            $this->onWillSwitch->willSwitchToFrame(TargetType::FRAME, $frames->get(0));
            $this->logger->verbose("Done! Switching to frame...");
            $this->targetLocator.frame($nameOrId);
            $this->logger->verbose("Done!");
            return $this->driver;
        }

    }

    public function parentFrame() {
        $this->logger->verbose("EyesTargetLocator.parentFrame()");
        if ($this->driver->getFrameChain()->size() != 0) {
            $this->logger->verbose("Making preparations..");
            $this->onWillSwitch->willSwitchToFrame(TargetType::PARENT_FRAME, null);
            $this->logger->verbose("Done! Switching to parent frame..");
            $this->targetLocator->parentFrame();
        }
        $this->logger->verbose("Done!");
        return $this->driver;
    }

    /**
     * Switches into every frame in the frame chain. This is used as way to
     * switch into nested frames (while considering scroll) in a single call.
     * @param frameArg The path to the frame to switch to.
     *                 Or the path to the frame to check. This is a list of
     *                 frame names/IDs (where each frame is nested in the
     *                 previous frame).
     * @return The WebDriver with the switched context.
     */
    public function frames($frameArg) {
        if($frameArg instanceof FrameChain){
            $frameChain = $frameArg;
            $this->logger->verbose("EyesTargetLocator.frames(frameChain)");
            foreach ($frameChain as $frame) {
                $this->logger->verbose("Scrolling by parent scroll position..");
                $this->scrollPosition->setPosition($frame->getParentScrollPosition());
                $this->logger->verbose("Done! Switching to frame...");
                $this->driver->switchTo()->frame($frame->getReference());
                $this->logger->verbose("Done!");
            }
        }else{
            $framesPath = $frameArg;
            $this->logger->verbose("EyesTargetLocator.frames(framesPath)");
            foreach($framesPath as $frameNameOrId) {
                $this->logger->verbose("Switching to frame...");
                $this->driver->switchTo()->frame($frameNameOrId);
                $this->logger->verbose("Done!");
            }
        }

        $this->logger->verbose("Done switching into nested frames!");
        return $this->driver;
    }

    public function window($nameOrHandle) {
        $this->logger->verbose("EyesTargetLocator.frames()");
        $this->logger->verbose("Making preparations..");
        $this->onWillSwitch->willSwitchToWindow($nameOrHandle);
        $this->logger->verbose("Done! Switching to window..");
        $this->targetLocator->window($nameOrHandle);
        $this->logger->verbose("Done!");
        return $this->driver;
    }

    public function defaultContent() {
        $this->logger->verbose("EyesTargetLocator.defaultContent()");
        if ($this->driver->getFrameChain()->size() != 0) {
            $this->logger->verbose("Making preparations..");
            $this->onWillSwitch->willSwitchToFrame(TargetType::DEFAULT_CONTENT, null);
            $this->logger->verbose("Done! Switching to default content..");
            $this->targetLocator->defaultContent();
            $this->logger->verbose("Done!");
        }
        return $this->driver;
    }

    public function activeElement() {
        $this->logger->verbose("EyesTargetLocator.activeElement()");
        $this->logger->verbose("Switching to element..");
        $element = $this->targetLocator->activeElement();
        if (!($element instanceof RemoteWebElement)) {
            throw new EyesException("Not a remote web element!");
        }
        $result = new EyesRemoteWebElement($this->logger, $this->driver,
                /*(RemoteWebElement)*/$element);
        $this->logger->verbose("Done!");
        return $result;
    }

    public function alert() {
        $this->logger->verbose("EyesTargetLocator.alert()");
        $this->logger->verbose("Switching to alert..");
        $result = $this->targetLocator->alert();
        $this->logger.verbose("Done!");
        return $result;
    }
}