<?php

interface AppOutputProvider
{
    public function getAppOutput(RegionProvider $regionProvider_, EyesScreenshot $lastScreenshot);
}