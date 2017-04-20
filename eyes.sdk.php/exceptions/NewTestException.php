<?php

namespace Applitools\Exceptions;

use Applitools\TestResults;

/**
 * Indicates that a new test (i.e., a test for which no baseline exists) ended.
 */
class NewTestException extends TestFailedException {

    /**
     * Creates a new NewTestException instance.
     * @param TestResults $results The test results if available, {@code null} otherwise.
     * @param string $message A description string.
     */
    public function __construct(TestResults $results, $message) {
        parent::__construct($results, $message);
    }
}

?>