<?php

class ElementPositionProvider implements PositionProvider {
    private $logger; //Logger
    private $driver; //EyesWebDriver
    private $element; //EyesRemoteWebElement

    public function __construct(Logger $logger, EyesWebDriver $driver,
                                   WebDriverElement $element) {
        ArgumentGuard::notNull($logger, "logger");
        ArgumentGuard::notNull($driver, "driver");
        ArgumentGuard::notNull($element, "element");

        $this->logger = $logger;
        $this->driver = $driver;
        $this->element = new EyesRemoteWebElement($logger, $driver,
                /*(RemoteWebElement)*/$element);
    }

    /**
     * @return The scroll position of the current element.
     */
    public function getCurrentPosition() {
        $this->logger->verbose("getCurrentScrollPosition()");

        $result = new Location($this->element->getScrollLeft(),
                $this->element->getScrollTop());

        $this->logger->verbose(sprintf("Current position: %s", json_encode($result)));

        return $result;
    }

    /**
     * Go to the specified location.
     * @param location The position to scroll to.
     */
    public function setPosition(Location $location) {
        $this->logger->verbose(sprintf("Scrolling element to %s", json_encode($location)));

        $this->element->scrollTo($location);

        $this->logger->verbose("Done scrolling element!");
    }

    /**
     *
     * @return The entire size of the container which the position is relative
     * to.
     */
    public function getEntireSize() {
        $this->logger->verbose("getEntireSize()");

        $result = new RectangleSize($this->element->getScrollWidth(),
                $this->element->getScrollHeight());

        $this->logger->verbose(sprintf("Entire size: %s", json_encode($result)));
        return $result;
    }

    public function getState() {
        return new ElementPositionMemento($this->getCurrentPosition());
    }

    public function restoreState(PositionMemento $state) {
        //s = (ElementPositionMemento) state;
        $this->setPosition(new Location($state->getX(), $state->getY()));
    }
}
