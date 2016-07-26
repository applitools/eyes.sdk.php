<?php

class BufferedImage //FIXME
{
    private $width;
    private $height;
    private $type;

    public function __construct($width, $height, $type)
    {
        $this->width = $width;
        $this->height = $height;
        $this->type = $type;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }
}
