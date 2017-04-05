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
}
