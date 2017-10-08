<?php

namespace Applitools\Selenium;
use Applitools\ArgumentGuard;
use Applitools\Logger;

/**
 * A wrapper class for TouchScreen implementation. This class will record
 * certain events such as tap.
 */
class EyesTouchScreen /*implements TouchScreen */ /*ATTENTION_MOCK*/
{ //FIXME

    private $logger; //Logger
    private $driver; //EyesWebDriver
    private $touch; //TouchScreen

    public function __construct(Logger $logger, EyesWebDriver $driver,
        /*ATTENTION_MOCK TouchScreen */
                                $touch)
    {   //FIXME
        ArgumentGuard::notNull($logger, "logger");
        ArgumentGuard::notNull($driver, "driver");
        ArgumentGuard::notNull($touch, "touch");

        $this->logger = $logger;
        $this->driver = $driver;
        $this->touch = $touch;
    }

    /**
     * A tap action. From our point of view, it is the same as a click.
     * @param Coordinates $where Where to tap.
     */
    public function singleTap(Coordinates $where)
    {
        // This is not a mistake - Appium only supports getPageLocation (and
        // the result is relative to the viewPort)
        $location = EyesSeleniumUtils::getPageLocation($where);
        $this->logger->verbose("tap(" . $location . ")");

        $this->driver->getEyes()->addMouseTrigger(MouseAction::Click, Region::getEmpty(),
            $location);

        $this->touch->singleTap($where);
    }

    public function down($x, $y)
    {
        $this->touch->down($x, $y);
    }

    public function up($x, $y)
    {
        $this->touch->up($x, $y);
    }

    public function move($x, $y)
    {
        $this->touch->move($x, $y);
    }

    public function scroll(Coordinates $where = null, $xOffset, $yOffset)
    {
        $this->touch->scroll($where, $xOffset, $yOffset);
    }

    /**
     * Double tap action. We treat it the same as a double click.
     * @param where Where to double click.
     */
    public function doubleTap(Coordinates $where)
    {
        // This is not a mistake - Appium only supports getPageLocation (and
        // the result is relative to the viewPort)
        $location = EyesSeleniumUtils::getPageLocation($where);
        $this->logger->verbose("tap(" . $location . ")");

        $this->driver->getEyes()->addMouseTrigger(MouseAction::DoubleClick,
            Region::getEmpty(), $location);

        $this->touch->doubleTap($where);
    }

    public function longPress(Coordinates $where)
    {
        $this->touch->longPress($where);
    }

    public function MOCK____flick($xSpeed, $ySpeed)
    {  //FIXME
        $this->touch->flick($xSpeed, $ySpeed);
    }

    public function flick(Coordinates $where, $xOffset, $yOffset, $speed)
    {
        $this->touch->flick(where, xOffset, yOffset, speed);
    }
}