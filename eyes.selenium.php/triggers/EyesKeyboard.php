<?php
/*
 * Applitools SDK for Selenium integration.
 */
namespace Applitools\Selenium;

use Applitools\ArgumentGuard;
use Applitools\Logger;
use Applitools\Region;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverKeyboard;

/**
 * A wrapper class for Selenium's Keyboard interface, so we can record keyboard events.
 */
class EyesKeyboard implements WebDriverKeyboard {

    /** @var Logger */
    private $logger;

    /** @var EyesWebDriver */
    private $eyesDriver;

    /** @var WebDriverKeyboard */
    private $keyboard;

    public function __construct(Logger $logger, EyesWebDriver $eyesDriver, WebDriverKeyboard $keyboard) {
        ArgumentGuard::notNull($logger, "logger");
        ArgumentGuard::notNull($eyesDriver, "eyesDriver");
        ArgumentGuard::notNull($keyboard, "keyboard");

        $this->logger = $logger;
        $this->eyesDriver = $eyesDriver;
        $this->keyboard = $keyboard;
    }
    
    public function sendKeys($charSequences) {

        $control = Region::getEmpty();

        // We first find the active element to get the region
        $activeElement = $this->eyesDriver->switchTo()->activeElement();

        if ($activeElement instanceof RemoteWebElement) {
            $activeElement = new EyesRemoteWebElement($this->logger, $this->eyesDriver,
                    /*(RemoteWebElement)*/ $activeElement);

            $control = /*(EyesRemoteWebElement)*/$activeElement->getBounds();
        }

        $chars = str_split($charSequences);
        foreach($chars as $keys) {
            $this->eyesDriver->getEyes()->addTextTrigger($control, $keys);
        }

        $this->keyboard->sendKeys($charSequences);
    }

    public function pressKey(/*CharSequence*/ $keyToPress) {
        $this->keyboard->pressKey($keyToPress);
    }

    public function releaseKey(/*CharSequence*/ $keyToRelease) {
        $this->keyboard->releaseKey($keyToRelease);
    }
}
