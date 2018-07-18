<?php
namespace Applitools\Selenium;

use Applitools\ArgumentGuard;
use Applitools\Location;
use Applitools\Logger;
use Applitools\PositionMemento;
use Applitools\PositionProvider;
use Facebook\WebDriver\JavaScriptExecutor;

/**
 * A {@link PositionProvider} which is based on CSS translates. This is
 * useful when we want to stitch a page which contains fixed position elements.
 */
class CssTranslatePositionProvider implements PositionProvider {

    /** @var Logger */
    private $logger;

    /** @var JavaScriptExecutor */
    private $executor;

    /** @var Location */
    private $lastSetPosition;

    public function __construct(Logger $logger, JavascriptExecutor $executor) {
        ArgumentGuard::notNull($logger, "logger");
        ArgumentGuard::notNull($executor, "executor");

        $this->logger = $logger;
        $this->executor = $executor;
    }

    public function getCurrentPosition() {
        $this->logger->verbose("position to return: {$this->lastSetPosition}");
        return $this->lastSetPosition;
    }

    public function setPosition(Location $location) {
        ArgumentGuard::notNull($location, "location");
        $this->logger->verbose("Setting position to: $location");
        EyesSeleniumUtils::translateTo($this->executor, $location);
        $this->logger->verbose("Done!");
        $this->lastSetPosition = $location;
    }

    /**
     * @return \Applitools\RectangleSize
     * @throws Exceptions\EyesDriverOperationException
     */
    public function getEntireSize() {
        $entireSize = EyesSeleniumUtils::getCurrentFrameContentEntireSize($this->executor);
        $this->logger->verbose("Entire size: $entireSize");
        return $entireSize;
    }

    public function getState() {
        return new CssTranslatePositionMemento(EyesSeleniumUtils::getCurrentTransform($this->executor));
    }

    public function restoreState(PositionMemento $state = null) {
        if(empty($state)){
            $state = new CssTranslatePositionMemento(array()); //FIXME need to check
        }
        EyesSeleniumUtils::setTransforms($this->executor, $state->getTransform());
    }
}
