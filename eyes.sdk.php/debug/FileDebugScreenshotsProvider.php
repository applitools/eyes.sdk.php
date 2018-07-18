<?php

namespace Applitools;

/**
 * A debug screenshot provider for saving screenshots to file.
 */
class FileDebugScreenshotsProvider extends DebugScreenshotsProvider
{

    public function save($image, $suffix)
    {
        $t = gettimeofday();
        $mSec = round($t["usec"] / 1000);
        $filename = $this->getPath() . $this->getPrefix() . date("Y_m_d H_i_s_", $t['sec']) . "{$mSec}_{$suffix}.png";
        ImageUtils::saveImage($image, $filename);
    }
}
