<?php

namespace Applitools;

/**
 * Interface for saving debug screenshots.
 */
abstract class DebugScreenshotsProvider {

    const DEFAULT_PREFIX = "screenshot_";
    const DEFAULT_PATH = "";

    private $prefix;
    private $path;

    public function __construct() {
        $this->prefix = self::DEFAULT_PREFIX;
        $this->path = null;
    }

    public function getPrefix() {
        return $this->prefix;
    }

    public function setPrefix($prefix) {
        $this->prefix = $prefix == null ? self::DEFAULT_PREFIX : $prefix;
    }

    public function setPath($path) {
        if ($path != null) {
            $path = ($path[strlen($path)-1] == "/") ? $path : $path . '/';
        } else {
            $path = self::DEFAULT_PATH;
        }

        $this->path = $path;
    }

    public function getPath() {
        return $this->path;
    }

    abstract public function save($image, $suffix);
}
