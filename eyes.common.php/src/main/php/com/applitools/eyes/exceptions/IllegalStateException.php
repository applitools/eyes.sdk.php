<?php

namespace Applitools\Exceptions;

/**
 * Applitools Illegal State Exception.
 */
class IllegalStateException extends EyesException
{
    /**
     * Creates an IllegalStateException instance.
     * @param string $message A description of the error.
     * @param int $code Code of the error.
     * @param \Throwable $previous The throwable this exception should wrap.
     */
    public function __construct($message, $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

?>