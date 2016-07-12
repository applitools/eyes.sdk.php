<?php
require "AppOutputWithScreenshot.php";
require "RegionProvider.php";

class AppOutputProvider
{
    public function getAppOutput(RegionProvider $regionProvider_, EyesScreenshot $lastScreenshot_)
    {
        return new AppOutputWithScreenshot($regionProvider_, $lastScreenshot_);
    }
}