<?php
/**
 * Encapsulates the instantiation of an EyesScreenshot object.
 */
interface EyesScreenshotFactory {
    function makeScreenshot(Gregwar\Image\Image $image);
}
