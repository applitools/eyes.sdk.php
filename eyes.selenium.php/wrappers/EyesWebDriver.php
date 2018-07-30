<?php

namespace Applitools\Selenium;

use Applitools\ArgumentGuard;
use Applitools\Exceptions\EyesException;
use Applitools\ImageUtils;
use Applitools\Logger;
use Applitools\RectangleSize;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\JavaScriptExecutor;
use Facebook\WebDriver\Remote\RemoteExecuteMethod;
use Facebook\WebDriver\Remote\RemoteTouchScreen;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;

/**
 * An Eyes implementation of the interfaces implemented by
 * {@link org.openqa.selenium.remote.RemoteWebDriver}.
 * Used so we'll be able to return the users an object with the same
 * functionality as {@link org.openqa.selenium.remote.RemoteWebDriver}.
 */
class EyesWebDriver implements WebDriver, JavaScriptExecutor /*HasCapabilities, HasInputDevices,
        FindsByClassName, FindsByCssSelector, FindsById, FindsByLinkText,
        FindsByName, FindsByTagName, FindsByXPath,
        SearchContext, TakesScreenshot, HasTouchScreen */  //FIXME
{

    /** @var Logger */
    private $logger;

    /** @var Eyes */
    private $eyes;

    /** @var RemoteWebDriver */
    private $driver;

    private $touch; //TouchScreen
    private $elementsIds; //Map<String, WebElement>

    /** @var FrameChain */
    private $frameChain;
    private $rotation; //ImageRotation

    /** @var RectangleSize */
    private $defaultContentViewportSize;

    /** @var RemoteExecuteMethod */
    private $executeMethod;

    /**
     * Rotates the image as necessary. The rotation is either manually forced
     * by passing a non-null ImageRotation, or automatically inferred.
     *
     * @param Logger $logger
     * @param WebDriver $driver The underlying driver which produced the screenshot.
     * @param Image $image The image to normalize.
     * @param ImageRotation $rotation The degrees by which to rotate the image:
     *                 positive values = clockwise rotation,
     *                 negative values = counter-clockwise,
     *                 0 = force no rotation, null = rotate automatically
     *                 when needed.
     * @return Image A normalized image.
     */
    public static function normalizeRotation(Logger $logger,
                                             WebDriver $driver,
                                             Image $image,
                                             ImageRotation $rotation)
    {
        ArgumentGuard::notNull($driver, "driver");
        ArgumentGuard::notNull($image, "image");
        $normalizedImage = clone $image;
        if ($rotation != null) {
            if ($rotation->getRotation() != 0) {
                $normalizedImage = ImageUtils::rotateImage($image, $rotation->getRotation());
            }
        } else { // Do automatic rotation if necessary
            try {
                $logger->verbose("Trying to automatically normalize rotation...");
                if (EyesSeleniumUtils::isMobileDevice($driver) &&
                    EyesSeleniumUtils::isLandscapeOrientation($driver)
                    && $image->height() > $image->width()
                ) {
                    // For Android, we need to rotate images to the right, and
                    // for iOS to the left.
                    $degrees = EyesSeleniumUtils::isAndroid($driver) ? 90 : -90;
                    $normalizedImage = ImageUtils::rotateImage($image, $degrees);
                }
            } catch (\Exception $e) {
                $logger->verbose("Got exception: " . $e->getMessage());
                $logger->verbose("Skipped automatic rotation handling.");
            }
        }

        return $normalizedImage;
    }

    public function __construct(Logger $logger, Eyes $eyes, RemoteWebDriver $driver)
    {
        ArgumentGuard::notNull($logger, "logger");
        ArgumentGuard::notNull($eyes, "eyes");
        ArgumentGuard::notNull($driver, "driver");

        $this->logger = $logger;
        $this->eyes = $eyes;
        $this->driver = $driver;
        $this->elementsIds = array();
        $this->frameChain = new FrameChain($logger);
        $this->defaultContentViewportSize = null;

        // initializing "touch" if possible
        $this->executeMethod = null;
        try {
            $this->executeMethod = new RemoteExecuteMethod($driver);
        } catch (\Exception $e) {
            // If an exception occurred, we simply won't instantiate "touch".
        }

        if (null != $this->executeMethod) {
            $this->touch = new EyesTouchScreen($logger, $this, new RemoteTouchScreen($this->executeMethod));
        } else {
            $this->touch = null;
        }

        $logger->verbose("Driver session is " . $this->getSessionId());
    }

    /**
     * @return Eyes
     */
    public function getEyes()
    {
        return $this->eyes;
    }

    public function getRemoteWebDriver()
    {
        return $this->driver;
    }

    public function getTouch()
    {
        return $this->touch;
    }

    /**
     *
     * @return ImageRotation The image rotation data.
     */
    public function getRotation()
    {
        return $this->rotation;
    }

    /**
     *
     * @param ImageRotation $rotation The image rotation data.
     */
    public function setRotation(ImageRotation $rotation)
    {
        $this->rotation = $rotation;
    }

    public function get($s)
    {
        $this->frameChain->clear();
        $this->driver->get($s);
    }

    public function getCurrentUrl()
    {
        return $this->driver->getCurrentUrl();
    }

    public function getTitle()
    {
        return $this->driver->getTitle();
    }

    public function findElements(WebDriverBy $by)
    {
        $foundWebElementsList = $this->driver->findElements($by); //List<WebElement>

        // This list will contain the found elements wrapped with our class.
        $resultElementsList = array(); //new ArrayList<WebElement>(foundWebElementsList->size());

        foreach ($foundWebElementsList as $currentElement) {
            if ($currentElement instanceof RemoteWebElement) {
                $resultElementsList[] = new EyesRemoteWebElement($this->logger, $this,
                    /*(RemoteWebElement)*/
                    $currentElement);

                // For Remote web elements, we can keep the IDs
                $this->elementsIds[$currentElement->getId()] = $currentElement;

            } else {
                throw new EyesException(sprintf("findElements: element is not a RemoteWebElement: %s", $by));
            }
        }

        return $resultElementsList;
    }

    public function findElement(WebDriverBy $by)
    {
        $webElement = $this->driver->findElement($by);

        //$this->driver->switchTo()->frame($webElement);

        if ($webElement instanceof RemoteWebElement) {
            $webElement = new EyesRemoteWebElement($this->logger, $this,
                /*(RemoteWebElement)*/
                $webElement);

            // For Remote web elements, we can keep the IDs,
            // for Id based lookup (mainly used for Javascript related
            // activities).
            $this->elementsIds[$webElement->getId()] = $webElement;
        } else {
            throw new EyesException(sprintf("findElement: Element is not a RemoteWebElement: %s", $by));
        }

        return $webElement;
    }

    /**
     * Found elements are sometimes accessed by their IDs (e.g. tapping an element in Appium).
     * @return array Maps of IDs for found elements.
     */
    public function getElementIds()
    {
        return $this->elementsIds;
    }

    public function getPageSource()
    {
        return $this->driver->getPageSource();
    }

    public function close()
    {
        $this->driver->close();
    }

    public function quit()
    {
        $this->driver->quit();
    }

    public function getWindowHandles()
    {
        return $this->driver->getWindowHandles();
    }

    public function getWindowHandle()
    {
        return $this->driver->getWindowHandle();
    }

    /**
     * @return EyesTargetLocator
     */
    public function switchTo()
    {
        $this->logger->verbose("switchTo()");
        return new EyesTargetLocator($this->logger, $this, $this->driver->switchTo());
    }

    public function navigate()
    {
        return $this->driver->navigate();
    }

    public function manage()
    {
        return $this->driver->manage();
    }

    public function getMouse()
    {
        return new EyesMouse($this->logger, $this, $this->driver->getMouse());
    }

    public function getKeyboard()
    {
        return new EyesKeyboard($this->logger, $this, $this->driver->getKeyboard());
    }

    public function findElementByClassName($className)
    {
        return $this->findElement(WebDriverBy::className($className));
    }

    public function findElementsByClassName($className)
    {
        return $this->findElements(WebDriverBy::className($className));
    }

    public function findElementByCssSelector($cssSelector)
    {
        return $this->findElement(WebDriverBy::cssSelector($cssSelector));
    }

    public function findElementsByCssSelector($cssSelector)
    {
        return $this->findElements(WebDriverBy::cssSelector($cssSelector));
    }

    public function findElementById($id)
    {
        return $this->findElement(WebDriverBy::id($id));
    }

    public function findElementsById($id)
    {
        return $this->findElements(WebDriverBy::id($id));
    }

    public function findElementByLinkText($linkText)
    {
        return $this->findElement(WebDriverBy::linkText($linkText));
    }

    public function findElementsByLinkText($linkText)
    {
        return $this->findElements(WebDriverBy::linkText($linkText));
    }

    public function findElementByPartialLinkText($partialLinkText)
    {
        return $this->findElement(WebDriverBy::partialLinkText($partialLinkText));
    }

    public function findElementsByPartialLinkText($partialLinkText)
    {
        return $this->findElements(WebDriverBy::partialLinkText($partialLinkText));
    }

    public function findElementByName($name)
    {
        return $this->findElement(WebDriverBy::name($name));
    }

    public function findElementsByName($name)
    {
        return $this->findElements(WebDriverBy::name($name));
    }

    public function findElementByTagName($tagName)
    {
        return $this->findElement(WebDriverBy::tagName($tagName));
    }

    public function findElementsByTagName($tagName)
    {
        return $this->findElements(WebDriverBy::tagName($tagName));
    }

    public function findElementByXPath($path)
    {
        return $this->findElement(WebDriverBy::xpath($path));
    }

    public function findElementsByXPath($path)
    {
        return $this->findElements(WebDriverBy::xpath($path));
    }

    public function getCapabilities()
    {
        return $this->driver->getCapabilities();
    }

    /**
     * @param $script
     * @param array $args
     * @return mixed
     */
    public function executeScript($script, array $args = array())
    {
        // Appium commands are sometimes sent as Javascript
        /*if (AppiumJsCommandExtractor::isAppiumJsCommand($script)) {
            $trigger = AppiumJsCommandExtractor::extractTrigger($this->elementsIds,
                $this->driver->manage()->window()->getSize(), $script, $args);

            if ($trigger != null) {
                // TODO - Daniel, additional type of triggers
                if ($trigger instanceof MouseTrigger) {
                    $mt = //(MouseTrigger)
                        clone $trigger;
                    $this->eyes->addMouseTrigger($mt->getMouseAction(),
                        $mt->getControl(), $mt->getLocation());
                }
            }
        }*/ //FIXME
        $this->logger->verbose("Execute script...");
        $result = $this->driver->executeScript($script, $args);
        $this->logger->verbose("Done!");
        return $result;
    }

    public function executeAsyncScript($script, array $args = array())
    {
        // Appium commands are sometimes sent as Javascript
        if (AppiumJsCommandExtractor::isAppiumJsCommand($script)) {
            $trigger = AppiumJsCommandExtractor::extractTrigger($this->elementsIds,
                $this->driver->manage()->window()->getSize(), $script, $args);

            if ($trigger != null) {
                // TODO - Daniel, additional type of triggers
                if ($trigger instanceof MouseTrigger) {
                    $mt = /*(MouseTrigger)*/
                        $trigger;
                    $this->eyes->addMouseTriggerCursor($mt->getMouseAction(), $mt->getControl(), $mt->getLocation());
                }
            }
        }
        return $this->driver->executeAsyncScript($script, $args);
    }


    /**
     * @param bool $forceQuery If true, we will perform the query even if we have a cached viewport size.
     * @return RectangleSize The viewport size of the default content (outer most frame).
     */
    public function getDefaultContentViewportSize($forceQuery = false)
    {
        $this->logger->verbose("getDefaultContentViewportSize()");

        if ($this->defaultContentViewportSize != null && !$forceQuery) {
            $this->logger->verbose("Using cached viewport size: " . json_encode($this->defaultContentViewportSize));
            return $this->defaultContentViewportSize;
        }

        $currentFrames = new FrameChain($this->logger, $this->getFrameChain());

        // Optimization
        if ($currentFrames->size() > 0) {
            $this->switchTo()->defaultContent();
        }

        $this->logger->verbose("Extracting viewport size...");
        $this->defaultContentViewportSize = EyesSeleniumUtils::extractViewportSize($this->logger, $this);
        $this->logger->verbose("Done! Viewport size: " . json_encode($this->defaultContentViewportSize));

        if ($currentFrames->size() > 0) {
            $this->switchTo()->frames($currentFrames);
        }

        return $this->defaultContentViewportSize;
    }

    public function getFrameChain()
    {
        return $this->frameChain;
    }

    public function getScreenshot()
    {
        $image64 = $this->driver->takeScreenshot();
        $image = imagecreatefromstring($image64);
        return $image;
    }

    private function getSessionId()
    {
        // extract remote web driver information
        return $this->driver->getSessionId();
    }

    public function takeScreenshot($save_as = null)
    {
        // TODO: Implement takeScreenshot() method. //FIXME
    }

    public function execute($name, $params)
    {
        // TODO: Implement execute() method. //FIXME
    }

    public function wait($timeout_in_second = 30, $interval_in_millisecond = 250)
    {
        // TODO: Implement wait() method. //FIXME
    }


    public function getUserAgent()
    {
        $userAgent = null;
        try {
            $userAgent = $this->driver->executeScript("return navigator.userAgent");
            $this->logger->verbose("user agent: $userAgent");
        } catch (\Exception $e) {
            $this->logger->verbose("Failed to obtain user-agent string");
            $userAgent = null;
        }
        return $userAgent;
    }

}
