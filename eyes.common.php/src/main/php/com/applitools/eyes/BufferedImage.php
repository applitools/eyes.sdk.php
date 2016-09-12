<?php

class BufferedImage //FIXME default class on java
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

    public function getData(){
        return new BufferedImage(0,0,0); //FIXME
    }

    public function getType()
    {
        return $this->type;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getRaster()
    {
        return new BufferedImage(24,25,46); //FIXME
    }

    public function setRect($width, $height, $type){ //FIXME
        $this->width = $width;
        $this->height = $height;
        $this->type = $type;
    }
}
