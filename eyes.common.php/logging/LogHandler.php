<?php

namespace Applitools;
/**
 * Handles log messages produces by the Eyes API.
 */
interface LogHandler {
    public function open();
    public function onMessage($verbose, $logString);
    public function close();
}

?>