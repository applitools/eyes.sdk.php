<?php

namespace Applitools;

/**
 * Encapsulates image retrieval.
 */
interface ImageProvider {

    /**
     * @return resource
     */
    function getImage();
}

?>