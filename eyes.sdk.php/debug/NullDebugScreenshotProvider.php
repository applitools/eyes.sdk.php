<?php

namespace Applitools;

/**
 * A mock debug screenshot provider.
 */
class NullDebugScreenshotProvider extends DebugScreenshotsProvider {

    public function save($image, $suffix) {
        // Do nothing.
    }
}
