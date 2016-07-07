<?php
interface ServerConnectorInterface {
    public function setApiKey($apiKey);
    public function getApiKey();

    public function setServerUrl($serverUrl);
    public function getServerUrl();

    public function setProxy($proxySettings);
    public function getProxy();

    /**
     *
     * @return The server timeout. (Seconds).
     */
    public function getTimeout();

    /**
     * Starts a new running session in the agent. Based on the given parameters,
     * this running session will either be linked to an existing session, or to
     * a completely new session.
     *
     * @param sessionStartInfo The start parameters for the session.
     * @return RunningSession object which represents the current running
     *         session
     * @throws EyesException
     */
    public function startSession($sessionStartInfo);

    /**
     * Stops the running session.
     *
     * @param runningSession The running session to be stopped.
     * @return TestResults object for the stopped running session
     * @throws EyesException
     */
    public function stopSession(RunningSession $runningSession, $isAborted, $save);

    /**
     * Matches the current window (held by the WebDriver) to the expected
     * window.
     *
     * @param runningSession The current agent's running session.
     * @param matchData Encapsulation of a capture taken from the application.
     * @return The results of the window matching.
     * @throws EyesException
     */
    public function matchWindow(RunningSession $runningSession, MatchWindowData $matchData);

}
