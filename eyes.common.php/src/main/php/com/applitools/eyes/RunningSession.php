<?php

namespace Applitools;

/**
 * Encapsulates data for the session currently running in the agent.
 */
class RunningSession
{
    private $isNewSession;
    private $id;
    private $url;

    public function __construct()
    {
        $this->isNewSession = false;
    }

    public function getIsNewSession()
    {
        return $this->isNewSession;
    }

    public function setIsNewSession($isNewSession)
    {
        $this->isNewSession = $isNewSession;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }
}