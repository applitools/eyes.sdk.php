<?php
require "ServerConnectorInterface.php";
require "RunningSession.php";

class ServerConnector implements ServerConnectorInterface
{

    const TIMEOUT = 300000; // 5 Minutes
    const API_PATH = "/api/sessions/running";
    const DEFAULT_CHARSET_NAME = "UTF-8";

    protected $sdkName;
    private $apiKey;
    private $endPoint;
    private $proxySettings;
    private $serverUrl;
    private $logger;
    private $ch;

    public function __construct(Logger $logger, $sdkName, $serverUrl)
    {
        $this->logger = $logger;
        $this->sdkName = $sdkName;
        $this->serverUrl = $serverUrl;
        $this->endPoint = $serverUrl.self::API_PATH;
    }

    /**
     * Sets the proxy settings to be used by the rest client.
     * @param proxySettings The proxy settings to be used by the rest client.
     * If {@code null} then no proxy is set.
     */
    public function setProxy($proxySettings)
    {
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

    public function getProxy()
    {
        return $this->ProxySettins;
    }


    /**
     * Sets the current server URL used by the rest client.
     * @param serverUrl The URI of the rest server.
     */
    public function setServerUrl($serverUrl)
    {
        $this->serverUrl = $serverUrl;
        // After the server is updated we must make sure the endpoint refers
        // to the correct path.
        $this->endPoint = $this->serverUrl . self::API_PATH;
    }

    /**
     * @return The URI of the eyes server.
     */
    public function getServerUrl()
    {
        return $this->serverUrl;
    }


    /**
     * Sets the API key of your applitools Eyes account.
     *
     * @param apiKey The api key to set.
     */
    public function setApiKey($apiKey)
    {
        //ArgumentGuard.notNull(apiKey, "apiKey");
        $this->apiKey = $apiKey;
    }

    /**
     * @return The currently set API key or {@code null} if no key is set.
     */
    public function getApiKey()
    {
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
    public function startSession($sessionStartInfo)
    {

        ArgumentGuard::notNull($sessionStartInfo, "sessionStartInfo");

      /*  $postData;
        $response;
        $statusCode;
        $validStatusCodes = array();
        $isNewSession;
        $runningSession;*/

        $params = [
            'startInfo' => [
                "appIdOrName" => $sessionStartInfo->getAppIdOrName(),
                "scenarioIdOrName" => $sessionStartInfo->getScenarioIdOrName(),
                "batchInfo" => $sessionStartInfo->getBatchInfo(),
                "environment" => ["displaySize" => $sessionStartInfo->getEnvironment()->getDisplaySize()],
                "matchLevel" => "Strict",
                "agentId" => $sessionStartInfo->getAgentId()
            ]
        ];
        $params = json_encode($params);

/*
        try {
//FIXME
            // since the web API requires a root property for this message
            //jsonMapper.configure(SerializationFeature.WRAP_ROOT_VALUE, true); ?????
            //$postData = jsonMapper.writeValueAsString(sessionStartInfo);

            // returning the root property addition back to false (default)
            //jsonMapper.configure(SerializationFeature.WRAP_ROOT_VALUE, false);
        } catch (Exception $e) { ///use IOException
            throw new Exception("Failed to convert " .    //eyesException
                "sessionStartInfo into Json string!", $e);
        }
*/
        try {
            $this->ch = curl_init();
            curl_setopt($this->ch, CURLOPT_URL, "https://eyessdk.applitools.com/api/sessions/running.json?apiKey=" . $this->apiKey);
            curl_setopt($this->ch, CURLOPT_POST, 1);
            curl_setopt($this->ch, CURLINFO_HEADER_OUT, true);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
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
        } catch (Exception $e) {  //RuntimeException
            $this->logger->verbose("startSession(): Server request failed: " . $e->getMessage());
            throw $e;
        }
        $validStatusCodes = array('200', '201');
        if (!in_array($information['http_code'], $validStatusCodes)) {
            $runningSession = null;
        } else {
            $result = json_decode($result, true);
            $runningSession = new RunningSession();
            $runningSession->setId($result['id']);
            //$runningSession->setId($result['id']);
        }

        // Ok, let's create the running session from the response

        //validStatusCodes.add(ClientResponse.Status.OK.getStatusCode());
        //validStatusCodes.add(ClientResponse.Status.CREATED.getStatusCode());

        //$runningSession = parseResponseWithJsonData(response, validStatusCodes, RunningSession.class);

        // If this is a new session, we set this flag.
        //statusCode = response.getStatus();
        //isNewSession = (statusCode == ClientResponse.Status.CREATED.getStatusCode());
        //runningSession.setIsNewSession(isNewSession);

        return $runningSession;
    }

    /**
     * Matches the current window (held by the WebDriver) to the expected
     * window.
     *
     * @param runningSession The current agent's running session.
     * @param matchData Encapsulation of a capture taken from the application.
     * @return The results of the window matching.
     * @throws EyesException For invalid status codes, or response parsing
     * failed.
     */
    public function matchWindow(RunningSession $runningSession, MatchWindowData $matchData)
    {
        ArgumentGuard::notNull($runningSession, "runningSession");
        ArgumentGuard::notNull($matchData, "data");
//FIXME
        $validStatusCodes = array();

        // since we rather not add an empty "tag" param
        //FIXME need to use ->path, not concatenation "."
        $runningSessionsEndpoint = $this->endPoint .'/'. $runningSession->getId().".json";
        // Serializing data into JSON (we'll treat it as binary later).
        // IMPORTANT This serializes everything EXCEPT for the screenshot (which
        // we'll add later).
       /* try {  FIXME
            $jsonData = json_encode($matchData);
        } catch (IOException $e) {
            throw new EyesException("Failed to serialize data for matchWindow!", $e);
        }

        // Convert the JSON to binary.
        $jsonToBytesConverter = new ByteArrayOutputStream();
        try {
            $jsonToBytesConverter->write(
                $jsonData->getBytes(DEFAULT_CHARSET_NAME));
            $jsonToBytesConverter->flush();
            $jsonBytes = $jsonToBytesConverter->toByteArray();
        } catch (IOException $e) {
            throw new EyesException("Failed create binary data from JSON!", $e);
        }

        // Getting the screenshot's bytes (notice this can be either
        // compressed/uncompressed form).
        $screenshot = json_decode(
        $matchData->getAppOutput()->getScreenshot64());

        // Ok, let's create the request data
        $requestOutputStream = new ByteArrayOutputStream();
        $requestDos = new DataOutputStream($requestOutputStream);
        */ try {
          /*  $requestDos->writeInt($jsonBytes->length);
            $requestDos->flush();
            $requestOutputStream->write($jsonBytes);
            $requestOutputStream->write($screenshot);
            $requestOutputStream->flush();

            // Ok, get the data bytes
            $requestData = $requestOutputStream->toByteArray();

            // Release the streams
            $requestDos->close();*/

            $params = [
                'appOutput' => [
                    "title" => $matchData->getAppOutput()->getTitle(),
                    "screenshot64" => base64_encode($matchData->getAppOutput()->getScreenshot64()->getImage())
                ],
                "tag" => $matchData->getTag(),
                "ignoreMismatch" => false,
            ];
            $params = json_encode($params);


            curl_setopt($this->ch, CURLOPT_URL,"https://eyessdk.applitools.com/api/sessions/running/".$runningSession->getId().".json?apiKey=".$this->apiKey);
            curl_setopt($this->ch, CURLOPT_POST, 1);
            curl_setopt($this->ch, CURLINFO_HEADER_OUT, true);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($params),
                )
            );

            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($this->ch);
        //$information = curl_getinfo($this->ch);

        } catch (IOException $e) {
            throw new EyesException("Failed send check window request!", $e);
        }
/*
        // Sending the request
        $response = $runningSessionsEndpoint->queryParam("apiKey", $this->apiKey)->
            request(MediaType::APPLICATION_JSON).
            post(Entity::entity($requestData,
                    MediaType::APPLICATION_OCTET_STREAM));

        // Ok, let's create the running session from the response
        $validStatusCodes = new ArrayList<>(1);
        $validStatusCodes->add(Response::Status/*.OK.getStatusCode());

        $result = parseResponseWithJsonData($response, $validStatusCodes,
                MatchResult::class);

        return $result;*/

    }

    public function stopSession(RunningSession $runningSession, $isAborted, $save)
    {
        ArgumentGuard::notNull($runningSession, "runningSession");
        //FIXME code not related to Java. need to implement enother 

        curl_setopt($this->ch, CURLOPT_URL,"https://eyessdk.applitools.com/api/sessions/running/".$runningSession->getId().".json?isAborted=false&updateBaseline=false&apiKey=".$this->apiKey);
        curl_setopt($this->ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
            )
        );
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec ($this->ch);
        $information = curl_getinfo($this->ch);
        if($information['http_code'] == 200){
            $this->logger->verbose("stopSession(): status 200. Session was stopped");
        }else{
            $this->logger->verbose("stopSession(): status ".$information['http_code'] . ". Need to check");
        }
        curl_close ($this->ch);

    }

}

