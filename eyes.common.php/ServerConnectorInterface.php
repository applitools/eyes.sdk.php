<?php

namespace Applitools;

use Applitools\Exceptions\EyesException;

interface ServerConnectorInterface
{
    public function setApiKey($apiKey);

    public function getApiKey();

    public function setServerUrl($serverUrl);

    public function getServerUrl();

    public function setProxy(ProxySettings $proxySettings);

    public function getProxy();

    /**
     *
     * @return int The server timeout. (Seconds).
     */
    public function getTimeout();

    /**
     * Starts a new running session in the agent. Based on the given parameters,
     * this running session will either be linked to an existing session, or to
     * a completely new session.
     *
     * @param SessionStartInfo $sessionStartInfo The start parameters for the session.
     * @return RunningSession object which represents the current running session
     * @throws EyesException
     */
    public function startSession(SessionStartInfo $sessionStartInfo);

    /**
     * Stops the running session.
     *
     * @param RunningSession $runningSession The running session to be stopped.
     * @param bool $isAborted
     * @param bool $save
     * @return TestResults object for the stopped running session
     */
    public function stopSession(RunningSession $runningSession, $isAborted, $save);

    /**
     * Matches the current window (held by the WebDriver) to the expected
     * window.
     *
     * @param RunningSession $runningSession The current agent's running session.
     * @param MatchWindowData $matchData Encapsulation of a capture taken from the application.
     * @return MatchResult The results of the window matching.
     * @throws EyesException
     */
    public function matchWindow(RunningSession $runningSession, MatchWindowData $matchData);

}
