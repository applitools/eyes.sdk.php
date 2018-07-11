<?php

namespace Applitools;

use Gregwar\Image\Image;

/**
 * A debug screenshot provider for saving screenshots to file.
 */
class FileDebugScreenshotsProvider extends DebugScreenshotsProvider
{

    public function save(Image &$image, $suffix)
    {
        $t = gettimeofday();
        $mSec = round($t["usec"] / 1000);
        $filename = $this->getPath() . $this->getPrefix() . date("Y_m_d H_i_s_", $t['sec']) . "{$mSec}_{$suffix}.png";
        $image = ImageUtils::saveImage($image, $filename);
        return $image;
    }
}
