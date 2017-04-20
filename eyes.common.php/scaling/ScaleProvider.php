<?php

namespace Applitools;

use Gregwar\Image\Image;

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
     * @param Image $image The source image.
     * @return Image The scaled image.
     */
    function scaleImage(Image $image);
}
