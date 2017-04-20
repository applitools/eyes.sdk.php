<?php

namespace Applitools;

use Gregwar\Image\Image;

/**
 * Encapsulates the instantiation of an EyesScreenshot object.
 */
interface EyesScreenshotFactory {


    /**
     * @param Image $image
     * @return EyesScreenshot
     */
    function makeScreenshot(Image $image);
}

?>