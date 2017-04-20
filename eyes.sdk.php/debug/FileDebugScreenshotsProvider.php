<?php

namespace Applitools;

use Gregwar\Image\Image;

/**
 * A debug screenshot provider for saving screenshots to file.
 */
class FileDebugScreenshotsProvider extends DebugScreenshotsProvider {

    public function save(Image $image, $suffix) {
        $filename = $this->getPath() . $this->getPrefix() . microtime() . "_" . $suffix . ".png";
        ImageUtils::saveImage($image, str_replace(" ", "_", $filename));
    }
}
