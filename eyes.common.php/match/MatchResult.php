<?php
/*
* Applitools SDK for Selenium integration.
*/

namespace Applitools;

/**
 * The result of a window match by the agent.
 */
//@JsonIgnoreProperties({"$id", "screenshot"})
class MatchResult
{

    private $asExpected = false;
    private $windowId;

    /** @var EyesScreenshot */
    private $screenshot;

    public function __construct()
    {
    }

    /**
     * @return bool
     */
    public function getAsExpected()
    {
        return $this->asExpected;
    }

    /**
     * @param bool $asExpected
     */
    public function setAsExpected($asExpected)
    {
        $this->asExpected = $asExpected;
    }

    /**
     * @return EyesScreenshot
     */
    public function getScreenshot()
    {
        return $this->screenshot;
    }

    /**
     * @param EyesScreenshot $screenshot
     */
    public function setScreenshot(EyesScreenshot $screenshot)
    {
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