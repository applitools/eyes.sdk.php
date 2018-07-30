<?php

namespace Applitools\Selenium\fluent {

    use Facebook\WebDriver\WebDriverBy;
    use Facebook\WebDriver\WebDriverElement;

    interface ISeleniumCheckTarget
    {
        /**
         * @return WebDriverBy The target selector.
         */
        function getTargetSelector();

        /**
         * @return WebDriverElement
         */
        function getTargetElement();

        /**
         * @return FrameLocator[]
         */
        function getFrameChain();
    }
}