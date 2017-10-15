<?php

namespace Applitools\Exceptions;

use Applitools\TestResults;

class DiffsFoundException extends TestFailedException
{
    /**
     * Creates a new DiffsFoundException instance.
     * @param TestResults $results The test results if available, {@code null} otherwise.
     * @param string $message A description string.
     */
    public function __construct(TestResults $results, $message) {
        parent::__construct($results, $message);
    }
}