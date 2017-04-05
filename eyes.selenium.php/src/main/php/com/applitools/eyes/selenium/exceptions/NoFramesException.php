<?php
/*
 * Applitools SDK for Selenium integration.
 */

namespace Applitools\Exceptions;

class NoFramesException extends EyesException {

    public function __construct($message) {
        parent::__construct($message);
    }
}