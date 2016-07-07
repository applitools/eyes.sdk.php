<?php

/**
 * An Eyes implementation of the interfaces implemented by
 * {@link org.openqa.selenium.remote.RemoteWebDriver}.
 * Used so we'll be able to return the users an object with the same
 * functionality as {@link org.openqa.selenium.remote.RemoteWebDriver}.
 */
class EyesWebDriver /*implements HasCapabilities, HasInputDevices,
        FindsByClassName, FindsByCssSelector, FindsById, FindsByLinkText,
        FindsByName, FindsByTagName, FindsByXPath, JavascriptExecutor,
        SearchContext, TakesScreenshot, WebDriver, HasTouchScreen */{

    private $logger; //Logger
    private $eyes; //Eyes
    private $driver; //RemoteWebDriver
    private $touch; //TouchScreen
    private $elementsIds; //Map<String, WebElement>
    private $frameChain; //FrameChain
    private $rotation; //ImageRotation
    private $defaultContentViewportSize; //RectangleSize

    /**
     * Rotates the image as necessary. The rotation is either manually forced
     * by passing a non-null ImageRotation, or automatically inferred.
     *
     * @param driver The underlying driver which produced the screenshot.
     * @param image The image to normalize.
     * @param rotation The degrees by which to rotate the image:
     *                 positive values = clockwise rotation,
     *                 negative values = counter-clockwise,
     *                 0 = force no rotation, null = rotate automatically
     *                 when needed.
     * @return A normalized image.
     */
    public static function normalizeRotation(Logger $logger,
                                                  WebDriver $driver,
                                                  BufferedImage $image,
                                                  ImageRotation $rotation) {
        ArgumentGuard::notNull($driver, "driver");
        ArgumentGuard::notNull($image, "image");
        $normalizedImage = clone $image;
        if ($rotation != null) {
            if ($rotation->getRotation() != 0) {
                $normalizedImage = ImageUtils::rotateImage($image,
                        $rotation->getRotation());
            }
        } else { // Do automatic rotation if necessary
            try {
                $logger->verbose("Trying to automatically normalize rotation...");
                if (EyesSeleniumUtils::isMobileDevice($driver) &&
                        EyesSeleniumUtils::isLandscapeOrientation($driver)
                        && $image->getHeight() > $image->getWidth()) {
                    // For Android, we need to rotate images to the right, and
                    // for iOS to the left.
                    $degrees = EyesSeleniumUtils::isAndroid($driver) ? 90 : -90;
                    $normalizedImage = ImageUtils::rotateImage($image, $degrees);
                }
            } catch (Exception $e) {
                $logger->verbose("Got exception: " + $e->getMessage());
                $logger->verbose("Skipped automatic rotation handling.");
            }
        }

        return $normalizedImage;
    }

    public function __construct(Logger $logger, Eyes $eyes, RemoteWebDriver $driver){
        ArgumentGuard::notNull($logger, "logger");
        ArgumentGuard::notNull($eyes, "eyes");
        ArgumentGuard::notNull($driver, "driver");

        $this->logger = $logger;
        $this->eyes = $eyes;
        $this->driver = $driver;
        $this->elementsIds = new HashMap/*<String, WebElement>*/();
        $this->frameChain = new FrameChain($logger);
        $this->defaultContentViewportSize = null;

        // initializing "touch" if possible
        $executeMethod = null;
        try {
            $executeMethod = new RemoteExecuteMethod($driver);
        } catch (Exception $e) {
            // If an exception occurred, we simply won't instantiate "touch".
        }
        if (null != $executeMethod) {
            $touch = new EyesTouchScreen($logger, $this,
                    new RemoteTouchScreen($executeMethod));
        } else {
            $touch = null;
        }

        $logger->verbose("Driver session is " + getSessionId());
    }

    public function getEyes() {
        return $this->eyes;
    }

    public function getRemoteWebDriver() {
        return $this->driver;
    }

    public function getTouch() {
        return $this->touch;
    }

    /**
     *
     * @return The image rotation data.
     */
    public function getRotation() {
        return $this->rotation;
    }

    /**
     *
     * @param rotation The image rotation data.
     */
    public function setRotation(ImageRotation $rotation) {
        $this->rotation = $rotation;
    }

    public function get($s) {
        $this->frameChain->clear();
        $this->driver->get($s);
    }

    public function getCurrentUrl() {
        return $this->driver->getCurrentUrl();
    }

    public function getTitle() {
        return $this->driver->getTitle();
    }

    public function findElements(By $by) {
        $foundWebElementsList = $this->driver->findElements($by); //List<WebElement>

        // This list will contain the found elements wrapped with our class.
        $resultElementsList = array(); //new ArrayList<WebElement>(foundWebElementsList->size());

        foreach ($foundWebElementsList as $currentElement) {
            if ($currentElement instanceof RemoteWebElement) {
                $resultElementsList[] = new EyesRemoteWebElement($this->logger, $this,
                        /*(RemoteWebElement)*/ $currentElement);

                // For Remote web elements, we can keep the IDs
                $this->elementsIds->put((/*(RemoteWebElement)*/ $currentElement->getId()),
                        $currentElement);

            } else {
                throw new EyesException(sprintf("findElements: element is not a RemoteWebElement: %s", $by));
            }
        }

        return $resultElementsList;
    }

    public function findElement(By $by) {
        $webElement = $this->driver->findElement($by);
        if ($webElement instanceof RemoteWebElement) {
            $webElement = new EyesRemoteWebElement($logger, $this,
                    /*(RemoteWebElement)*/ $webElement);

            // For Remote web elements, we can keep the IDs,
            // for Id based lookup (mainly used for Javascript related
            // activities).
            $this->elementsIds->put(/*(RemoteWebElement)*/ $webElement->getId(),
                    $webElement);
        } else {
            throw new EyesException(sprintf(
                    "findElement: Element is not a RemoteWebElement: %s", $by));
        }

        return $webElement;
    }

    /**
     * Found elements are sometimes accessed by their IDs (e.g. tapping an
     * element in Appium).
     * @return Maps of IDs for found elements.
     */
    public function getElementIds () {
        return $this->elementsIds;
    }

    public function getPageSource() {
        return $this->driver->getPageSource();
    }

    public function close() {
        $this->driver->close();
    }

    public function quit() {
        $this->driver->quit();
    }

    public function getWindowHandles() {
        return $this->driver->getWindowHandles();
    }

    public function getWindowHandle() {
        return $this->driver->getWindowHandle();
    }
/*
    public function switchTo() {
        $this->logger->verbose("switchTo()");
        return new EyesTargetLocator(logger, this, driver.switchTo(),
                new EyesTargetLocator.OnWillSwitch() {
                    public void willSwitchToFrame(
                            EyesTargetLocator.TargetType targetType,
                            WebElement targetFrame) {
                        logger.verbose("willSwitchToFrame()");
                        switch(targetType) {
                            case DEFAULT_CONTENT:
                                logger.verbose("Default content.");
                                frameChain.clear();
                                break;
                            case PARENT_FRAME:
                                logger.verbose("Parent frame.");
                                frameChain.pop();
                                break;
                            default: // Switching into a frame
                                logger.verbose("Frame");

                                String frameId = ((EyesRemoteWebElement)
                                        targetFrame).getId();
                                Point pl = targetFrame.getLocation();
                                Dimension ds = targetFrame.getSize();
                                // Get the frame's content location.
                                Location contentLocation = new
                                        BordersAwareElementContentLocationProvider
                                        ().getLocation(logger, targetFrame,
                                        new Location(pl.getX(), pl.getY()));
                                frameChain.push(new Frame(logger, targetFrame,
                                        frameId,
                                        contentLocation,
                                        new RectangleSize(ds.getWidth(),
                                                ds.getHeight()),
                                        new ScrollPositionProvider(logger,
                                                driver).getCurrentPosition()));
                        }
                        logger.verbose("Done!");
                    }

                    public void willSwitchToWindow(String nameOrHandle) {
                        logger.verbose("willSwitchToWindow()");
                        frameChain.clear();
                        logger.verbose("Done!");
                    }
                });
    }
*/
    public function navigate() {
        return $this->driver->navigate();
    }

    public function manage() {
        return $this->driver->manage();
    }

    public function getMouse() {
        return new EyesMouse($this->logger, $this, $this->driver->getMouse());
    }

    public function getKeyboard() {
        return new EyesKeyboard($this->logger, $this, $this->driver->getKeyboard());
    }

    public function findElementByClassName($className) {
        return $this->findElement(By::className($className));
    }

    public function findElementsByClassName(String $className) {
        return $this->findElements(By::className($className));
    }

    public function findElementByCssSelector(String $cssSelector) {
        return $this->findElement(By::cssSelector($cssSelector));
    }

    public function findElementsByCssSelector(String $cssSelector) {
        return $this->findElements(By::cssSelector($cssSelector));
    }

    public function findElementById(String $id) {
        return $this->findElement(By::id($id));
    }

    public function findElementsById($id) {
        return $this->findElements(By::id($id));
    }

    public function findElementByLinkText($linkText) {
        return $this->findElement(By::linkText($linkText));
    }

    public function findElementsByLinkText($linkText) {
        return $this->findElements(By::linkText($linkText));
    }

    public function findElementByPartialLinkText($partialLinkText) {
        return $this->findElement(By::partialLinkText($partialLinkText));
    }

    public function findElementsByPartialLinkText($partialLinkText) {
        return $this->findElements(By::partialLinkText($partialLinkText));
    }

    public function findElementByName($name) {
        return $this->findElement(By::name($name));
    }

    public function findElementsByName($name) {
        return $this->findElements(By::name($name));
    }

    public function findElementByTagName($tagName) {
        return $this->findElement(By::tagName($tagName));
    }

    public function findElementsByTagName($tagName) {
        return $this->findElements(By::tagName($tagName));
    }

    public function findElementByXPath($path) {
        return $this->findElement(By::xpath($path));
    }

    public function findElementsByXPath($path) {
        return $this->findElements(By::xpath($path));
    }

    public function getCapabilities() {
        return $this->driver->getCapabilities();
    }

    public function executeScript($script, $args) {

        // Appium commands are sometimes sent as Javascript
        if (AppiumJsCommandExtractor::isAppiumJsCommand($script)) {
            $trigger = AppiumJsCommandExtractor::extractTrigger($elementsIds,
                            $this->driver->manage()->window()->getSize(), $script, $args);

            if ($trigger != null) {
                // TODO - Daniel, additional type of triggers
                if ($trigger instanceof MouseTrigger) {
                    $mt = /*(MouseTrigger)*/ clone $trigger;
                    $this->eyes->addMouseTrigger($mt->getMouseAction(),
                            $mt->getControl(), $mt->getLocation());
                }
            }
        }
        $this->logger->verbose("Execute script...");
        $result = $this->driver->executeScript($script, $args);
        $this->logger->verbose("Done!");
        return $result;
    }

    public function executeAsyncScript($script, $args) {

        // Appium commands are sometimes sent as Javascript
        if (AppiumJsCommandExtractor::isAppiumJsCommand($script)) {
            $trigger = AppiumJsCommandExtractor::extractTrigger($elementsIds,
                    $this->driver->manage()->window()->getSize(), $script, $args);

            if ($trigger != null) {
                // TODO - Daniel, additional type of triggers
                if ($trigger instanceof MouseTrigger) {
                    $mt = /*(MouseTrigger)*/ $trigger;
                    $this->eyes->addMouseTrigger($mt->getMouseAction(), $mt->getControl(), $mt->getLocation());
                }
            }
        }
        return $this->driver->executeAsyncScript($script, $args);
    }


    /**
     * @param forceQuery If true, we will perform the query even if we have a
     *                   cached viewport size.
     * @return The viewport size of the default content (outer most frame).
     */
    public function getDefaultContentViewportSize($forceQuery = false) {
        $this->logger->verbose("getDefaultContentViewportSize()");

        if ($this->defaultContentViewportSize != null && !$forceQuery) {
            $this->logger->verbose("Using cached viewport size: " . $this->defaultContentViewportSize);
            return $this->defaultContentViewportSize;
        }

        $currentFrames = $this->getFrameChain();
        // Optimization
        if ($currentFrames->size() > 0) {
            $this->switchTo()->defaultContent();
        }

        $this->logger->verbose("Extracting viewport size...");
        $this->defaultContentViewportSize = EyesSeleniumUtils::extractViewportSize($this->logger, $this);
        $this->logger->verbose("Done! Viewport size: " . $this->defaultContentViewportSize);

        if ($currentFrames->size() > 0) {
            $locator = $this->switchTo();
            $locator->frames($currentFrames);
        }
        return $this->defaultContentViewportSize;
    }

    /**
     *
     * @return A copy of the current frame chain.
     */
    public function getFrameChain() {
        return new FrameChain($this->logger, $this->frameChain);
    }

    public function getScreenshotAs(/*OutputType<X>*/ $xOutputType){
        // Get the image as base64.
        $screenshot64 = $this->driver->getScreenshotAs(OutputType::BASE64);
        $screenshot = ImageUtils::imageFromBase64($screenshot64);
        $screenshot = $this->normalizeRotation($this->logger, $this->driver, $screenshot, $this->rotation);

        // Return the image in the requested format.
        $screenshot64 = ImageUtils::base64FromImage($screenshot);
        return $xOutputType->convertFromBase64Png($screenshot64);
    }

    public function getUserAgent() {
        $userAgent = null;
        try {
            $userAgent = $this->driver->executeScript(
                    "return navigator.userAgent");
            $this->logger->verbose("user agent: "+ $userAgent);
        } catch (Exception $e) {
            $this->logger->verbose("Failed to obtain user-agent string");
            $userAgent = null;
        }
        return $userAgent;
    }

    private function getSessionId() {
        // extract remote web driver information
        return $this->driver->getSessionId()->toString();
    }
}
