<?php

namespace Applitools\Selenium;

use Applitools\ArgumentGuard;
use Applitools\Exceptions\EyesException;
use Applitools\Location;
use Applitools\Logger;
use Applitools\RectangleSize;
use Facebook\WebDriver\Exception\NoSuchFrameException;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverTargetLocator;

/**
 * Wraps a target locator so we can keep track of which frames have been switched to.
 */
class EyesTargetLocator implements WebDriverTargetLocator
{
    /** @var Logger Logger */
    private $logger;
    /** @var EyesWebDriver */
    protected $driver;
    /** @var ScrollPositionProvider */
    private $scrollPosition;
    /** @var WebDriverTargetLocator */
    private $targetLocator;

    /**
     * Initialized a new EyesTargetLocator object.
     * @param Logger $logger
     * @param EyesWebDriver $driver The WebDriver from which the targetLocator was received.
     * @param WebDriverTargetLocator $targetLocator The actual TargetLocator object.
     */
    public function __construct(Logger $logger, EyesWebDriver $driver, WebDriverTargetLocator $targetLocator)
    {
        ArgumentGuard::notNull($logger, "logger");
        ArgumentGuard::notNull($driver, "driver");
        ArgumentGuard::notNull($targetLocator, "targetLocator");
        $this->logger = $logger;
        $this->driver = $driver;
        $this->targetLocator = $targetLocator;
        $this->scrollPosition = new ScrollPositionProvider($logger, $driver);
    }

    public function frame($selector)
    {
        $frameElement = null;
        if ($selector instanceof RemoteWebElement) {
            $frameElement = $selector;
            $this->logger->verbose("EyesTargetLocator.frame(element)");
        } else if ($selector instanceof WebDriverBy) {
            $frames = $this->driver->findElements($selector);
            if (count($frames) == 0) {
                throw new NoSuchFrameException("The given selector didn't find any match.");
            }
            $frameElement = $frames[0];
        } else if (is_string($selector)) {
            $nameOrId = $selector;
            $this->logger->verbose("EyesTargetLocator->frame('$nameOrId')");
            $frameElement = Eyes::findElement($this->driver, $nameOrId);
        } else {
            throw new \InvalidArgumentException("Can't handle selector of type " . get_class($selector));
        }

        $this->logger->verbose("Making preparations...");
        $this->willSwitchToFrame($frameElement);
        $this->logger->verbose("Done! Switching to frame...");
        $this->targetLocator->frame($frameElement);
        $this->logger->verbose("Done!");
        return $this->driver;
    }

    private function willSwitchToFrame(RemoteWebElement $targetFrame = null)
    {
        /** @var EyesRemoteWebElement $eyesFrame */
        $eyesFrame = null;
        if ($targetFrame instanceof EyesRemoteWebElement) {
            $eyesFrame = $targetFrame;
        } else {
            $eyesFrame = new EyesRemoteWebElement($this->logger, $this->driver, $targetFrame);
        }

        $pl = $eyesFrame->getLocation();
        $ds = $eyesFrame->getSize();

        $clientWidth = $eyesFrame->getClientWidth();
        $clientHeight = $eyesFrame->getClientHeight();

        $location = new Location($pl->getX(), $pl->getY());

        // Get the frame's content location.
        $contentLocation = BordersAwareElementContentLocationProvider::getLocation($this->logger, $targetFrame, $location);

        $originalLocation = $this->scrollPosition->getCurrentPosition();
        $this->scrollPosition->setPosition($location);

        $currentLocation = $this->scrollPosition->getCurrentPosition();

        $frame = new Frame($this->logger, $targetFrame,
            $contentLocation,
            new RectangleSize($ds->getWidth(), $ds->getHeight()),
            new RectangleSize($clientWidth, $clientHeight),
            $currentLocation,
            $originalLocation);

        $this->driver->getFrameChain()->push($frame);
    }

    public function parentFrame()
    {
        $this->logger->verbose("EyesTargetLocator.parentFrame()");
        $chain = $this->driver->getFrameChain();
        $this->logger->verbose("switching to parent frame. \"before\" chain size: {$chain->size()}");

        if ($chain->size() > 0) {
            $this->logger->verbose("Making preparations...");
            $this->driver->getFrameChain()->pop();
            $this->logger->verbose("Done! Switching to parent frame...");
            if ($chain->size() > 0) {
                $this->logger->verbose("switching to current frame. chain size: {$chain->size()}");
                $this->targetLocator->defaultContent();
                foreach ($chain->getFrames() as $frame) {
                    $this->targetLocator->frame($frame->getReference());
                }
            } else {
                $this->logger->verbose("switching to default content");
                $this->targetLocator->defaultContent();
            }
        }
        $this->logger->verbose("Done!");
        return $this->driver;
    }

    /**
     * Switches into every frame in the frame chain. This is used as way to
     * switch into nested frames (while considering scroll) in a single call.
     * @param mixed $frameArg The path to the frame to switch to.
     *                 Or the path to the frame to check. This is a list of
     *                 frame names/IDs (where each frame is nested in the
     *                 previous frame).
     * @return WebDriver The WebDriver with the switched context.
     */
    public function frames($frameArg)
    {
        if ($frameArg instanceof FrameChain) {
            $frameChain = $frameArg;
            $this->logger->verbose("EyesTargetLocator.frames(frameChain)");
            $frames = $frameChain->getFrames();
            if ($frames != null) {
                foreach ($frames as $frame) {
                    $this->logger->verbose("Scrolling by parent scroll position..");
                    $this->scrollPosition->setPosition($frame->getParentScrollPosition());
                    $this->logger->verbose("Done! Switching to frame...");
                    $this->frame($frame->getReference());
                    $this->logger->verbose("Done!");
                }
            }
        } else {
            $framesPath = $frameArg;
            $this->logger->verbose("EyesTargetLocator.frames(framesPath)");
            foreach ($framesPath as $frameNameOrId) {
                $this->logger->verbose("Switching to frame...");
                $this->frame($frameNameOrId);
                $this->logger->verbose("Done!");
            }
        }
        $this->logger->verbose("Done switching into nested frames!");
        return $this->driver;
    }

    public function window($nameOrHandle)
    {
        $this->logger->verbose("EyesTargetLocator.window($nameOrHandle)");
        $this->driver->getFrameChain()->clear();
        $this->logger->verbose("Done! Switching to window...");
        $this->targetLocator->window($nameOrHandle);
        $this->logger->verbose("Done!");
        return $this->driver;
    }

    public function defaultContent()
    {
        $this->logger->verbose("EyesTargetLocator.defaultContent()");
        if ($this->driver->getFrameChain()->size() != 0) {
            $this->logger->verbose("Making preparations..");
            $this->driver->getFrameChain()->clear();
            $this->logger->verbose("Done! Switching to default content...");
            $this->targetLocator->defaultContent();
            $this->logger->verbose("Done!");
        }
        return $this->driver;
    }

    public function activeElement()
    {
        $this->logger->verbose("EyesTargetLocator.activeElement()");
        $this->logger->verbose("Switching to element..");
        $element = $this->targetLocator->activeElement();
        if (!($element instanceof RemoteWebElement)) {
            throw new EyesException("Not a remote web element!");
        }
        $result = new EyesRemoteWebElement($this->logger, $this->driver, $element);
        $this->logger->verbose("Done!");
        return $result;
    }

    public function alert()
    {
        $this->logger->verbose("EyesTargetLocator.alert()");
        $this->logger->verbose("Switching to alert..");
        $result = $this->targetLocator->alert();
        $this->logger->verbose("Done!");
        return $result;
    }
}