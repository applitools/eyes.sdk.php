<?php

namespace Applitools;

/**
 * The list of possible scaling methods.
 */
class ScaleMethod
{
    private $method;

    const SPEED = "SPEED";
    const QUALITY = "QUALITY";
    const ULTRA_QUALITY = "ULTRA_QUALITY";


    public static function getDefault()
    {
        $method = new self(self::SPEED);
        return $method;
    }

    function __construct($method)
    {
        $this->method = constant('self::' . $method);
    }

    public function getMethod()
    {
        return $this->method;
    }
}
