<?php

namespace Applitools;

/**
 * Encapsulates cutting logic.
 */
interface CutProvider {

    /**
     *
     * @param Image $image The image to cut.
     * @return Image A new cut image.
     */
    function cut($image);
}

?>