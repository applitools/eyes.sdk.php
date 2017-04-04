<?php

namespace Applitools;

/**
 * Determines how detected failures are reported.
 */
class FailureReports
{
    /**
     * Failures are reported immediately when they are detected.
     */
    const IMMEDIATE = "IMMEDIATE";

    /**
     * Failures are reported when tests are completed (i.e., when
     * {@link EyesBase#close()} is called).
     */
    const ON_CLOSE = "ON_CLOSE";
}
