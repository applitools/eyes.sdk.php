<?php
interface OnWillSwitch {
    /**
    * Will be called before switching into a frame.
    * @param targetType The type of frame we're about to switch into.
    * @param targetFrame The element about to be switched to,
    *                     if available. Otherwise, null.
    */
    public function willSwitchToFrame($targetType, RemoteWebElement $targetFrame, Logger $logger, WebDriver $driver);


}