<?php

/**
 * An application output (title, image, etc).
 */
class AppOutput
{

    /**
     * The title of the screen of the application being captured.
     */
    private $title;
    private $screenshot64;

    /**
     * @param title        The title of the window.
     * @param screenshot64 Base64 encoding of the screenshot's bytes (the
     *                     byte can be in either in compressed or
     *                     uncompressed form)
     */
    public function __construct($title, $screenshot64)
    {
        $this->title = $title;
        $this->screenshot64 = $screenshot64;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getScreenshot64()
    {
        return $this->screenshot64;
    }
}