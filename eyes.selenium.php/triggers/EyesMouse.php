<?php

namespace Applitools\Selenium;
use Applitools\ArgumentGuard;
use Applitools\Location;
use Applitools\Logger;
use Applitools\MouseAction;
use Applitools\Region;
use Facebook\WebDriver\Interactions\Internal\WebDriverCoordinates;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\WebDriverMouse;

/**
 * A wrapper class for Selenium's Mouse class. It adds saving of mouse events
 * so they can be sent to the agent later on.
 */
class EyesMouse implements WebDriverMouse {

    private $logger; //Logger
    private $eyesDriver; //EyesWebDriver
    private $mouse; //Mouse
    private $mouseLocation; //Location

    public function __construct(Logger $logger, EyesWebDriver $eyesDriver, WebDriverMouse $mouse) {
        ArgumentGuard::notNull($logger, "logger");
        ArgumentGuard::notNull($eyesDriver, "eyesDriver");
        ArgumentGuard::notNull($mouse, "mouse");

        $this->logger = $logger;
        $this->eyesDriver = $eyesDriver;
        $this->mouse = $mouse;
        $this->mouseLocation = new Location(0, 0);
    }

    /**
     * Moves the mouse according to the coordinates, if required.
     *
     * @param $where WebDriverCoordinates Optional. The coordinates to move to. If null, mouse position does not changes.
     */
    protected function moveIfNeeded(WebDriverCoordinates $where) {
        if ($where != null) {
            $this->mouseMove($where);
        }
    }

    public function click(WebDriverCoordinates $where) {
        $location = EyesSeleniumUtils::getPageLocation($where);
        $this->logger->verbose("click(" . json_encode($location) . ")");

        $this->moveIfNeeded($where);
        $this->addMouseTrigger(MouseAction::Click);

        $this->logger->verbose("Location is " . json_encode($this->mouseLocation));
        $this->mouse->click($where);
    }

    public function doubleClick(WebDriverCoordinates $where) {
        $location = EyesSeleniumUtils::getPageLocation($where);
        $this->logger->verbose("doubleClick(" . json_encode($location) . ")");

        $this->moveIfNeeded($where);
        $this->addMouseTrigger(MouseAction::DoubleClick);

        $this->logger->verbose("Location is " . json_encode($this->mouseLocation));
        $this->mouse->doubleClick($where);
    }

    public function mouseDown(WebDriverCoordinates $where) {
        $location = EyesSeleniumUtils::getPageLocation($where);
        $this->logger->verbose("mouseDown(" . json_encode($location) . ")");

        $this->moveIfNeeded($where);
        $this->addMouseTrigger(MouseAction::Down);

        $this->logger->verbose("Location is " . json_encode($this->mouseLocation));
        $this->mouse->mouseDown($where);
    }

    public function mouseUp(WebDriverCoordinates $where) {
        $location = EyesSeleniumUtils::getPageLocation($where);
        $this->logger->verbose("mouseUp(" . json_encode($location) . ")");

        $this->moveIfNeeded($where);
        $this->addMouseTrigger(MouseAction::Up);

        $this->logger->verbose("Location is " . json_encode($this->mouseLocation));
        $this->mouse->mouseUp($where);
    }

    /*public function mouseMove(Coordinates $where) {
        $location = EyesSeleniumUtils::getPageLocation($where);
        $this->logger->verbose("mouseMove(" . json_encode($location) . ")");

        if ($location != null) {
            $newX = max(0, $location->getX());
            $newY = max(0, $location->getY());
            $mouseLocation = new Location($newX, $newY);

            $this->addMouseTrigger(MouseAction::Move);
        }

        $this->mouse->mouseMove($where);
    }*/

    public function mouseMove(WebDriverCoordinates $where, $xOffset = null, $yOffset = null) {
        $location = EyesSeleniumUtils::getPageLocation($where);
        $this->logger->verbose("mouseMove(" . json_encode($location) . ", " . $xOffset . ", "
                . $yOffset . ")");

        if ($location != null) {
            $newX = (int)($location->getX() + $xOffset);
            $newY = (int)($location->getY() + $yOffset);
        } else {
            $newX = (int)($this->mouseLocation->getX() + $xOffset);
            $newY = (int)($this->mouseLocation->getY() + $yOffset);
        }

        if ($newX < 0) {
            $newX = 0;
        }

        if ($newY < 0) {
            $newY = 0;
        }

        $this->mouseLocation = new Location($newX, $newY);

        $this->addMouseTrigger(MouseAction::Move);

        $this->mouse->mouseMove($where, $xOffset, $yOffset);
    }

    public function contextClick(WebDriverCoordinates $where) {
        $location = EyesSeleniumUtils::getPageLocation($where);
        $this->logger->verbose("contextClick(" . json_encode($location) . ")");

        $this->moveIfNeeded($where);
        $this->addMouseTrigger(MouseAction::RightClick);

        $this->logger->verbose("Location is " . json_encode($this->mouseLocation));
        $this->mouse->contextClick($where);
    }

    protected function addMouseTrigger($action) {
        // Notice we send a copy of 'mouseLocation' to make sure the callee
        // will not change its values thus affecting our internal state.
        $this->eyesDriver->getEyes()->addMouseTriggerCursor(
                $action, Region::getEmpty(), $this->mouseLocation);
    }
}
