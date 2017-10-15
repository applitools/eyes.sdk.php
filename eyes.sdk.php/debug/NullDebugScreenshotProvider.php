<?php

namespace Applitools;

use Gregwar\Image\Image;

/**
 * A mock debug screenshot provider.
 */
class NullDebugScreenshotProvider extends DebugScreenshotsProvider {

    public function save(Image &$image, $suffix) {
        // Do nothing.
        return $image;
    }
}
