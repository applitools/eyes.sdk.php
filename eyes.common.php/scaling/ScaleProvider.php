<?php

namespace Applitools;

/**
 * Encapsulates scaling logic.
 */
interface ScaleProvider
{
    /**
     *
     * @return float The ratio by which an image will be scaled.
     */
    function getScaleRatio();

    /**
     * Scales a given source image.
     * @param resource $image The source image.
     * @return resource The scaled image.
     */
    function scaleImage($image);
}
