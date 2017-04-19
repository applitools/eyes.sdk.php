<?php

namespace Applitools;

use Gregwar\Image\Image;

/**
 * Encapsulates image retrieval.
 */
interface ImageProvider {

    /**
     * @return Image
     */
    function getImage();
}

?>