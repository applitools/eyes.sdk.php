<?php

namespace Applitools\Selenium;


use Applitools\ArgumentGuard;
use Applitools\Location;
use Applitools\Logger;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\Remote\RemoteWebElement;

/**
 * Encapsulates an algorithm to find an element's content location, based on
 * the element's location.
 */
class BordersAwareElementContentLocationProvider {

    /**
     * Returns a location based on the given location.
     * @param Logger $logger The logger to use.
     * @param RemoteWebElement $element The element for which we want to find the content's location.
     * @param Location $location The location of the element.
     * @return Location The location of the content of the element.
     */
    public static function getLocation(Logger $logger, RemoteWebElement $element,
                                Location $location) {
        ArgumentGuard::notNull($logger, "logger");
        ArgumentGuard::notNull($element, "element");
        ArgumentGuard::notNull($location, "location");

        $logger->verbose(sprintf(
                "BordersAdditionFrameLocationProvider(logger, element, %s)",
                json_encode($location)));

        // Frame borders also have effect on the frame's
        // location.
        //$leftBorderWidth, $topBorderWidth;
        //$propValue;
        try {
            $logger->verbose("Get element border left width...");
            if ($element instanceof EyesRemoteWebElement) {
                $logger->verbose(
                        "Element is an EyesWebElement, " .
                                "using 'getComputedStyle'.");
                try {
                    $propValue = /*((EyesRemoteWebElement)*/
                        //FIXME $element->getComputedStyle("border-left-width");
                        $element->getBorderLeftWidth(); //FIXME
                } catch (WebDriverException $e) {
                    $logger->verbose(sprintf(
                            "Using getComputedStyle failed: %s.", $e->getMessage()));
                    $logger->verbose("Using getCssValue...");
                    $propValue =
                        //FIXME $element->getCssValue("border-left-width");
                        $element->getBorderLeftWidth(); //FIXME
                }
                $logger->verbose("Done!");
            } else {
                // OK, this is weird, we got an element which is not
                // EyesWebElement?? Log it and try to move on.
                $logger->verbose("Element is not an EyesWebElement! " .
                        "(when trying to get border-left-width) " .
                        "Element's class: " .
                        get_class($element));
                $logger->verbose("Using getCssValue...");
                $propValue = $element->getCssValue("border-left-width");
                //$element->getBorderLeftWidth(); //FIXME
                $logger->verbose("Done!");
            }
            // Convert border value from the format "2px" to int.
            $leftBorderWidth = round($propValue);
            $logger->verbose("border-left-width: " . $leftBorderWidth);
        } catch (WebDriverException $e) {
            $logger->verbose(sprintf(
                    "Couldn't get the element's border-left-width: %s. " .
                            "Falling back to default",
                    $e->getMessage()));
            $leftBorderWidth = 0;
        }
        try {
            $logger->verbose("Get element's border top width...");
            if ($element instanceof EyesRemoteWebElement) {
                $logger->verbose(
                        "Element is an EyesWebElement, " .
                                "using 'getComputedStyle'.");
                try {
                    $propValue = /*((EyesRemoteWebElement)*/
                        //FIXME $element->getComputedStyle("border-top-width");
                        $element->getBorderTopWidth(); //FIXME
                } catch (WebDriverException $e) {
                    $logger->verbose(sprintf(
                            "Using getComputedStyle failed: %s.",
                            $e->getMessage()));
                    $logger->verbose("Using getCssValue...");
                    $propValue =
                        //FIXME $element->getCssValue("border-top-width");
                        $element->getBorderTopWidth(); //FIXME
                }
                $logger->verbose("Done!");
            } else {
                // OK, this is weird, we got an element which is not
                // EyesWebElement?? Log it and try to move on.
                $logger->verbose("Element is not an EyesWebElement " .
                        "(when trying to get border-top-width) " .
                        "Element's class: " .
                        get_class($element));
                $logger->verbose("Using getCssValue...");
                $propValue =
                    //FIXME $element->getCssValue("border-top-width");
                    $element->getBorderTopWidth(); //FIXME
                $logger->verbose("Done!");
            }

            $topBorderWidth = round($propValue);

            $logger->verbose("border-top-width: " . $topBorderWidth);
        } catch (WebDriverException $e) {
            $logger->verbose(sprintf(
                    "Couldn't get the element's border-top-width: %s. " .
                            "Falling back to default",
                    $e->getMessage()));
            $topBorderWidth = 0;
        }

        $contentLocation = clone $location;
        $contentLocation->offset($leftBorderWidth, $topBorderWidth);
        $logger->verbose("Done!");
        return $contentLocation;
    }
}
