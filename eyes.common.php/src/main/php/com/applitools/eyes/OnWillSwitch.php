<?php

namespace Applitools;

use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriver;

interface OnWillSwitch {
    /**
     * Will be called before switching into a frame.
     * @param mixed $targetType The type of frame we're about to switch into.
     * @param RemoteWebElement $targetFrame The element about to be switched to, if available. Otherwise, null.
     * @param Logger $logger
     * @param WebDriver $driver
     * @return
     */
    public function willSwitchToFrame($targetType, RemoteWebElement $targetFrame, Logger $logger, WebDriver $driver);


}

?>