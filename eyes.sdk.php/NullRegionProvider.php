<?php

namespace Applitools;

class NullRegionProvider extends RegionProvider
{
    public function getRegion()
    {
        return Region::getEmpty();
    }

    private static $instance;

    public static function getInstance()
    {
        if (static::$instance == null) {
            static::$instance = new NullRegionProvider();
        }
        return static::$instance;
    }
}