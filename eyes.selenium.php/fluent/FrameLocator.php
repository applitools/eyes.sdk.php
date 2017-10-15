<?php

namespace Applitools\Selenium\fluent {

    use Facebook\WebDriver\WebDriverBy;

    class FrameLocator implements ISeleniumFrameCheckTarget
    {
        /** @var WebDriverBy */
        private $frameSelector;

        /** @var string */
        private $frameNameOrId;

        /** @var int */
        private $frameIndex;

        /**
         * @param WebDriverBy $frameSelector
         */
        public function setFrameSelector($frameSelector)
        {
            $this->frameSelector = $frameSelector;
        }

        /**
         * @param string $frameNameOrId
         */
        public function setFrameNameOrId($frameNameOrId)
        {
            $this->frameNameOrId = $frameNameOrId;
        }

        /**
         * @param int $frameIndex
         */
        public function setFrameIndex($frameIndex)
        {
            $this->frameIndex = $frameIndex;
        }

        /**
         * @return int
         */
        function getFrameIndex()
        {
            return $this->frameIndex;
        }

        /**
         * @return string
         */
        function getFrameNameOrId()
        {
            return $this->frameNameOrId;
        }

        /**
         * @return WebDriverBy
         */
        function getFrameSelector()
        {
            return $this->frameSelector;
        }
    }
}