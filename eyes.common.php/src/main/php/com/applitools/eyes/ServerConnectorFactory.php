<?php
require "ServerConnector.php";
/**
 * Encapsulates creation of a conenctivity provider.
 */
class ServerConnectorFactory
{
    /***
     *
     * @param logger A logger instance.
     * @param sdkName An identifier for the current agent. Can be any string.
     * @param serverUrl The URI of the Eyes server.
     * @return ServerConnector object which represents the current connect
     */
    public static function create($logger, $sdkName, $serverUrl)
    {
        return new ServerConnector($logger, $sdkName, $serverUrl);
    }
}