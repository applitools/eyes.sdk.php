<?php
/*
* Applitools SDK for Selenium integration.
*/
require "../../eyes/eyes.php/eyes.common.php/src/main/php/com/applitools/eyes/MatchWindowData.php";
require "../../eyes/eyes.php/eyes.common.php/src/main/php/com/applitools/eyes/Options.php";


class MatchWindowTask
{

    const MATCH_INTERVAL = 500; // Milliseconds

    private $serverConnector; //ServerConnector
    private $runningSession; //RunningSession
    private $defaultRetryTimeout;
    private $appOutputProvider; // AppOutputProvider


    /**
     * @param logger            A logger instance.
     * @param serverConnector   Our gateway to the agent
     * @param runningSession    The running session in which we should match the
     *                          window
     * @param retryTimeout      The default total time we tell the agent to ignore
     *                          mismatches.
     * @param appOutputProvider A callback for getting the application output
     *                          when performing match
     */
    public function __construct(Logger $logger,
        ServerConnector $serverConnector,
        RunningSession $runningSession, $retryTimeout,
        AppOutputProvider $appOutputProvider)
    {
        ArgumentGuard::notNull($serverConnector, "serverConnector");
        ArgumentGuard::notNull($runningSession, "runningSession");
        ArgumentGuard::greaterThanOrEqualToZero($retryTimeout, "retryTimeout");
        ArgumentGuard::notNull($appOutputProvider, "appOutputProvider");

        $this->logger = $logger;
        $this->serverConnector = $serverConnector;
        $this->runningSession = $runningSession;
        $this->defaultRetryTimeout = $retryTimeout/* 1000*/;
        $this->appOutputProvider = $appOutputProvider;
    }

    /**
     * Creates the match data and calls the server connector matchWindow method.
     *
     * @param userInputs     The user inputs related to the current appOutput.
     * @param appOutput      The application output to be matched.
     * @param tag            Optional tag to be associated with the match (can
     *                       be {@code null}).
     * @param ignoreMismatch Whether to instruct the server to ignore the
     *                       match attempt in case of a mismatch.
     * @return The match result.
     */
    protected function performMatch(/*Trigger[] */
        $userInputs,
        AppOutputWithScreenshot $appOutput,
        $tag, $ignoreMismatch)
    {
        // Prepare match data.
        $data = new MatchWindowData($userInputs, $appOutput->getAppOutput(), $tag, $ignoreMismatch,
            new Options($tag, $userInputs, $ignoreMismatch, false, false, false));
        // Perform match.
        /*return*/ $this->serverConnector->matchWindow($this->runningSession, $data);
    }

    /**
     * Repeatedly obtains an application snapshot and matches it with the next
     * expected output, until a match is found or the timeout expires.
     *
     * @param userInputs                        User input preceding this match.
     * @param lastScreenshot                    The last screenshot matched or
     *                                          not ignored.
     * @param regionProvider                    Window region to capture.
     * @param tag                               Optional tag to be associated with
     *                                          the match (can be {@code null}).
     * @param shouldMatchWindowRunOnceOnTimeout Force a single match attempt at the
     *                                          end of the match timeout.
     * @param ignoreMismatch                    Whether to instruct the server to
     *                                          ignore the match attempt in case
     *                                          of a mismatch.
     * @param retryTimeout                      The amount of time to retry
     *                                          matching in milliseconds or a
     *                                          negative value to use the default
     *                                          retry timeout.
     * @return Returns the results of the match
     */
    public function matchWindow(/*Trigger[]*/$userInputs, EyesScreenshot $lastScreenshot,
        RegionProvider $regionProvider, $tag, $shouldMatchWindowRunOnceOnTimeout, $ignoreMismatch, $retryTimeout)
    {
        if ($retryTimeout < 0) {
            $retryTimeout = $this->defaultRetryTimeout;
        }

        $this->logger->log(sprintf("retryTimeout = %d", $retryTimeout));

        $elapsedTimeStart = microtime(true);

        // If the wait to load time is 0, or "run once" is true,
        // we perform a single check window.
        if (0 == $retryTimeout || $shouldMatchWindowRunOnceOnTimeout) {

            if ($shouldMatchWindowRunOnceOnTimeout) {
                GeneralUtils::sleep($retryTimeout);
            }

            // Getting the screenshot.
            $appOutput = $this->appOutputProvider->getAppOutput($regionProvider, $lastScreenshot);

            $matchResult = $this->performMatch($userInputs, $appOutput, $tag, $ignoreMismatch);

        } else {
            /*
            * We call a "tolerant" match window until we find a match
            * or we timeout, in which case we call a single "strict"
            * match.
            */

            // We intentionally start the timer after(!) taking the screenshot,
            // so less time is "wasted" on the transfer of the image.
            $appOutput = $this->appOutputProvider->getAppOutput($regionProvider, $lastScreenshot);

            // Start the retry timer.
            $start = microtime(true);

            $this->performMatch($userInputs, $appOutput, $tag, true);

            $retry = microtime(true) - $start;
echo "UUUUUUUUUUUUUu->".$retry."------".$retryTimeout."----------";
            // The match retry loop.
            while ($retry < $retryTimeout) {
                echo "UUUUUUUUUUUUUu->".$retry."------".$retryTimeout."----------";
                // Wait before trying again.
                GeneralUtils::sleep(self::MATCH_INTERVAL);

                $appOutput = $this->appOutputProvider->getAppOutput($regionProvider, $lastScreenshot);

                // Notice the ignoreMismatch here is true
                $matchResult = $this->performMatch($userInputs, $appOutput, $tag, true);

                $retry = microtime(true) - $start;
            }

            // if we're here because we haven't found a match yet, try once more
            /* FIXME if (!$matchResult->getAsExpected()) {

                $appOutput = $this->appOutputProvider->getAppOutput($regionProvider,
                    $lastScreenshot);

                $matchResult = $this->performMatch($userInputs, $appOutput, $tag,
                    $ignoreMismatch);
            }*/
        }
        $elapsedTime = (microtime(true) - $elapsedTimeStart);
        $this->logger->log(sprintf("Completed in  %.2f seconds", $elapsedTime));
        //$matchResult->setScreenshot($appOutput/* FIXME now output is a string ->getScreenshot());
        //return $matchResult; //FIXME no return
    }
}
