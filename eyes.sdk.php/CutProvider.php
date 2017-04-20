<?php

namespace Applitools;

use Gregwar\Image\Image;

/**
 * Encapsulates cutting logic.
 */
interface CutProvider {

    /**
     *
     * @param Image $image The image to cut.
     * @return Image A new cut image.
     */
    function cut(Image $image);



    /**
     * Get a scaled version of the cut provider.
     *
     * @param float $scaleRatio The ratio by which to scale the current cut parameters.
     * @return CutProvider A new scale cut provider instance.
     */
    function scale($scaleRatio);
}

?>