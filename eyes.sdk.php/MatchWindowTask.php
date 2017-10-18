<?php
/*
* Applitools SDK for Selenium integration.
*/

namespace Applitools;


use Applitools\fluent\ICheckSettings;
use Applitools\fluent\ICheckSettingsInternal;

class MatchWindowTask
{

    const MATCH_INTERVAL = 500; // Milliseconds

    /** @var ServerConnector */
    private $serverConnector;

    /** @var RunningSession */
    private $runningSession;

    /** @var int */
    private $defaultRetryTimeout;

    /** @var AppOutputProvider */
    private $appOutputProvider;

    /** @var MatchResult */
    private $matchResult;

    /** @var EyesScreenshot */
    private $lastScreenshot;

    /** @var Region */
    private $lastScreenshotBounds;

    /**
     * @param $logger Logger A logger instance.
     * @param $serverConnector ServerConnector Our gateway to the agent
     * @param $runningSession RunningSession The running session in which we should match the window
     * @param $retryTimeout int The default total time we tell the agent to ignore mismatches.
     * @param $appOutputProvider AppOutputProvider A callback for getting the application output when performing match
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
        $this->defaultRetryTimeout = $retryTimeout/* 1000*/
        ;
        $this->appOutputProvider = $appOutputProvider;
    }

    /**
     * Creates the match data and calls the server connector matchWindow method.
     *
     * @param array $userInputs The user inputs related to the current appOutput.
     * @param AppOutputWithScreenshot $appOutput The application output to be matched.
     * @param string $tag Optional tag to be associated with the match (can be {@code null}).
     * @param bool $ignoreMismatch Whether to instruct the server to ignore the match attempt in case of a mismatch.
     * @param ImageMatchSettings $imageMatchSettings
     * @return MatchResult The match result.
     */
    protected function performMatch(
        $userInputs,
        AppOutputWithScreenshot $appOutput,
        $tag, $ignoreMismatch, ImageMatchSettings $imageMatchSettings)
    {
        // Prepare match data.
        $data = new MatchWindowData($userInputs, $appOutput->getAppOutput(), $tag, $ignoreMismatch,
            new Options($tag, $userInputs, $ignoreMismatch,
                false, false, false,
                $imageMatchSettings));
        // Perform match.
        return $this->serverConnector->matchWindow($this->runningSession, $data);
    }

    /**
     * Repeatedly obtains an application snapshot and matches it with the next
     * expected output, until a match is found or the timeout expires.
     *
     * @param Trigger[] $userInputs User input preceding this match.
     * @param Region $region Window region to capture.
     * @param string $tag Optional tag to be associated with the match (can be {@code null}).
     * @param bool $shouldMatchWindowRunOnceOnTimeout Force a single match attempt at the end of the match timeout.
     * @param bool $ignoreMismatch Whether to instruct the server to ignore the match attempt in case of a mismatch.
     * @param ICheckSettings $checkSettings The check settings to use.
     * @return MatchResult Returns the results of the match
     */
    public function matchWindow($userInputs, Region $region, $tag, $shouldMatchWindowRunOnceOnTimeout, $ignoreMismatch, ICheckSettings $checkSettings)
    {
        ArgumentGuard::notNull($checkSettings, "checkSettings");

        $retryTimeout = -1;
        if ($checkSettings instanceof ICheckSettingsInternal) {
            $retryTimeout = $checkSettings->getTimeout();
        }

        if ($retryTimeout === null || $retryTimeout < 0) {
            $retryTimeout = $this->defaultRetryTimeout;
        }

        $this->logger->log("retryTimeout = $retryTimeout");


        $imageMatchSettings = null; // TODO - implement
        $screenshot = $this->takeScreenshot($userInputs, $region, $tag,
            $shouldMatchWindowRunOnceOnTimeout, $ignoreMismatch, $imageMatchSettings, $retryTimeout);


        if ($ignoreMismatch) {
            return $this->matchResult;
        }

        $this->updateLastScreenshot($screenshot);
        $this->updateBounds($region);

        return $this->matchResult;
    }

    /**
     * @param EyesScreenshot $screenshot
     */
    private function updateLastScreenshot(EyesScreenshot $screenshot)
    {
        if ($screenshot != null) {
            $this->lastScreenshot = $screenshot;
        }
    }

    /**
     * @param Region $region
     */
    private function updateBounds(Region $region)
    {
        if ($region->isEmpty()) {
            if ($this->lastScreenshot == null) {
                // We set an "infinite" image size since we don't know what the screenshot size is...
                $this->lastScreenshotBounds = Region::CreateFromLTWH(0, 0, PHP_INT_MAX, PHP_INT_MAX);
            } else {
                $image = $this->lastScreenshot->getImage();
                $this->lastScreenshotBounds = Region::CreateFromLTWH(0, 0, $image->width(), $image->height());
            }
        } else {
            $this->lastScreenshotBounds = $region;
        }
    }

    /**
     * @param Trigger[] $userInputs
     * @param Region $region
     * @param string $tag
     * @param bool $shouldMatchWindowRunOnceOnTimeout
     * @param bool $ignoreMismatch
     * @param ImageMatchSettings|null $imageMatchSettings
     * @param int $retryTimeout
     * @return EyesScreenshot
     */
    private function takeScreenshot($userInputs, Region $region, $tag, $shouldMatchWindowRunOnceOnTimeout,
                                    $ignoreMismatch, ImageMatchSettings $imageMatchSettings = null, $retryTimeout)
    {
        $elapsedTimeStart = microtime(true);

        /** @var EyesScreenshot $screenshot */
        $screenshot = null;

        // If the wait to load time is 0, or "run once" is true,
        // we perform a single check window.
        if (0 == $retryTimeout || $shouldMatchWindowRunOnceOnTimeout) {

            if ($shouldMatchWindowRunOnceOnTimeout) {
                GeneralUtils::sleep($retryTimeout);
            }
            $screenshot = $this->tryTakeScreenshot($userInputs, $region, $tag, $ignoreMismatch, $imageMatchSettings);
        } else {
            $screenshot = $this->retryTakingScreenshot($userInputs, $region, $tag, $ignoreMismatch, $imageMatchSettings, $retryTimeout);
        }

        $elapsedTime = (microtime(true) - $elapsedTimeStart);

        $this->logger->verbose(sprintf("Completed in %.2f seconds", $elapsedTime));
        //matchResult.setScreenshot(screenshot);
        return $screenshot;
    }

    /**
     * @param Trigger[] $userInputs
     * @param Region $region
     * @param string $tag
     * @param bool $ignoreMismatch
     * @param ImageMatchSettings|null $imageMatchSettings
     * @param int $retryTimeout
     * @return EyesScreenshot
     */
    private function retryTakingScreenshot($userInputs, Region $region, $tag, $ignoreMismatch,
                                           ImageMatchSettings $imageMatchSettings = null, $retryTimeout)
    {
        // Start the retry timer.
        $start = microtime(true);

        /** @var EyesScreenshot */
        $screenshot = null;

        $retry = microtime(true) - $start;

        // The match retry loop.
        while ($retry < $retryTimeout) {

            // Wait before trying again.
            GeneralUtils::sleep(self::MATCH_INTERVAL);

            $screenshot = $this->tryTakeScreenshot($userInputs, $region, $tag, true, $imageMatchSettings);

            if ($this->matchResult->getAsExpected()) {
                break;
            }

            $retry = microtime(true) - $start;
        }

        // if we're here because we haven't found a match yet, try once more
        if (!$this->matchResult->getAsExpected()) {
            $screenshot = $this->tryTakeScreenshot($userInputs, $region, $tag, $ignoreMismatch, $imageMatchSettings);
        }
        return $screenshot;
    }

    /**
     * @param Trigger[] $userInputs
     * @param Region $region
     * @param string $tag
     * @param bool $ignoreMismatch
     * @param ImageMatchSettings|null $imageMatchSettings
     * @return EyesScreenshot
     */
    private function tryTakeScreenshot($userInputs, Region $region, $tag,
                                       $ignoreMismatch, ImageMatchSettings $imageMatchSettings = null)
    {
        $appOutput = $this->appOutputProvider->getAppOutput($region, $this->lastScreenshot);
        $screenshot = $appOutput->getScreenshot();
        $this->matchResult = $this->performMatch($userInputs, $appOutput, $tag, $ignoreMismatch, $imageMatchSettings);
        return $screenshot;
    }

}

?>