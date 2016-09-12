<?php

/**
 * Encapsulates scaling logic.
 */
interface ScaleProvider
{
    /**
     *
     * @return The ratio by which an image will be scaled.
     */
    function getScaleRatio();

    /**
     *
     * @param image The image to scale.
     * @return A new scaled image.
     */
    // FIXME need to use Gregwar\Image\Image function scaleImage($image);
}
