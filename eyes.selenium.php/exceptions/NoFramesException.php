<?php
/*
 * Applitools SDK for Selenium integration.
 */

namespace Applitools\Selenium\Exceptions;

use Applitools\Exceptions\EyesException;

class NoFramesException extends EyesException {

    public function __construct($message) {
        parent::__construct($message);
    }
}