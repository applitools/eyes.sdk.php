<?php

namespace Applitools\Exceptions;

/**
 * Encapsulates an error when trying to perform an action using WebDriver.
 */
class EyesDriverOperationException extends EyesException {
    /**
     * Creates an EyesException instance.
     * @param string $message A description of the error.
     * @param \Throwable $e The throwable this exception should wrap.
     */
    public function __construct($message, \Throwable $e) {
        parent::__construct($message, $e);
    }
}
