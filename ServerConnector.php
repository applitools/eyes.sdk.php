<?php
require "ServerConnectorInterface.php";
class ServerConnector implements ServerConnectorInterface{

    const TIMEOUT = 300000; // 5 Minutes
    const API_PATH = "/api/sessions/running";
    const DEFAULT_CHARSET_NAME = "UTF-8";

    protected $sdkName;
    private $apiKey;
    private $endPoint;
    private $proxySettings;
    private $serverUrl;

    /**
     * Sets the proxy settings to be used by the rest client.
     * @param proxySettings The proxy settings to be used by the rest client.
     * If {@code null} then no proxy is set.
     */
    public function setProxy($proxySettings) {
        $this->proxySettings = $proxySettings;
            // After the server is updated we must make sure the endpoint refers
            // to the correct path.
        //endPoint = endPoint.path(API_PATH);   ////????
    }

    /**
     *
     * @return The current proxy settings used by the rest client,
     * or {@code null} if no proxy is set.
     */

    public function getProxy() {
        return $this->ProxySettins;
    }


    /**
     * Sets the current server URL used by the rest client.
     * @param serverUrl The URI of the rest server.
     */
    public function setServerUrl($serverUrl) {
        $this->serverUrl = $serverUrl;
            // After the server is updated we must make sure the endpoint refers
            // to the correct path.
        //$this->endPoint = $this->serverUrl . self::API_PATH;   ////????????
    }

    /**
     * @return The URI of the eyes server.
     */
    public function getServerUrl() {
        return $this->serverUrl;
    }


    /**
     * Sets the API key of your applitools Eyes account.
     *
     * @param apiKey The api key to set.
     */
    public function setApiKey($apiKey) {
        //ArgumentGuard.notNull(apiKey, "apiKey");
        $this->apiKey = $apiKey;
    }

    /**
     * @return The currently set API key or {@code null} if no key is set.
     */
    public function getApiKey() {
        return $this->apiKey;
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
     * @param sessionStartInfo The start parameters for the session.
     * @return RunningSession object which represents the current running
     *         session
     * @throws EyesException
     */
    public function startSession($sessionStartInfo){

        //ArgumentGuard.notNull(sessionStartInfo, "sessionStartInfo");

        $postData;
        $response;
        $statusCode;
        $validStatusCodes = array();
        $isNewSession;
        $runningSession;

        $params = [
            'startInfo' => [
                "appIdOrName" => $sessionStartInfo->getAppIdOrName(),
                "scenarioIdOrName" => $sessionStartInfo->getScenarioIdOrName(),
                "batchInfo" => $sessionStartInfo->getbatchInfo(),
                "environment" => $sessionStartInfo->getEnvironment(),
                "matchLevel" => "Strict",
                "agentId" => $sessionStartInfo->getAgentId()
            ]
        ];
        $params = json_encode($params);


        try {

                // since the web API requires a root property for this message
            //jsonMapper.configure(SerializationFeature.WRAP_ROOT_VALUE, true); ?????
            //$postData = jsonMapper.writeValueAsString(sessionStartInfo);

                // returning the root property addition back to false (default)
            //jsonMapper.configure(SerializationFeature.WRAP_ROOT_VALUE, false);
        } catch (Exception $e) { ///use IOException
            throw new Exception("Failed to convert " .    //eyesException
                    "sessionStartInfo into Json string!", $e);
        }

        try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,"https://eyessdk.applitools.com/api/sessions/running.json?apiKey=".$this->apiKey);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($params),
                    )
                );
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $serverOutput = curl_exec ($ch);
                $information = curl_getinfo($ch);
            /*    $response = endPoint.queryParam("apiKey", apiKey).
                    accept(MediaType.APPLICATION_JSON).
                    entity(postData, MediaType.APPLICATION_JSON_TYPE).
                    post(ClientResponse.class);*/
        } catch (Exception $e) {  //RuntimeException
                //logger.log("startSession(): Server request failed: " + e.getMessage());
            throw $e;
        }

echo "<pre>"; print_r($information); echo "</pre>";
die("Stop.Session should be started");
        // Ok, let's create the running session from the response
        $validStatusCodes = array();
        //validStatusCodes.add(ClientResponse.Status.OK.getStatusCode());
        //validStatusCodes.add(ClientResponse.Status.CREATED.getStatusCode());

        //runningSession = parseResponseWithJsonData(response, validStatusCodes, RunningSession.class);

        // If this is a new session, we set this flag.
        //statusCode = response.getStatus();
        //isNewSession = (statusCode == ClientResponse.Status.CREATED.getStatusCode());
        //runningSession.setIsNewSession(isNewSession);

        return $runningSession;
    }

    public function matchWindow(RunningSession $runningSession, MatchWindowData $matchData)
    {
        // TODO: Implement matchWindow() method.
    }

    public function stopSession(RunningSession $runningSession, $isAborted, $save)
    {
        // TODO: Implement stopSession() method.
    }

}

