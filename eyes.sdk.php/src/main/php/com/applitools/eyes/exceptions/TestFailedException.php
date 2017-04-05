<?php

namespace Applitools\Exceptions;

use Applitools\TestResults;

/**
 * Indicates that a test did not pass (i.e., test either failed or is a new
 * test).
 */
class TestFailedException extends \Exception{

    private $testResults = null; //TestResults

    /**
     * Creates a new TestFailedException instance.
     * @param TestResults $testResults The results of the current test if available,
     *                      {@code null} otherwise.
     * @param string $message A description string.
     * @param \Throwable $previous The cause for this exception.
     */
    public function __construct(TestResults $testResults = null, $message, \Throwable $previous = null) {
        parent::__construct($message, $previous);
        $this->testResults = $testResults;
    }

    /**
     * @return TestResults The failed test results, or {@code null} if the test has not
     * yet ended (e.g., when thrown due to {@link FailureReports#IMMEDIATE} settings).
     */
    public function getTestResults() {
        return $this->testResults;
    }
}

?>