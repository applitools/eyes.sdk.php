<?php
/**
 * Applitools software
 */

namespace Tests\Applitools\Images;

use Applitools\ImageUtils;
use Applitools\Logger;
use Applitools\PrintLogHandler;
use PHPUnit\Framework\TestCase;

class TestImageUtils extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testResize()
    {
        $logger = new Logger();
        $logger->setLogHandler(new PrintLogHandler(true));
        ImageUtils::initLogger($logger);
        echo getcwd() . "\n";

        $image = imagecreatefromjpeg("minions-800x500.jpg");
        $resizedImage = ImageUtils::resizeImage($image, 400, 250);

        $t = gettimeofday();
        $timestamp = date("Y_m_d H_i_s", $t['sec']);
        imagepng($resizedImage, "minions-400x250_$timestamp.png");
    }
}