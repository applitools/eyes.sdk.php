<?php

namespace Applitools;

use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverElement;

class AppiumJsCommandExtractor
{
    const COMMAND_PREFIX = "mobile: ";
    const TAP_COMMAND = AppiumJsCommandExtractor::COMMAND_PREFIX . "tap";
    const APPIUM_COORDINATES_DEFAULT = 0.5;
    const APPIUM_TAP_COUNT_DEFAULT = 1;

    /**
     * Used for identifying if a javascript script is a command to Appium.
     * @param string $script The script to test whether it's an Appium command.
     * @return bool True if the script is an Appium command, false otherwise.
     */
    public static function isAppiumJsCommand($script)
    {
        return strncmp($script, AppiumJsCommandExtractor::COMMAND_PREFIX, strlen(AppiumJsCommandExtractor::COMMAND_PREFIX)) === 0;
    }

    /**
     * Given a command and its parameters, returns the equivalent trigger.
     * @param mixed $elementsIds A mapping of known elements' IDs to elements.
     * @param WebDriverDimension $viewportSize The dimensions of the current viewport
     * @param string $script The Appium command from which the trigger would be extracted
     * @param mixed $args The trigger's parameters.
     * @return MouseTrigger The trigger which represents the given command.
     */
    public static function extractTrigger($elementsIds, WebDriverDimension $viewportSize, $script, $args)
    {

        if (strcmp($script, AppiumJsCommandExtractor::TAP_COMMAND) === 0) {
            if (count($args) != 1) {
                // We don't know what the rest of the parameters are, so...
                return null;
            }

            try {
                $tapObject = /*(Map<String, String>)*/
                    $args[0];
                $xObj = $tapObject->get("x");
                $yObj = $tapObject->get("y");
                $tapCountObj = $tapObject->get("tapCount");
            } catch (\Exception $e) {
                // We only know how to handle Map as the arguments container.
                return null;
            }

            $x = ($xObj != null) ? (float)$xObj : AppiumJsCommandExtractor::APPIUM_COORDINATES_DEFAULT; //FIXME
            $y = ($yObj != null) ? (float)$yObj : AppiumJsCommandExtractor::APPIUM_COORDINATES_DEFAULT;

            // If an element is referenced, then the coordinates are relative
            // to the element.
            $elementId = $tapObject->get("element");
            if ($elementId != null) {

                /** @var WebDriverElement $referencedElement */
                $referencedElement = $elementsIds->get($elementId);

                // If an element was referenced, but we don't have it's ID,
                // we can't create the trigger.
                if ($referencedElement == null) {
                    return null;
                }

                $elementPosition = $referencedElement->getLocation();
                $elementSize = $referencedElement->getSize();


                $control = Region::CreateFromLTWH($elementPosition->getX(), $elementPosition->getY(), $elementSize->getWidth(), $elementSize->getHeight());

                // If coordinates are percentage of the size of the
                // viewport/element.
                if ($x < 1) {
                    $x = $control->getWidth() * $x;
                }
                if ($y < 1) {
                    $y = $control->getHeight() * $y;
                }

            } else {
                // If coordinates are percentage of the size of the
                // viewport/element.
                if ($x < 1) {
                    $x = $viewportSize->getWidth() * $x;
                }
                if ($y < 1) {
                    $y = $viewportSize->getHeight() * $y;
                }

                // creating a fake control, for which the tap is at the right
                // bottom corner
                $control = Region::CreateFromLTWH(0, 0, round($x), round($y));
            }


            $location = new Location(round($x), round($y));

            // Deciding whether this is click/double click.
            $tapCount = ($tapCountObj != null) ?
                (int)$tapCountObj : AppiumJsCommandExtractor::APPIUM_TAP_COUNT_DEFAULT;
            $action = ($tapCount == 1) ? MouseAction::Click : MouseAction::DoubleClick;

            return new MouseTrigger($action, $control, $location);
        }

        // No trigger from the given command.
        return null;
    }
}