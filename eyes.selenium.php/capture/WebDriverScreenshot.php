<?php
/*
 * Applitools SDK for Selenium integration.
 */

namespace Applitools\Selenium;

/**
 * Encapsulation for the WebDriver wire protocol "screenshot" command response.
 */

// Different browsers return different parameters in addition to "value".

class WebDriverScreenshot
{
    private $value;

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

}