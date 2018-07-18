<?php

namespace Applitools\Selenium;

use Applitools\ArgumentGuard;
use Applitools\Location;
use Applitools\Logger;
use Applitools\PositionMemento;
use Applitools\PositionProvider;
use Applitools\RectangleSize;
use Applitools\Selenium\Exceptions\EyesDriverOperationException;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\JavaScriptExecutor;

class ScrollPositionProvider implements PositionProvider
{

    /** @var Logger */
    protected $logger;

    /** @var JavaScriptExecutor */
    protected $executor;

    public function __construct(Logger $logger, JavascriptExecutor $executor)
    {
        ArgumentGuard::notNull($logger, "logger");
        ArgumentGuard::notNull($executor, "executor");

        $this->logger = $logger;
        $this->executor = $executor;
    }

    /**
     * @return Location The scroll position of the current frame.
     * @throws EyesDriverOperationException
     */
    public function getCurrentPosition()
    {
        $this->logger->verbose("getCurrentScrollPosition()");
        try {
            $result = EyesSeleniumUtils::getCurrentScrollPosition($this->executor);
        } catch (WebDriverException $e) {
            throw new EyesDriverOperationException("Failed to extract current scroll position!", $e);
        }
        $this->logger->verbose("Current position: $result");
        return $result;
    }

    /**
     * Go to the specified location.
     * @param Location $location The position to scroll to.
     */
    public function setPosition(Location $location)
    {
        $this->logger->verbose("Scrolling to $location");
        EyesSeleniumUtils::setCurrentScrollPosition($this->executor, $location);
        $this->logger->verbose("Done scrolling!");
    }

    /**
     *
     * @return RectangleSize The entire size of the container which the position is relative to.
     * @throws EyesDriverOperationException
     */
    public function getEntireSize()
    {
        $result = EyesSeleniumUtils::getCurrentFrameContentEntireSize($this->executor);
        $this->logger->verbose("Entire size: $result");
        return $result;
    }

    /**
     * @return PositionMemento|ScrollPositionMemento
     * @throws EyesDriverOperationException
     */
    public function getState()
    {
        return new ScrollPositionMemento($this->getCurrentPosition());
    }

    public function restoreState(PositionMemento $state = null)
    {
        if (empty($state)) {
            $state = new ScrollPositionMemento(new Location(0, 0));
        }
        $this->setPosition(new Location($state->getX(), $state->getY()));
    }
}
