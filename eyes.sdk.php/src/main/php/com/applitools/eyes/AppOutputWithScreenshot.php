<?php

namespace Applitools;

/**
 * A container for a AppOutput along with the screenshot used for
 * creating it. (We specifically avoid inheritance so we don't have to deal
 * with serialization issues).
 */
class AppOutputWithScreenshot
{
    private $appOutput; //AppOutput
    private $screenshot; //EyesScreenshot

    public function __construct(AppOutput $appOutput, EyesScreenshot $screenshot)
    {
        $this->appOutput = $appOutput;
        $this->screenshot = $screenshot;
    }

    public function getAppOutput()
    {
        return $this->appOutput;
    }

    public function getScreenshot()
    {
        return $this->screenshot;
    }
}