<?php

namespace Applitools;

/**
 * Encapsulates cutting logic.
 */
interface CutProvider {

    /**
     *
     * @param resource $image The image to cut.
     * @return resource A new cut image.
     */
    function cut($image);

    /**
     * Get a scaled version of the cut provider.
     *
     * @param float $scaleRatio The ratio by which to scale the current cut parameters.
     * @return CutProvider A new scale cut provider instance.
     */
    function scale($scaleRatio);
}