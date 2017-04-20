<?php

namespace Applitools;

/**
 * Encapsulates creation of a connectivity provider.
 */
class ServerConnectorFactory
{
    /***
     *
     * @param Logger $logger A logger instance.
     * @param string $sdkName An identifier for the current agent. Can be any string.
     * @param string $serverUrl The URI of the Eyes server.
     * @return ServerConnector ServerConnector object which represents the current connect
     */
    public static function create(Logger $logger, $sdkName, $serverUrl)
    {
        return new ServerConnector($logger, $sdkName, $serverUrl);
    }
}