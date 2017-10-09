<?php

namespace Applitools\Selenium\fluent;


use Facebook\WebDriver\WebDriverBy;

interface ISeleniumFrameCheckTarget
{
    /**
     * @return int
     */
    function getFrameIndex();

    /**
     * @return string
     */
    function getFrameNameOrId();

    /**
     * @return WebDriverBy
     */
    function getFrameSelector();
}