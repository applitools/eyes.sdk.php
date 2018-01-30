<?php

namespace Applitools\Selenium;

use Applitools\ArgumentGuard;
use Applitools\CoordinatesType;
use Applitools\Exceptions\EyesException;
use Applitools\Location;
use Applitools\Logger;
use Applitools\MouseAction;
use Applitools\Region;
use Facebook\WebDriver\Remote\DriverCommand;
use Facebook\WebDriver\Remote\FileDetector;
use Facebook\WebDriver\Remote\RemoteExecuteMethod;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverPoint;

class EyesRemoteWebElement extends RemoteWebElement
{
    /** @var Logger */
    private $logger;

    /** @var EyesWebDriver */
    private $eyesDriver;

    /** @var WebDriverElement */
    private $webElement;

    /** @var \ReflectionMethod */
    private $executeMethod;

    const JS_GET_COMPUTED_STYLE_FORMATTED_STR =
         "var elem = arguments[0];" .
         "var styleProp = '%s';" .
         "if (window.getComputedStyle) {" .
             "return window.getComputedStyle(elem, null).getPropertyValue(styleProp);" .
         "} else if (elem.currentStyle) {" .
         "   return elem.currentStyle[styleProp];" .
         "} else {" .
             "return null;" .
         "}";

    const JS_GET_SCROLL_LEFT = "return arguments[0].scrollLeft;";
    const JS_GET_SCROLL_TOP = "return arguments[0].scrollTop;";
    const JS_GET_SCROLL_WIDTH = "return arguments[0].scrollWidth;";
    const JS_GET_SCROLL_HEIGHT = "return arguments[0].scrollHeight;";

    const JS_GET_CLIENT_WIDTH = "return arguments[0].clientWidth;";
    const JS_GET_CLIENT_HEIGHT = "return arguments[0].clientHeight;";

    const JS_GET_BOUNDING_CLIENT_RECT = "return arguments[0].getBoundingClientRect();";

    const JS_SCROLL_TO_FORMATTED_STR =
        "arguments[0].scrollLeft = %d;" .
        "arguments[0].scrollTop = %d;";

    const JS_GET_OVERFLOW = "return arguments[0].style.overflow;";
    const JS_SET_OVERFLOW_FORMATTED_STR = "arguments[0].style.overflow = '%s'";

    public function __construct(Logger $logger, EyesWebDriver $eyesDriver, WebDriverElement $webElement)
    {
        ArgumentGuard::notNull($logger, "logger");
        ArgumentGuard::notNull($eyesDriver, "eyesDriver");
        ArgumentGuard::notNull($webElement, "webElement");

        parent::__construct(new RemoteExecuteMethod($eyesDriver->getRemoteWebDriver()), $webElement->getID());

        $this->logger = $logger;
        $this->eyesDriver = $eyesDriver;
        $this->webElement = $webElement;

        try {
            // We can't call the execute method directly because it is
            // protected, and we must override this function since we don't
            // have the "parent" and "id" of the aggregated object.
            //FIXME need to check

            $this->executeMethod = new \ReflectionMethod(RemoteExecuteMethod::class, "execute");
            //$executeMethod = RemoteWebElement.class.getDeclaredMethod("execute", String.class, Map.class);
            $this->executeMethod->setAccessible(true);
        } catch (\Exception $e) {
            throw new EyesException("Failed to find 'execute' method!", 0, $e);
        }
    }

    public function getBounds()
    {
        $rect = $this->eyesDriver->executeScript(self::JS_GET_BOUNDING_CLIENT_RECT, array($this));
        return Region::CreateFromLTWH($rect['left'], $rect['top'], $rect['width'], $rect['height']);
    }
/*
    public function getBounds()
    {
        $loc = $this->webElement->getLocation();
        $size = $this->webElement->getSize();
        $region = Region::CreateFromLTWH($loc->getX(), $loc->getY(), $size->getWidth(), $size->getHeight());
        $region->setCoordinatesType(CoordinatesType::CONTEXT_RELATIVE);
        return $region;
    }
*/
    public function getClientAreaBounds(){
        $bounds = $this->getBounds();

        $clientWidth = $this->getClientWidth();
        $clientHeight = $this->getClientHeight();

        $this->logger->verbose("element rect: $bounds");

        $borderLeftWidth = $this->getBorderLeftWidth();
        $borderTopWidth = $this->getBorderTopWidth();

        $elementRegion = Region::CreateFromLTWH(
            round($bounds->getLeft() + $borderLeftWidth),
            round($bounds->getTop() + $borderTopWidth),
            $clientWidth,
            $clientHeight);

        return $elementRegion;
    }

    /**
     * Returns the computed value of the style property for the current element.
     * @param string $propStyle The style property which value we would like to extract.
     * @return mixed The value of the style property of the element, or {@code null}.
     */
    public function getComputedStyle($propStyle)
    {
        $scriptToExec = sprintf
        (self::JS_GET_COMPUTED_STYLE_FORMATTED_STR, $propStyle);
        return $this->eyesDriver->executeScript($scriptToExec, [$this]);
    }

    /**
     * @param string $propStyle
     * @return float The integer value of a computed style.
     */
    public function getComputedStyleInteger($propStyle)
    {
        $computedStyle = $this->getComputedStyle($propStyle);
        return intval(round(floatval(str_replace("px", "", trim($computedStyle)))));
    }

    /**
     * @return float The value of the scrollLeft property of the element.
     */
    public function getScrollLeft()
    {
        return $this->eyesDriver->executeScript(self::JS_GET_SCROLL_LEFT, array($this));
    }

    /**
     * @return float The value of the scrollTop property of the element.
     */
    public function getScrollTop()
    {
        return $this->eyesDriver->executeScript(self::JS_GET_SCROLL_TOP, array($this));
    }

    /**
     * @return float The value of the scrollWidth property of the element.
     */
    public function getScrollWidth()
    {
        return $this->webElement->getAttribute('scrollWidth');
    }

    /**
     * @return float The value of the scrollHeight property of the element.
     */
    public function getScrollHeight()
    {
        return $this->webElement->getAttribute('scrollHeight');
    }

    /**
     * @return float The value of the clientWidth property of the element.
     */
    public function getClientWidth()
    {
        return $this->webElement->getAttribute('clientWidth');
    }

    /**
     * @return float The value of the clientHeight property of the element.
     */
    public function getClientHeight()
    {
        return $this->webElement->getAttribute('clientHeight');
    }

    /**
     * @return float The width of the left border.
     */
    public function getBorderLeftWidth()
    {
        //return $this->getComputedStyleInteger("border-left-width");
        return str_replace('px', '', $this->getCssValue("border-left-width")); //FIXME need to optimize
    }

    /**
     * @return float The width of the right border.
     */
    public function getBorderRightWidth()
    {
        //return $this->getComputedStyleInteger("border-right-width");
        return str_replace('px', '', $this->getCssValue("border-right-width")); //FIXME need to optimize
    }

    /**
     * @return float The width of the top border.
     */
    public function getBorderTopWidth()
    {
        //return $this->getComputedStyleInteger("border-top-width");
        return str_replace('px', '', $this->getCssValue("border-top-width")); //FIXME need to optimize
    }

    /**
     * @return float The width of the bottom border.
     */
    public function getBorderBottomWidth()
    {
        //return $this->getComputedStyleInteger("border-bottom-width");
        return str_replace('px', '', $this->getCssValue("border-bottom-width")); //FIXME need to optimize
    }

    /**
     * Scrolls to the specified location inside the element.
     * @param Location $location The location to scroll to.
     */
    public function scrollTo(Location $location)
    {
        $this->eyesDriver->executeScript(sprintf(self::JS_SCROLL_TO_FORMATTED_STR, $location->getX(), $location->getY()), array($this));
    }

    /**
     * @return string The overflow of the element.
     */
    public function getOverflow()
    {
        return $this->getCssValue("overflow");
        //return $this->eyesDriver->getRemoteWebDriver()->executeScript(self::JS_GET_OVERFLOW, array(array(":id" => $this->getId())));

    }

    /**
     * Sets the overflow of the element.
     * @param string $overflow The overflow to set.
     */
    public function setOverflow($overflow)
    {

        $this->eyesDriver->executeScript(sprintf(self::JS_SET_OVERFLOW_FORMATTED_STR,
            $overflow), array($this));
    }

    public function click()
    {
        // Letting the driver know about the current action.
        $currentControl = $this->getBounds();
        $this->eyesDriver->getEyes()->addMouseTriggerElement(MouseAction::Click, $this);
        $this->logger->verbose("click($currentControl)");

        $this->webElement->click();
    }

    public function getWrappedDriver()
    {
        return $this->eyesDriver;
    }

    public function getId()
    {
        return $this->webElement->getId();
    }

    public function setParent(RemoteWebDriver $parent)
    {
        $this->webElement->setParent($parent);
    }

    public function execute($command, $parameters = array())
    {
        try { //FIXME need to check
            return $this->eyesDriver->getRemoteWebDriver()->execute($command, $parameters);
        } catch (\Exception $e) {
            throw new EyesException("Failed to invoke 'execute' method!",0, $e);
        }
    }

    public function setId($id)
    {
        $this->webElement->setId($id);
    }

    public function setFileDetector(FileDetector $detector)
    {
        $this->webElement->setFileDetector($detector);
    }

    public function submit()
    {
        $this->webElement->submit();
    }

    public function sendKeys(/*CharSequence... */
        $keysToSend)
    {
        $chars = str_split($keysToSend);
        foreach ($chars as $key) {
            $this->eyesDriver->getEyes()->addTextTriggerElement($this, $key);
        }

        $this->webElement->sendKeys($keysToSend);
    }

    public function clear()
    {
        $this->webElement->clear();
    }

    public function getTagName()
    {
        return $this->webElement->getTagName();
    }

    public function getAttribute($name)
    {
        return $this->webElement->getAttribute($name);
    }

    public function isSelected()
    {
        return $this->webElement->isSelected();
    }

    public function isEnabled()
    {
        return $this->webElement->isEnabled();
    }

    public function getText()
    {
        return $this->webElement->getText();
    }

    public function getCssValue($propertyName)
    {
        return $this->webElement->getCssValue($propertyName);
    }

    /**
     * For RemoteWebElement object, the function returns an
     * EyesRemoteWebElement object. For all other types of WebElement,
     * the function returns the original object.
     * @param WebDriverElement $elementToWrap
     * @return EyesRemoteWebElement|WebDriverElement
     */
    private function wrapElement(WebDriverElement $elementToWrap)
    {
        $resultElement = $elementToWrap; //FIXME clone?
        if ($elementToWrap instanceof RemoteWebElement) {
            $resultElement = new EyesRemoteWebElement($this->logger, $this->eyesDriver,
                /*(RemoteWebElement) */
                $elementToWrap);
        }
        return $resultElement;
    }

    /**
     * For RemoteWebElement object, the function returns an
     * EyesRemoteWebElement object. For all other types of WebElement,
     * the function returns the original object.
     * @param $elementsToWrap
     * @return array
     */
    private function wrapElements($elementsToWrap)
    {
        // This list will contain the found elements wrapped with our class.
        $wrappedElementsList = array();

        foreach ($elementsToWrap as $currentElement) {
            if ($currentElement instanceof RemoteWebElement) {
                $wrappedElementsList[] = new EyesRemoteWebElement($this->logger, $this->eyesDriver, $currentElement);
            } else {
                $wrappedElementsList[] = $currentElement;
            }
        }

        return $wrappedElementsList;
    }

    public function findElements(WebDriverBy $by)
    {
        return $this->wrapElements($this->webElement->findElements($by));
    }

    public function findElement(WebDriverBy $by)
    {
        return $this->wrapElement($this->webElement->findElement($by));
    }

    public function findElementById($using)
    {
        return $this->findElement(WebDriverBy::id($using));
    }

    public function findElementsById($using)
    {
        return $this->findElements(WebDriverBy::id($using));
    }

    public function findElementByLinkText($using)
    {
        return $this->findElement(WebDriverBy::linkText($using));
    }

    public function findElementsByLinkText($using)
    {
        return $this->findElements(WebDriverBy::linkText($using));
    }

    public function findElementByName($using)
    {
        return $this->findElement(WebDriverBy::name($using));
    }

    public function findElementsByName($using)
    {
        return $this->findElements(WebDriverBy::name($using));
    }

    public function findElementByClassName($using)
    {
        return $this->findElement(WebDriverBy::className($using));
    }

    public function findElementsByClassName($using)
    {
        return $this->findElements(WebDriverBy::className($using));
    }

    public function findElementByCssSelector($using)
    {
        return $this->findElement(WebDriverBy::cssSelector($using));
    }

    public function findElementsByCssSelector($using)
    {
        return $this->findElements(WebDriverBy::cssSelector($using));
    }

    public function findElementByXPath($using)
    {
        return $this->findElement(WebDriverBy::xpath($using));
    }

    public function findElementsByXPath($using)
    {
        return $this->findElements(WebDriverBy::xpath($using));
    }

    public function findElementByPartialLinkText($using)
    {
        return $this->findElement(WebDriverBy::partialLinkText($using));
    }

    public function findElementsByPartialLinkText($using)
    {
        return $this->findElements(WebDriverBy::partialLinkText($using));
    }

    public function findElementByTagName($using)
    {
        return $this->findElement(WebDriverBy::tagName($using));
    }

    public function findElementsByTagName($using)
    {
        return $this->findElements(WebDriverBy::tagName($using));
    }

    public function equals(WebDriverElement $other)
    {
        return ($other instanceof RemoteWebElement) && ($this->webElement->equals($other));
    }

    public function hashCode()
    {
        return $this->webElement->hashCode();
    }

    public function isDisplayed()
    {
        return $this->webElement->isDisplayed();
    }

    /**
     * @inheritdoc
     */
    public function getLocation()
    {
        // This is workaround: Selenium currently just removes the value
        // after the decimal dot (instead of rounding up), which causes
        // incorrect locations to be returned when using ChromeDriver (with
        // FF it seems that the coordinates are already rounded up, so
        // there's no problem). So, we copied the code from the Selenium
        // client and instead of using "rawPoint.get(...).intValue()" we
        // return the double value, and use "ceil".
        $response = $this->execute(DriverCommand::GET_ELEMENT_LOCATION,
            array(":id" => $this->getId()));//ImmutableMap::of("id", $elementId));
        //$rawPoint = $response->getValue();
        $this->logger->verbose("{$response['x']},{$response['y']}");
        $x = round($response["x"]);
        $y = round($response["y"]);
        return new WebDriverPoint($x, $y);

        // TODO: Use the command delegation instead. (once the bug is fixed).
//        return webElement.getLocation();
    }

    public function getSize()
    {
        // This is workaround: Selenium currently just removes the value
        // after the decimal dot (instead of rounding up), which might cause
        // incorrect size to be returned . So, we copied the code from the
        // Selenium client and instead of using "rawPoint.get(...).intValue()"
        // we return the double value, and use "ceil".
        $elementId = $this->getId();
        $response = $this->execute(DriverCommand::GET_ELEMENT_SIZE,
            array(":id" => $elementId));//ImmutableMap::of("id", elementId));
        //$rawSize = $response->getValue();
        $width = floor($response["width"]);
        $height = floor($response["height"]);
        return new WebDriverDimension($width, $height);

        // TODO: Use the command delegation instead. (once the bug is fixed).
//        return webElement.getSize();
    }

    public function getCoordinates()
    {
        return $this->webElement->getCoordinates();
    }

    public function toString()
    {
        return "EyesRemoteWebElement:" . $this->webElement->toString();
    }
}