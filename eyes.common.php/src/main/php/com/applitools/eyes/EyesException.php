<?php

/**
 * Applitools Eyes Exception.
 */
class EyesException extends Exception
{

    /**
     * Creates an EyesException instance.
     * @param string $message A description of the error.
     * @param int $code Code of the error.
     * @param Exception $previous The throwable this exception should wrap.
     */
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

?>