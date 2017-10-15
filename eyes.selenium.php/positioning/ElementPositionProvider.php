<?php
namespace Applitools\Selenium;

use Applitools\ArgumentGuard;
use Applitools\Location;
use Applitools\Logger;
use Applitools\PositionMemento;
use Applitools\PositionProvider;
use Applitools\RectangleSize;
use Facebook\WebDriver\WebDriverElement;

class ElementPositionProvider implements PositionProvider {

    /** @var Logger */
    private $logger;

    /** @var EyesWebDriver */
    private $driver;

    /** @var EyesRemoteWebElement */
    private $element;

    public function __construct(Logger $logger, EyesWebDriver $driver, WebDriverElement $element) {
        ArgumentGuard::notNull($logger, "logger");
        ArgumentGuard::notNull($driver, "driver");
        ArgumentGuard::notNull($element, "element");

        $this->logger = $logger;
        $this->driver = $driver;
        $this->element = new EyesRemoteWebElement($logger, $driver, $element);
    }

    /**
     * @return Location The scroll position of the current element.
     */
    public function getCurrentPosition() {
        $this->logger->verbose("getCurrentScrollPosition()");

        $result = new Location($this->element->getScrollLeft(), $this->element->getScrollTop());

        $this->logger->verbose("Current position: $result");

        return $result;
    }

    /**
     * Go to the specified location.
     * @param Location $location The position to scroll to.
     */
    public function setPosition(Location $location) {
        $this->logger->verbose("Scrolling element to $location");

        $this->element->scrollTo($location);

        $this->logger->verbose("Done scrolling element!");
    }

    /**
     *
     * @return RectangleSize The entire size of the container which the position is relative to.
     */
    public function getEntireSize() {
        $this->logger->verbose("getEntireSize()");
        //Don't have possibility to get whole page.
        //But can get region size that contains current element
        $result = new RectangleSize($this->element->getScrollWidth(), $this->element->getScrollHeight());

        $this->logger->verbose("Entire size: $result");
        return $result;
    }

    public function getState() {
        return new ElementPositionMemento($this->getCurrentPosition());
    }

    public function restoreState(PositionMemento $state) {
        if ($state instanceof ElementPositionMemento) {
            $this->setPosition(new Location($state->getX(), $state->getY()));
        } else {
            throw new \InvalidArgumentException('state should be of type \Applitools\ElementPositionMemento');
        }
    }
}
