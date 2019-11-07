<?php
/*
* Applitools SDK for Selenium integration.
*/

namespace Applitools;


use Applitools\fluent\ICheckSettings;
use Applitools\fluent\ICheckSettingsInternal;
use Applitools\fluent\IGetRegions;

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
     * @param EyesBase $eyes eyes instance for getting AgentSetup JSON
     * @return MatchResult The match result.
     * @throws Exceptions\EyesException
     * @throws \Exception
     */
    protected function performMatch(
        $userInputs,
        AppOutputWithScreenshot $appOutput,
        $tag, $ignoreMismatch, ImageMatchSettings $imageMatchSettings, EyesBase $eyes = null)
    {
        $this->logger->verbose("performMatch()");
        //Get agent setup
        $agentSetupJsonStr = "";
        if (!empty($eyes)) {
            $this->logger->verbose("Eyes not empty for agent setup retrieve");
            $agentSetup = $eyes->getAgentSetup();
            if (!empty($agentSetup)) {
                $agentSetupJsonStr .= json_encode($agentSetup);
                $this->logger->verbose("AgentSetup: $agentSetupJsonStr");
            }
        }

        // Prepare match data.
        $data = new MatchWindowData($userInputs, $appOutput->getAppOutput(), $tag, $ignoreMismatch,
            new Options($tag, $userInputs, $ignoreMismatch,
                false, false, false,
                $imageMatchSettings), $agentSetupJsonStr);
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
     * @param ICheckSettingsInternal $checkSettingsInternal
     * @param EyesBase $eyes
     * @param int $retryTimeout
     * @return MatchResult Returns the results of the match
     */
    public function matchWindow($userInputs, Region $region, $tag, $shouldMatchWindowRunOnceOnTimeout, $ignoreMismatch,
                                ICheckSettingsInternal $checkSettingsInternal = null, EyesBase $eyes, $retryTimeout)
    {
        if ($retryTimeout === null || $retryTimeout < 0) {
            $retryTimeout = $this->defaultRetryTimeout;
        }

        $this->logger->log("retryTimeout = $retryTimeout");

        $screenshot = $this->takeScreenshot($userInputs, $region, $tag,
            $shouldMatchWindowRunOnceOnTimeout, $ignoreMismatch, $checkSettingsInternal, $eyes, $retryTimeout);

        if ($ignoreMismatch) {
            return $this->matchResult;
        }

        $this->updateLastScreenshot($screenshot);
        $this->updateBounds($region);

        return $this->matchResult;
    }

    /**
     * Build match settings by merging the check settings and the default match settings.
     * @param ICheckSettingsInternal $checkSettingsInternal the settings to match the image by.
     * @param EyesBase eyes the Eyes object to use.
     * @param EyesScreenshot screenshot the Screenshot wrapper object.
     * @return ImageMatchSettings Merged match settings.
     */
    public function createImageMatchSettings(ICheckSettingsInternal $checkSettingsInternal = null,
                                             EyesBase $eyes, EyesScreenshot $screenshot)
    {
        $dms = $eyes->getDefaultMatchSettings();
        $matchLevel = ($checkSettingsInternal != null) ? $checkSettingsInternal->getMatchLevel() : null;
        if ($matchLevel == null) {
            $matchLevel = $dms->getMatchLevel();
        }

        /** @var ImageMatchSettings $imageMatchSettings */
        $imageMatchSettings = new ImageMatchSettings($matchLevel, null);

        $ignoreCaret = ($checkSettingsInternal != null) ? $checkSettingsInternal->getIgnoreCaret() : null;
        if ($ignoreCaret == null) {
            $ignoreCaret = $dms->isIgnoreCaret();
        }

        $imageMatchSettings->setIgnoreCaret($ignoreCaret);

        if ($checkSettingsInternal != null){
            $this->collectSimpleRegions($checkSettingsInternal, $imageMatchSettings, $eyes, $screenshot);
            $this->collectFloatingRegions($checkSettingsInternal, $imageMatchSettings, $eyes, $screenshot);
        }
        return $imageMatchSettings;
    }

    private function collectSimpleRegions(ICheckSettingsInternal $checkSettingsInternal,
                                          ImageMatchSettings $imageMatchSettings, EyesBase $eyes,
                                          EyesScreenshot $screenshot)
    {
        $imageMatchSettings->setIgnoreRegions($this->collectRegions($checkSettingsInternal->getIgnoreRegions(), $eyes, $screenshot));
        $imageMatchSettings->setLayoutRegions($this->collectRegions($checkSettingsInternal->getLayoutRegions(), $eyes, $screenshot));
        $imageMatchSettings->setStrictRegions($this->collectRegions($checkSettingsInternal->getStrictRegions(), $eyes, $screenshot));
        $imageMatchSettings->setContentRegions($this->collectRegions($checkSettingsInternal->getContentRegions(), $eyes, $screenshot));
    }


    /**
     * @param IGetRegions[] $regionsProviders
     * @param EyesBase $eyes
     * @param EyesScreenshot $screenshot
     * @return Region[]
     */
    private function collectRegions($regionsProviders, EyesBase $eyes, EyesScreenshot $screenshot)
    {
        /** @var Region[] $regions */
        $regions = [];

        foreach ($regionsProviders as $regionsProvider) {
            $regions = array_merge($regions, $regionsProvider->getRegions($eyes, $screenshot));
        }

        return $regions;
    }

    /**
     * @param ICheckSettingsInternal $checkSettings
     * @param ImageMatchSettings $imageMatchSettings
     * @param EyesBase $eyes
     * @param EyesScreenshot $screenshot
     */
    private function collectFloatingRegions(ICheckSettingsInternal $checkSettings, ImageMatchSettings $imageMatchSettings,
                                            EyesBase $eyes, EyesScreenshot $screenshot)
    {
        /** @var FloatingMatchSettings[] $floatingRegions */
        $floatingRegions = [];

        foreach ($checkSettings->getFloatingRegions() as $floatingRegionProvider) {
            $floatingRegions = array_merge($floatingRegions, $floatingRegionProvider->getRegions($eyes, $screenshot));
        }
        $imageMatchSettings->setFloatingMatchSettings($floatingRegions);
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
                $this->lastScreenshotBounds = Region::CreateFromLTWH(0, 0, imagesx($image), imagesy($image));
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
     * @param ICheckSettingsInternal $checkSettingsInternal
     * @param EyesBase $eyes
     * @param int $retryTimeout
     * @return EyesScreenshot
     */
    private function takeScreenshot($userInputs, Region $region, $tag, $shouldMatchWindowRunOnceOnTimeout,
                                    $ignoreMismatch, ICheckSettingsInternal $checkSettingsInternal = null, EyesBase $eyes,
                                    $retryTimeout)
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
            $screenshot = $this->tryTakeScreenshot($userInputs, $region, $tag, $ignoreMismatch, $checkSettingsInternal, $eyes);
        } else {
            $screenshot = $this->retryTakingScreenshot($userInputs, $region, $tag, $ignoreMismatch, $checkSettingsInternal, $eyes, $retryTimeout);
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
     * @param ICheckSettingsInternal $checkSettingsInternal
     * @param EyesBase $eyes
     * @param int $retryTimeout
     * @return EyesScreenshot
     */
    private function retryTakingScreenshot($userInputs, Region $region, $tag, $ignoreMismatch,
                                           ICheckSettingsInternal $checkSettingsInternal = null, EyesBase $eyes,
                                           $retryTimeout)
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

            $screenshot = $this->tryTakeScreenshot($userInputs, $region, $tag, true, $checkSettingsInternal, $eyes);

            if ($this->matchResult->getAsExpected()) {
                break;
            }

            $retry = microtime(true) - $start;
        }

        // if we're here because we haven't found a match yet, try once more
        if (!$this->matchResult->getAsExpected()) {
            $screenshot = $this->tryTakeScreenshot($userInputs, $region, $tag, $ignoreMismatch, $checkSettingsInternal, $eyes);
        }
        return $screenshot;
    }

    /**
     * @param Trigger[] $userInputs
     * @param Region $region
     * @param string $tag
     * @param bool $ignoreMismatch
     * @param ICheckSettingsInternal $checkSettingsInternal
     * @param EyesBase $eyes
     * @return EyesScreenshot
     * @throws Exceptions\EyesException
     * @throws \Exception
     */
    private function tryTakeScreenshot($userInputs, Region $region, $tag,
                                       $ignoreMismatch,
                                       ICheckSettingsInternal $checkSettingsInternal = null, EyesBase $eyes)
    {
        $appOutput = $this->appOutputProvider->getAppOutput($region, $this->lastScreenshot);
        $screenshot = $appOutput->getScreenshot();
        $matchSettings = $this->createImageMatchSettings($checkSettingsInternal, $eyes, $screenshot);
        $this->matchResult = $this->performMatch($userInputs, $appOutput, $tag, $ignoreMismatch, $matchSettings, $eyes);
        return $screenshot;
    }

}

?>