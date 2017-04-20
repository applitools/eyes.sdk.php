<?php
/*
 * Applitools software.
 */

namespace Applitools;

/***
 * Encapsulates settings for sending Eyes communication via proxy.
 */
class ProxySettings {
    private $uri;
    private $username;
    private $password;

    /**
     *
     * @param string $uri The proxy's URI.
     * @param string $username The username to be sent to the proxy.
     * @param string $password The password to be sent to the proxy.
     */
    public function __construct($uri, $username = null, $password = null) {
        ArgumentGuard::notNull($uri, "uri");
        $this->uri = $uri;
        $this->username = $username;
        $this->password = $password;
    }

    public function getUri() {
        return $this->uri;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getPassword() {
        return $this->password;
    }
}
?>