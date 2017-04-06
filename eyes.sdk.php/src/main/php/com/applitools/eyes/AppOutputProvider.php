<?php

namespace Applitools;

interface AppOutputProvider
{
    /**
     * @param RegionProvider $regionProvider
     * @param EyesScreenshot $lastScreenshot
     * @return AppOutputWithScreenshot
     */
    public function getAppOutput(RegionProvider $regionProvider, EyesScreenshot $lastScreenshot);
}