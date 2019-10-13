<?php

namespace Applitools;

use Applitools\Exceptions\EyesException;
use Exception;

class ServerConnector implements ServerConnectorInterface
{

    const TIMEOUT = 300000; // 5 Minutes
    const API_PATH = "/api/sessions/running";
    const DEFAULT_CHARSET_NAME = "UTF-8";

    protected $sdkName;
    private $apiKey;
    private $endPoint;

    /** @var ProxySettings */
    private $proxySettings;

    private $serverUrl;
    private $logger;
    private $ch;

    public function __construct(Logger $logger, $sdkName, $serverUrl)
    {
        $this->logger = $logger;
        $this->sdkName = $sdkName;
        $this->serverUrl = $serverUrl;
        $this->endPoint = $serverUrl . self::API_PATH;
    }

    /**
     * Sets the proxy settings to be used by the rest client.
     * @param ProxySettings $proxySettings The proxy settings to be used by the rest client.
     * If {@code null} then no proxy is set.
     */
    public function setProxy(ProxySettings $proxySettings)
    {
        $this->proxySettings = $proxySettings;
        // After the server is updated we must make sure the endpoint refers
        // to the correct path.
        //endPoint = endPoint.path(API_PATH);   ////????
    }

    /**
     *
     * @return ProxySettings The current proxy settings used by the rest client,
     * or {@code null} if no proxy is set.
     */

    public function getProxy()
    {
        return $this->proxySettings;
    }


    /**
     * Sets the current server URL used by the rest client.
     * @param string $serverUrl The URI of the rest server.
     */
    public function setServerUrl($serverUrl)
    {
        $this->serverUrl = $serverUrl;
        // After the server is updated we must make sure the endpoint refers
        // to the correct path.
        $this->endPoint = $this->serverUrl . self::API_PATH;
    }

    /**
     * @return string The URI of the eyes server.
     */
    public function getServerUrl()
    {
        return $this->serverUrl;
    }


    /**
     * Sets the API key of your applitools Eyes account.
     *
     * @param string $apiKey The api key to set.
     */
    public function setApiKey($apiKey)
    {
        //ArgumentGuard.notNull(apiKey, "apiKey");
        $this->apiKey = $apiKey;
    }

    /**
     * @return string The currently set API key or {@code null} if no key is set.
     */
    public function getApiKey()
    {
        return isset($this->apiKey) ? $this->apiKey : (isset($_SERVER["APPLITOOLS_API_KEY"]) ? $_SERVER["APPLITOOLS_API_KEY"] : null);
    }


    public function getTimeout()
    {
        // TODO: Implement getTimeout() method.
    }


    /**
     * Starts a new running session in the agent. Based on the given parameters,
     * this running session will either be linked to an existing session, or to
     * a completely new session.
     *
     * @param SessionStartInfo $sessionStartInfo The start parameters for the session.
     * @return RunningSession object which represents the current running session
     * @throws \Exception
     */
    public function startSession(SessionStartInfo $sessionStartInfo)
    {

        ArgumentGuard::notNull($sessionStartInfo, "sessionStartInfo");
        $params = [
            'startInfo' => [
                "appIdOrName" => $sessionStartInfo->getAppIdOrName(),
                "scenarioIdOrName" => $sessionStartInfo->getScenarioIdOrName(),
                "batchInfo" => $sessionStartInfo->getBatchInfo(),
                "environment" => [
                    "os" => $sessionStartInfo->getEnvironment()->getOs(),
                    "hostingApp" => $sessionStartInfo->getEnvironment()->getHostingApp(),
                    "inferred" => $sessionStartInfo->getEnvironment()->getInferred(),
                    "displaySize" => [
                        "width" => $sessionStartInfo->getEnvironment()->getDisplaySize()->getWidth(),
                        "height" => $sessionStartInfo->getEnvironment()->getDisplaySize()->getHeight()
                    ]
                ],
                "branchName" => $sessionStartInfo->getBranchName(),
                "parentBranchName" => $sessionStartInfo->getParentBranchName(),
                "matchLevel" => $sessionStartInfo->getDefaultMatchSettings()->getMatchLevel(),
                "agentId" => $sessionStartInfo->getAgentId(),
                "properties" => $sessionStartInfo->getProperties()
            ]
        ];
        $params = json_encode($params);

        try {
            $this->ch = curl_init();
            curl_setopt($this->ch, CURLOPT_URL, "{$this->endPoint}?apiKey={$this->getApiKey()}");

            if ($this->proxySettings != null) {
                curl_setopt($this->ch, CURLOPT_PROXY, $this->proxySettings->getUri());
            }

            curl_setopt($this->ch, CURLOPT_POST, 1);
            curl_setopt($this->ch, CURLINFO_HEADER_OUT, true);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                    'Accept: application/json',
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($params),
                )
            );
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($this->ch);
            $information = curl_getinfo($this->ch);
            /*    $response = endPoint.queryParam("apiKey", apiKey).
                    accept(MediaType.APPLICATION_JSON).
                    entity(postData, MediaType.APPLICATION_JSON_TYPE).
                    post(ClientResponse.class);*/
        } catch (\RuntimeException $e) {
            $this->logger->verbose("startSession(): Server request failed: " . $e->getMessage());
            throw $e;
        }
        $validStatusCodes = array('200', '201');
        if (!in_array($information['http_code'], $validStatusCodes)) {
            throw new \Exception('Invalid status code.');
        } else {
            $result = json_decode($result, true);
            $runningSession = new RunningSession();
            //FIXME
            $runningSession->setId($result['id']);
            $runningSession->setUrl($result['url']);
        }

        if ($information['http_code'] == 201) {
            $runningSession->setIsNewSession(true);
        }

        return $runningSession;
    }

    /**
     * Matches the current window (held by the WebDriver) to the expected
     * window.
     *
     * @param RunningSession $runningSession The current agent's running session.
     * @param MatchWindowData $matchData Encapsulation of a capture taken from the application.
     * @return MatchResult The results of the window matching.
     * @throws EyesException For invalid status codes, or response parsing failed.
     * @throws \Exception
     */
    public function matchWindow(RunningSession $runningSession, MatchWindowData $matchData)
    {
        ArgumentGuard::notNull($runningSession, "runningSession");
        ArgumentGuard::notNull($matchData, "data");

        $base64data = $matchData->getAppOutput()->getScreenshot64();
        $imageData = base64_decode($base64data);

        if ($imageData == false) {
            $this->logger->log("base64 data: $base64data");
        }

        $this->logger->log("base64 data length: " . strlen($base64data));
        $this->logger->log("image data length: " . strlen($imageData));

        $runningSessionsEndpoint = $this->endPoint . '/' . $runningSession->getId() . "?apiKey=" . $this->getApiKey();

        $options = $matchData->getOptions();
        $matchSettings = $options->getImageMatchSettings();

        try {
            $params = [
                'appOutput' => [
                    "title" => $matchData->getAppOutput()->getTitle()
                ],
                "tag" => $matchData->getTag(),
                "ignoreMismatch" => $matchData->getIgnoreMismatch(),
                "options" => [
                    "name" => $options->getName(),
                    "forceMatch" => $options->getForceMatch(),
                    "forceMismatch" => $options->getForceMismatch(),
                    "ignoreMatch" => $options->getIgnoreMatch(),
                    "ignoreMismatch" => $options->getIgnoreMismatch(),
                    "imageMatchSettings" => $this->getImageMatchSettingsAsFormattedArray($matchSettings),
                    "userInputs" => $options->getUserInputsAsFormattedArray()
                ],
                "userInputs" => [],
                "agentSetup" => $matchData->getAgentSetupStr()
            ];
            $json = json_encode($params);

            $this->logger->verbose($json);

            $params = pack('N', strlen($json)) . $json . $imageData;

            curl_reset($this->ch);
            curl_setopt($this->ch, CURLOPT_URL, $runningSessionsEndpoint);

            if ($this->proxySettings != null) {
                curl_setopt($this->ch, CURLOPT_PROXY, $this->proxySettings->getUri());
            }

            curl_setopt($this->ch, CURLOPT_POST, 1);
            curl_setopt($this->ch, CURLINFO_HEADER_OUT, true);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                    'Accept: application/json',
                    'Content-Type: application/octet-stream',
                    'Content-Length: ' . strlen($params),
                )
            );

            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($this->ch);
            $information = curl_getinfo($this->ch);

            $validStatusCodes = array('200', '201');
            if (in_array($information['http_code'], $validStatusCodes)) {
                $this->logger->verbose("matchWindow(): Server request success.");
            } else {
                $this->logger->verbose("matchWindow(): Server request failed. Code: " . $information['http_code']);
                throw new Exception('Invalid status code. Code: ' . $information['http_code']);
            }
            $result = new MatchResult();
            if (!empty($response)) {
                $res = json_decode($response);
                $result->setAsExpected($res->asExpected == "true" ? true : false);
            }

        } catch (Exception $e) {
            $this->logger->log("Failed sending checkWindow request. code: {$e->getCode()}. message: {$e->getMessage()}");
            throw new EyesException("Failed sending checkWindow request!", $e->getCode(), $e);
        }
        return $result;
    }

    /**
     * @param RunningSession $runningSession
     * @param bool $isAborted
     * @param bool $save
     * @return TestResults
     * @throws Exception
     */
    public function stopSession(RunningSession $runningSession, $isAborted, $save)
    {
        ArgumentGuard::notNull($runningSession, "runningSession");

        $runningSessionsEndpoint = $this->endPoint . '/' . $runningSession->getId() . "?apiKey=" . $this->getApiKey();

        $isAbortedStr = $isAborted ? 'true' : 'false';
        $saveStr = $save ? 'true' : 'false';

        curl_reset($this->ch);
        curl_setopt($this->ch, CURLOPT_URL, "{$runningSessionsEndpoint}&aborted={$isAbortedStr}&updateBaseline={$saveStr}");

        if ($this->proxySettings != null) {
            curl_setopt($this->ch, CURLOPT_PROXY, $this->proxySettings->getUri());
        }

        curl_setopt($this->ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'Accept: application/json',
                'Content-Type: application/json',
            )
        );
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($this->ch);
        $information = curl_getinfo($this->ch);
        $validStatusCodes = array('200', '201');
        if (in_array($information['http_code'], $validStatusCodes)) {
            $this->logger->verbose("stopSession(): Session was stopped");
        } else {
            $this->logger->verbose("stopSession(): status " . $information['http_code'] . ". Closing was failed");
            throw new \Exception('Invalid status code.');
        }
        curl_close($this->ch);
        //FIXME may be need to use parseResponseWithJsonData for preparing result
        return new TestResults(json_decode($server_output, true));
    }

    private function getImageMatchSettingsAsFormattedArray(ImageMatchSettings $matchSettings)
    {
        $retVal = [
            "matchLevel" => $matchSettings->getMatchLevel(),
            "ignoreCaret" => $matchSettings->isIgnoreCaret(),
            "ignore" => $matchSettings->getRegionsAsFormattedArray($matchSettings->getIgnoreRegions()),
            "layout" => $matchSettings->getRegionsAsFormattedArray($matchSettings->getLayoutRegions()),
            "strict" => $matchSettings->getRegionsAsFormattedArray($matchSettings->getStrictRegions()),
            //"exact" => $matchSettings->getRegionsAsFormattedArray($matchSettings->getExactRegions()),
            "content" => $matchSettings->getRegionsAsFormattedArray($matchSettings->getContentRegions()),
            "floating" => $matchSettings->getFloatingMatchSettingsAsFormattedArray()
        ];

        $exact = $matchSettings->getExact();
        if ($exact != null) {
            $retVal["exact"] = [
                "minDiffIntensity" => $exact->getMinDiffIntensity(),
                "minDiffWidth" => $exact->getMinDiffWidth(),
                "minDiffHeight" => $exact->getMinDiffHeight(),
                "matchThreshold" => $exact->getMatchThreshold()
            ];
        }

        return $retVal;
    }
}
