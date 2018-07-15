<?php
/*
 * Applitools software.
 */

namespace Applitools\Selenium;

use Applitools\ArgumentGuard;
use Applitools\Exceptions\EyesException;
use Applitools\GeneralUtils;
use Applitools\Location;
use Applitools\Logger;
use Applitools\RectangleSize;
use Applitools\Selenium\Exceptions\EyesDriverOperationException;
use Exception;
use Facebook\WebDriver\Exception\UnknownServerException;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\Interactions\Internal\WebDriverCoordinates;
use Facebook\WebDriver\JavaScriptExecutor;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverPoint;

/**
 * We named this class EyesSeleniumUtils because there's a SeleniumUtils
 * class, and it caused collision.
 */
class EyesSeleniumUtils
{
    const SLEEP = 1000;
    const RETRIES = 3;
    // See Applitools WiKi for explanation.
    const JS_GET_VIEWPORT_SIZE =
        "var height = undefined;"
        . "var width = undefined;"
        . "  if (window.innerHeight) {height = window.innerHeight;}"
        . "  else if (document.documentElement "
        . "&& document.documentElement.clientHeight) "
        . "{height = document.documentElement.clientHeight;}"
        . "  else { var b = document.getElementsByTagName('body')[0]; "
        . "if (b.clientHeight) {height = b.clientHeight;}"
        . "};"
        . " if (window.innerWidth) {width = window.innerWidth;}"
        . " else if (document.documentElement "
        . "&& document.documentElement.clientWidth) "
        . "{width = document.documentElement.clientWidth;}"
        . " else { var b = document.getElementsByTagName('body')[0]; "
        . "if (b.clientWidth) {"
        . "width = b.clientWidth;}"
        . "};"
        . "return [width, height];";

    const JS_GET_CURRENT_SCROLL_POSITION =
        "var doc = document.documentElement; "
        . "var x = window.scrollX || "
        . "((window.pageXOffset || doc.scrollLeft) - (doc.clientLeft || 0));"
        . " var y = window.scrollY || "
        . "((window.pageYOffset || doc.scrollTop) - (doc.clientTop || 0));"
        . "return [x, y];";


    // IMPORTANT: Notice there's a major difference between scrollWidth
    // and scrollHeight. While scrollWidth is the maximum between an
    // element's width and its content width, scrollHeight might be
    // smaller (!) than the clientHeight, which is why we take the
    // maximum between them.
    const JS_GET_CONTENT_ENTIRE_SIZE =
        "var scrollWidth = document.documentElement.scrollWidth; "
        . "var bodyScrollWidth = document.body.scrollWidth; "
        . "var totalWidth = Math.max(scrollWidth, bodyScrollWidth); "
        . "var clientHeight = document.documentElement.clientHeight; "
        . "var bodyClientHeight = document.body.clientHeight; "
        . "var scrollHeight = document.documentElement.scrollHeight; "
        . "var bodyScrollHeight = document.body.scrollHeight; "
        . "var maxDocElementHeight = Math.max(clientHeight, scrollHeight); "
        . "var maxBodyHeight = Math.max(bodyClientHeight, bodyScrollHeight); "
        . "var totalHeight = Math.max(maxDocElementHeight, maxBodyHeight); "
        . "return [totalWidth, totalHeight];";

    const JS_TRANSFORM_KEYS = '["transform","-webkit-transform"]';

    /**
     * Extracts the location relative to the entire page from the coordinates
     * (e.g. as opposed to viewport)
     * @param WebDriverCoordinates $coordinates The coordinates from which location is extracted.
     * @return Location The location relative to the entire page
     */
    public static function getPageLocation(WebDriverCoordinates $coordinates)
    {
        if ($coordinates == null) {
            return null;
        }

        $p = $coordinates->onPage();
        return new Location($p->getX(), $p->getY());
    }

    /**
     * Extracts the location relative to the <b>viewport</b> from the
     * coordinates (e.g. as opposed to the entire page).
     * @param WebDriverCoordinates $coordinates The coordinates from which location is extracted.
     * @return Location The location relative to the viewport.
     */
    public static function getViewportLocation(WebDriverCoordinates $coordinates)
    {
        if ($coordinates == null) {
            return null;
        }

        $p = $coordinates->inViewPort();
        return new Location($p->getX(), $p->getY());
    }

    /**
     *
     * @param RemoteWebDriver $driver The driver for which to check if it represents a mobile device.
     * @return bool {@code true} if the platform running the test is a mobile platform. {@code false} otherwise.
     */
    public static function isMobileDevice(RemoteWebDriver $driver)
    {
        $caps = $driver->getCapabilities();
        $platformName = $caps->getCapability("platformName");
        return (strcasecmp($platformName, "ios") == 0 || strcasecmp($platformName, "android") == 0);
    }

    /**
     * @param WebDriver $driver The driver for which to check the orientation.
     * @return bool if this is a mobile device and is in landscape if this is a mobile device and is in landscape
     * @throws EyesDriverOperationException
     */
    public static function isLandscapeOrientation(WebDriver $driver)
    {
        if ($driver instanceof EyesWebDriver) {
            $driver = $driver->getRemoteWebDriver();
        }
        // We can only find orientation for mobile devices.
        if (self::isMobileDevice($driver)) {
            try {
                return strcasecmp($driver->manage()->window()->getScreenOrientation(), "landscape") == 0;
            } catch (\Exception $e) {
                throw new EyesDriverOperationException("Failed to get orientation!", $e);
            }
        }

        return false;
    }

    /**
     * Sets the overflow of the current context's document element.
     * @param JavascriptExecutor $executor The executor to use for setting the overflow.
     * @param mixed $value The overflow value to set.
     * @return string The previous overflow value (could be {@code null} if undefined).
     */
    public static function setOverflow(JavascriptExecutor $executor, $value)
    {
        if ($value == null) {
            $script = "var origOverflow = document.documentElement.style.overflow; "
                . "document.documentElement.style.overflow = undefined;"
                . " return origOverflow";
        } else {
            $script = sprintf("var origOverflow = document.documentElement.style.overflow; " .
                "document.documentElement.style.overflow = \"%s\"; " .
                "return origOverflow", $value);
        }
        return (string)$executor->executeScript($script);
    }

    /**
     * Hides the scrollbars of the current context's document element.
     *
     * @param JavascriptExecutor $executor The executor to use for hiding the scrollbars.
     * @param int $stabilizationTimeout The amount of time to wait for the "hide
     *                             scrollbars" action to take effect
     *                             (Milliseconds). Zero/negative values are
     *                             ignored.
     * @return string The previous value of the overflow property (could be {@code null}).
     */
    public static function hideScrollbars(JavascriptExecutor $executor, $stabilizationTimeout)
    {
        $originalOverflow = self::setOverflow($executor, "hidden");
        if ($stabilizationTimeout > 0) {
            try { //?????? FIXME need to check
                GeneralUtils::sleep($stabilizationTimeout);
            } catch (\Exception $e) {
                // Nothing to do.
            }
        }
        return $originalOverflow;
    }

    /**
     *
     * @param JavascriptExecutor $executor The executor to use.
     * @return Location The current scroll position of the current frame.
     */
    public static function getCurrentScrollPosition(JavascriptExecutor $executor)
    {
        //noinspection unchecked
        $positionAsList = $executor->executeScript(self::JS_GET_CURRENT_SCROLL_POSITION);
        return new Location((int)$positionAsList[0], (int)$positionAsList[1]);
    }

    /**
     * Sets the scroll position of the current frame.
     * @param JavascriptExecutor $executor The executor to use.
     * @param Location $location The position to be set.
     */
    public static function setCurrentScrollPosition(JavascriptExecutor $executor, Location $location)
    {
        $executor->executeScript(sprintf("window.scrollTo(%d,%d)", $location->getX(), $location->getY()));
    }

    /**
     *
     * @param JavascriptExecutor $executor The executor to use.
     * @return RectangleSize The size of the entire content.
     * @throws EyesDriverOperationException
     */
    public static function getCurrentFrameContentEntireSize(JavascriptExecutor $executor)
    {
        try {
            //noinspection unchecked
            $esAsList = $executor->executeScript(self::JS_GET_CONTENT_ENTIRE_SIZE);
            if (count($esAsList) <= 0) {
                throw new EyesDriverOperationException("Received empty value as frame's size");
            }
            $result = new RectangleSize((int)$esAsList[0], (int)$esAsList[1]);
        } catch (WebDriverException $e) {
            throw new EyesDriverOperationException(
                "Got exception while trying to extract entire size!", $e);
        }
        return $result;
    }

    /**
     *
     * @param JavaScriptExecutor $executor The executor to use.
     * @return RectangleSize The viewport size.
     */
    public static function executeViewportSizeExtraction(JavaScriptExecutor $executor) //FIXME
    {
        $vsAsList = $executor->executeScript(self::JS_GET_VIEWPORT_SIZE);
        return new RectangleSize((int)$vsAsList[0], (int)$vsAsList[1]);
    }

    /**
     * @param Logger $logger The logger to use.
     * @param WebDriver $driver The web driver to use.
     * @return RectangleSize The viewport size of the current context.
     */
    public static function extractViewportSize(Logger $logger, WebDriver $driver)
    {
        $logger->log("extractViewportSize()");

        if ($driver instanceof JavaScriptExecutor) {
            try {
                return self::executeViewportSizeExtraction($driver);
            } catch (Exception $ex) {
                $logger->verbose("Failed to extract viewport size using Javascript: " . $ex->getMessage());
            }
        }

        $logger->verbose("Using window size as viewport size.");

        /** @var WebDriverDimension $windowSize */
        $windowSize = $driver->manage()->window()->getSize();

        $width = $windowSize->getWidth();
        $height = $windowSize->getHeight();
        try {
            if (EyesSeleniumUtils::isLandscapeOrientation($driver) && $height > $width) {
                //noinspection SuspiciousNameCombination
                $height2 = $width;
                //noinspection SuspiciousNameCombination
                $width = $height;
                $height = $height2;
            }
        } catch (WebDriverException $e) {
            // Not every WebDriver supports querying for orientation.
        }
        $logger->verbose("Done! Size $width x $height");
        return new RectangleSize($width, $height);
    }

    /**
     *
     * @param Logger $logger The logger to use.
     * @param WebDriver $driver The web driver to use.
     * @param RectangleSize $size The size to set as the viepwort size.
     * @throws EyesException
     */
    public static function setViewportSize(Logger $logger, WebDriver $driver, RectangleSize $size)
    {
        $logger->log("setViewportSize($size)");

        ArgumentGuard::notNull($size, "size");

        try {
            // We move the window to (0,0) to have the best chance to be able to
            // set the viewport size as requested.
            $driver->manage()->window()->setPosition(new WebDriverPoint(0, 0));
        } catch (UnknownServerException $ex) {
            $logger->log("Can't move window. " . $ex->getMessage());
        }

        $actualViewportSize = self::extractViewportSize($logger, $driver);

        $logger->log("Initial viewport size: $actualViewportSize");

        // If the viewport size is already the required size
        if ($size->getWidth() == $actualViewportSize->getWidth() &&
            $size->getHeight() == $actualViewportSize->getHeight()
        ) {
            $logger->log("Required size already set.");
            return;
        }

        $browserSize = $driver->manage()->window()->getSize();
        $logger->log("Current browser size: {$browserSize->getWidth()}x{$browserSize->getHeight()}");
        $requiredBrowserSize = new WebDriverDimension($browserSize->getWidth() + ($size->getWidth() - $actualViewportSize->getWidth()),
            $browserSize->getHeight() + ($size->getHeight() - $actualViewportSize->getHeight()));
        $logger->log("Trying to set browser size to: {$requiredBrowserSize->getWidth()}x{$requiredBrowserSize->getHeight()}");

        $retriesLeft = self::RETRIES;
        do {
            $driver->manage()->window()->setSize($requiredBrowserSize);
            GeneralUtils::sleep(self::SLEEP);
            $browserSize = $driver->manage()->window()->getSize();
            $logger->log("Current browser size: {$browserSize->getWidth()}x{$browserSize->getHeight()}");
        } while (--$retriesLeft > 0 && !$browserSize->equals($requiredBrowserSize));

        if (!$browserSize->equals($requiredBrowserSize)) {
            throw new EyesException("Failed to set browser size!");
        }

        $actualViewportSize = self::extractViewportSize($logger, $driver);
        $logger->log("Current viewport size: $actualViewportSize");
        if (!$actualViewportSize->equals($size)) {
            // Additional attempt. This Solves the "maximized browser" bug
            // (border size for maximized browser sometimes different than
            // non-maximized, so the original browser size calculation is
            // wrong).
            $logger->log("Attempting one more time...");
            $browserSize = $driver->manage()->window()->getSize();
            $requiredBrowserSize = new WebDriverDimension($browserSize->getWidth() + ($size->getWidth() - $actualViewportSize->getWidth()),
                $browserSize->getHeight() + ($size->getHeight() - $actualViewportSize->getHeight()));

            $logger->log("Browser size: {$browserSize->getWidth()}x{$browserSize->getHeight()}");
            $logger->log("Required browser size: {$requiredBrowserSize->getWidth()}x{$requiredBrowserSize->getHeight()}");

            $retriesLeft = self::RETRIES;
            do {
                $driver->manage()->window()->setSize($requiredBrowserSize);
                GeneralUtils::sleep(self::SLEEP);
                $actualViewportSize = self::extractViewportSize($logger, $driver);
                $browserSize = $driver->manage()->window()->getSize();
                $logger->log("Browser size: {$browserSize->getWidth()}x{$browserSize->getHeight()}");
                $logger->log("Viewport size: $actualViewportSize");
            } while (--$retriesLeft > 0 && !$actualViewportSize != $size);
        }

        if (!$actualViewportSize->equals($size)) {
            throw new EyesException("Failed to set the viewport size.");
        }
    }

    /**
     *
     * @param RemoteWebDriver $driver The driver to test.
     * @return bool {@code true} if the driver is an Android driver.
     * {@code false} otherwise.
     */
    public static function isAndroid(RemoteWebDriver $driver)
    {
        $capabilities = $driver->getCapabilities();
        $platformName = $capabilities->getCapability("platformName");
        return strcasecmp("android", $platformName) == 0;
    }

    /**
     *
     * @param RemoteWebDriver $driver The driver to test.
     * @return bool {@code true} if the driver is an iOS driver.
     * {@code false} otherwise.
     */
    public static function isIOS(RemoteWebDriver $driver)
    {
        $capabilities = $driver->getCapabilities();
        $platformName = $capabilities->getCapability("platformName");
        return strcasecmp("ios", $platformName) == 0;
    }

    /**
     *
     * @param RemoteWebDriver $driver The driver to get the platform version from.
     * @return string The platform version or {@code null} if it is undefined.
     */
    public static function getPlatformVersion(RemoteWebDriver $driver)
    {
        $capabilities = $driver->getCapabilities();
        $platformVersionObj = $capabilities->getCapability("platformVersion");
        return $platformVersionObj; //$platformVersionObj == null ? null : String.valueOf($platformVersionObj);
    }

    /**
     * @param JavascriptExecutor $executor The executor to use.
     * @return float The device pixel ratio.
     */
    public static function getDevicePixelRatio(JavascriptExecutor $executor)
    {
        return (float)$executor->executeScript("return window.devicePixelRatio");
    }

    /**
     *
     * @param JavascriptExecutor $executor The executor to use.
     * @return mixed The current documentElement transform values, according to
     * {@link #JS_TRANSFORM_KEYS}.
     */
    public static function getCurrentTransform(JavascriptExecutor $executor)
    {
        $script = "return { ";
        foreach (json_decode(self::JS_TRANSFORM_KEYS) as $key) { // ???????
            $script .= "'$key': document.documentElement.style['$key'],";
        }

        // Ending the list
        $script .= " }";

        //noinspection unchecked
        return /*(Map<String, String>)*/
            $executor->executeScript($script);

    }

    /**
     * Sets transforms for document.documentElement according to the given
     * map of style keys and values.
     *
     * @param JavascriptExecutor $executor The executor to use.
     * @param array $transforms The transforms to set. Keys are used as style keys,
     *                   and values are the values for those styles.
     */
    public static function setTransforms(JavascriptExecutor $executor, $transforms)
    {
        $script = "";
        foreach ($transforms as $key => $entry) {
            $script .= "document.documentElement.style['$key'] = '$entry';";
        }
        $executor->executeScript($script);
    }

    /**
     * Set the given transform to document.documentElement for all style keys
     * defined in {@link #JS_TRANSFORM_KEYS} .
     *
     * @param JavascriptExecutor $executor The executor to use.
     * @param string $transform The transform value to set.
     */
    public static function setTransform(JavascriptExecutor $executor, $transform)
    {
        $transforms = array();

        foreach (json_decode(self::JS_TRANSFORM_KEYS) as $key) {
            $transforms[$key] = $transform;
        }

        self::setTransforms($executor, $transforms);
    }

    /**
     * Translates the current documentElement to the given position.
     * @param JavascriptExecutor $executor The executor to use.
     * @param Location $position The position to translate to.
     */
    public static function translateTo(JavascriptExecutor $executor, Location $position)
    {
        self::setTransform($executor, "translate(-{$position->getX()}px, -{$position->getY()}px)");
    }

    /**
     * Returns the given executor's (which actually represents a web driver) user-agent string.
     * @param JavaScriptExecutor $executor
     * @param Logger $logger
     * @return string|null
     */
    public static function getUserAgent(JavaScriptExecutor $executor, Logger $logger)
    {
        $userAgent = null;
        try {
            $userAgent = $executor->executeScript("return navigator.userAgent");
            $logger->verbose("user agent: $userAgent");
        } catch (\Exception $e) {
            $logger->verbose("Failed to obtain user-agent string");
            $userAgent = null;
        }
        return $userAgent;
    }
}
