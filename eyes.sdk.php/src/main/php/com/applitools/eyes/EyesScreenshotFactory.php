<?php

namespace Applitools;

use Gregwar\Image\Image;

/**
 * Encapsulates the instantiation of an EyesScreenshot object.
 */
interface EyesScreenshotFactory {
    function makeScreenshot(Image $image);
}

?>