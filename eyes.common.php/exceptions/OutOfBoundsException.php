<?php

namespace Applitools\Exceptions;

use Throwable;

/**
 * Applitools Eyes exception indicating the a geometrical element is out of
 * bounds (point outside a region, region outside another region etc.)
 */
class OutOfBoundsException extends EyesException {
    public function __construct($message, Throwable $e = null) {
        parent::__construct($message, $e);
    }
}

?>