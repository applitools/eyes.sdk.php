<?php
/*
* Applitools SDK for Selenium integration.
*/

/**
 * The result of a window match by the agent.
 */
//@JsonIgnoreProperties({"$id", "screenshot"})
class MatchResult
{

    private $asExpected = false;
    private $windowId;
    private $screenshot; //EyesScreenshot

    public function __construct()
    {
    }

    public function getAsExpected()
    {
        return $this->asExpected;
    }

    public function setAsExpected($asExpected)
    {
        $this->asExpected = $asExpected;
    }

    public function getScreenshot()
    {
        return $this->screenshot;
    }

    public function setScreenshot($screenshot)
    {//EyesScreenshot
        $this->screenshot = $screenshot;
    }

    public function getWindowId()
    {
        return $this->windowId;
    }

    public function setWindowId($windowId)
    {
        $this->windowId = $windowId;
    }

}