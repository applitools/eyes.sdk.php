<?php
/*
 * Applitools SDK for Selenium integration.
 */

class NoFramesException extends EyesException {

    public function __construct($message) {
        parent::__construct($message);
    }
}