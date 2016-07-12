<?php
/**
 * Encapsulates cutting logic.
 */
interface CutProvider {

    /**
     *
     * @param image The image to cut.
     * @return A new cut image.
     */
    function cut($image);
}
