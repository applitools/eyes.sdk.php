<?php

class ScrollPositionProvider implements PositionProvider
{

    protected $logger; //Logger
    protected $executor; //JavascriptExecutor

    public function __construct(Logger $logger, JavascriptExecutor $executor)
    {
        ArgumentGuard::notNull($logger, "logger");
        ArgumentGuard::notNull($executor, "executor");

        $this->logger = $logger;
        $this->executor = $executor;
    }

    /**
     * @return The scroll position of the current frame.
     */
    public function getCurrentPosition()
    {
        $this->logger->verbose("getCurrentScrollPosition()");
        try {
            $result = EyesSeleniumUtils::getCurrentScrollPosition($this->executor);
        } catch (WebDriverException $e) {
            throw new EyesDriverOperationException("Failed to extract current scroll position!");
        }
        $this->logger->verbose(sprintf("Current position: %s", json_encode($result)));
        return $result;
    }

    /**
     * Go to the specified location.
     * @param location The position to scroll to.
     */
    public function setPosition(Location $location)
    {
        $this->logger->verbose(sprintf("Scrolling to %s", json_encode($location)));
        EyesSeleniumUtils::setCurrentScrollPosition($this->executor, $location);
        $this->logger->verbose("Done scrolling!");
    }

    /**
     *
     * @return The entire size of the container which the position is relative
     * to.
     */
    public function getEntireSize()
    {
        $result = EyesSeleniumUtils::getCurrentFrameContentEntireSize($this->executor);
        $this->logger->verbose(sprintf("Entire size: %s", json_encode($result)));
        return $result;
    }
    
    public function getState(){
        return new ScrollPositionMemento($this->getCurrentPosition());
    }

    public function restoreState(PositionMemento $state) {
        $s = /*(ScrollPositionMemento) */$state;
        $this->setPosition(new Location($s->getX(), $s->getY()));
    }
}
