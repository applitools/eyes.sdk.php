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
}
