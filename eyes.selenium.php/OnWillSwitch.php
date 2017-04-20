<?php

namespace Applitools;

use Facebook\WebDriver\Remote\RemoteWebElement;

interface OnWillSwitch {
    /**
     * Will be called before switching into a frame.
     * @param mixed $targetType The type of frame we're about to switch into.
     * @param RemoteWebElement|null $targetFrame The element about to be switched to, if available. Otherwise, null.
     * @return
     */
    public function willSwitchToFrame($targetType, RemoteWebElement $targetFrame = null);
}

?>