<?php

namespace Applitools;

/**
 * Encapsulates the instantiation of an EyesScreenshot object.
 */
interface EyesScreenshotFactory {

    /**
     * @param resource $image
     * @return EyesScreenshot
     */
    function makeScreenshot($image);
}

?>