<?php

namespace Applitools;

interface AppOutputProvider
{
    /**
     * @param Region $region
     * @param EyesScreenshot $lastScreenshot
     * @return AppOutputWithScreenshot
     */
    public function getAppOutput(Region $region, EyesScreenshot $lastScreenshot);
}