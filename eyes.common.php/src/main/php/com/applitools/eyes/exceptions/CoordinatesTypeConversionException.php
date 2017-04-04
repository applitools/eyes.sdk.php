<?php

namespace Applitools\Exceptions;

use Throwable;

class CoordinatesTypeConversionException extends EyesException
{
    /**
     * Creates an CoordinatesTypeConversionException instance.
     * @param string $message A description of the error.
     * @param int $code Code of the error.
     * @param Throwable $previous The throwable this exception should wrap.
     */
    public function __construct($message, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

?>