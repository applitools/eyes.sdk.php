<?php

/**
 * Indicates that a test did not pass (i.e., test either failed or is a new
 * test).
 */
class TestFailedException /*extends AssertionError FIXME*/ {

    private $testResults = null; //TestResults

    /**
     * Creates a new TestFailedException instance.
     * @param testResults The results of the current test if available,
     *                      {@code null} otherwise.
     * @param message A description string.
     * @param cause The cause for this exception.
     */
    public function __construct(TestResults $testResults = null, $message, Throwable $cause) {
        parent::__construct($message, $cause);
        $this->testResults = $testResults;
    }

    /**
     * @return The failed test results, or {@code null} if the test has not
     * yet ended (e.g., when thrown due to
     * {@link com.applitools.eyes.FailureReports#IMMEDIATE} settings).
     */
    public function getTestResults() {
        return $this->testResults;
    }
}
