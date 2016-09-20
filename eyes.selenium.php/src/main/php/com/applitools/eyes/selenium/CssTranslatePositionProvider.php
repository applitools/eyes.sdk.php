<?php
/**
 * A {@link PositionProvider} which is based on CSS translates. This is
 * useful when we want to stitch a page which contains fixed position elements.
 */
class CssTranslatePositionProvider implements PositionProvider {

    private $logger; //Logger
    private $executor; //JavascriptExecutor
    private $lastSetPosition; //Location cache.

    public function __construct(Logger $logger, JavascriptExecutor
            $executor) {
        ArgumentGuard::notNull($logger, "logger");
        ArgumentGuard::notNull($executor, "executor");

        $this->logger = $logger;
        $this->executor = $executor;
    }

    public function getCurrentPosition() {
        $this->logger->verbose("position to return: " . json_encode($this->lastSetPosition));
        return $this->lastSetPosition;
    }

    public function setPosition(Location $location) {
        ArgumentGuard::notNull($location, "location");
        $this->logger->verbose("Setting position to: " . json_encode($location));
        EyesSeleniumUtils::translateTo($this->executor, $location);
        $this->logger->verbose("Done!");
        $this->lastSetPosition = $location;
    }

    public function getEntireSize() {
        /*RectangleSize */$entireSize = EyesSeleniumUtils::getCurrentFrameContentEntireSize($this->executor);
        $this->logger->verbose("Entire size: " . json_encode($entireSize));
        return $entireSize;
    }

    public function getState() {
        return new CssTranslatePositionMemento(
                EyesSeleniumUtils::getCurrentTransform($this->executor));
    }

    public function restoreState(PositionMemento $state) {
        EyesSeleniumUtils::setTransforms($this->executor,
                /*((CssTranslatePositionMemento)*/$state->getTransform());
    }
}
