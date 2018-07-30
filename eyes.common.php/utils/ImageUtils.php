<?php
/*
 * Applitools software.
 */

namespace Applitools;

use Applitools\Exceptions\EyesException;

class ImageUtils
{

    /** @var  Logger */
    private static $logger;

    public static function initLogger(Logger $logger)
    {
        self::$logger = $logger;
    }

    /**
     * Creates a BufferedImage instance from raw image bytes.
     *
     * @param string $imageBytes The raw bytes of the image.
     * @return resource A BufferedImage instance representing the image.
     * @throws EyesException If there was a problem
     * creating the {@code BufferedImage} instance.
     */
    public static function imageFromBytes($imageBytes)
    {
        try {
            $image = imagecreatefromstring($imageBytes);
            return $image;
        } catch (\Exception $e) {
            throw new EyesException("Failed to create buffered image!", $e);
        }
    }

    /**
     * Get a copy of the part of the image given by region.
     *
     * @param resource $image The image from which to get the part.
     * @param Region $region The region which should be copied from the image.
     * @return resource The part of the image.
     */
    public static function getImagePart($image, Region $region)
    {
        ArgumentGuard::notNull($image, "image");

//        if (self::$logger != null) {
//            self::$logger->verbose("getImagePart (image [{$image->width()}x{$image->height()}], $region)");
//        }

        //self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());
        //$image->crop($region->getLeft(), $region->getTop(), $region->getWidth(), $region->getHeight());
        //self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());

        $image = imagecrop($image, ['x' => $region->getLeft(), 'y' => $region->getTop(), 'width' => $region->getWidth(), 'height' => $region->getHeight()]);
        return $image;
    }

    /**
     * Rotates an image by the given degrees.
     *
     * @param resource $image The image to rotate.
     * @param float $deg The degrees by which to rotate the image.
     * @return resource A rotated image.
     */
    public static function rotateImage($image, $deg)
    {
        /* FIXME
                ArgumentGuard::notNull($image, "image");

               $radians = Math::toRadians($deg);

               // We need this to calculate the width/heightf the rotated image.
            /*   double angleSin = Math.abs(Math.sin(radians));
               double angleCos = Math.abs(Math.cos(radians));

               int originalWidth = image.getWidth();
               double originalHeight = image.getHeight();

               int rotatedWidth = (int) Math.floor(
                       (originalWidth * angleCos) + (originalHeight * angleSin)
               );

               int rotatedHeight = (int) Math.floor(
                       (originalHeight * angleCos) + (originalWidth * angleSin)
               );

               BufferedImage rotatedImage =
                       new BufferedImage(rotatedWidth, rotatedHeight, image.getType());

               Graphics2D g = rotatedImage.createGraphics();

               // Notice we must first perform translation so the rotated result
               // will be properly positioned.
               g.translate((rotatedWidth-originalWidth)/2,
                       (rotatedHeight-originalHeight)/2);

               g.rotate(radians, originalWidth / 2, originalHeight / 2);

               g.drawRenderedImage(image, null);
               g.dispose();
       */
        return $image;
    }

    /**
     * Scales an image by the given ratio
     *
     * @param resource $image The image to scale.
     * @param float $scaleRatio Factor to multiply the image dimensions by
     * @return resource If the scale ratio != 1, returns a new scaled image,
     * otherwise, returns the original image.
     */
    public static function scaleImage($image, $scaleRatio)
    {
        //if you have ScaleProvider use  $scaleProvider->getScaleRatio();
        ArgumentGuard::notNull($image, "image");
        ArgumentGuard::notNull($scaleRatio, "scaleRatio");
        if ($scaleRatio == 1) {
            return $image;
        }

        $wSrc = imagesx($image);
        $hSrc = imagesy($image);

        $imageRatio = $hSrc / $wSrc;
        $scaledWidth = ceil($wSrc * $scaleRatio);
        $scaledHeight = ceil($scaledWidth * $imageRatio);
        //self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());

        $scaledImage = self::resizeImage($image, $scaledWidth, $scaledHeight);

        //self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());

        return $scaledImage;
    }

    /**
     * Scales an image by the given ratio
     *
     * @param resource $image The image to scale.
     * @param int $targetWidth The width to resize the image to
     * @param int $targetHeight The height to resize the image to
     * @return resource If the size of image equal to target size, returns the original image,
     * otherwise, returns a new resized image.
     * @throws \Exception
     */
    public static function resizeImage($image, $targetWidth, $targetHeight)
    {
        ArgumentGuard::notNull($image, "image");
        ArgumentGuard::notNull($targetWidth, "targetWidth");
        ArgumentGuard::notNull($targetHeight, "targetHeight");

        $wSrc = imagesx($image);
        $hSrc = imagesy($image);

        if ($wSrc == $targetWidth && $hSrc == $targetHeight) {
            return $image;
        }

        self::$logger->verbose("original size: {$wSrc}x{$hSrc} ; target size: {$targetWidth}x{$targetHeight}");
        //self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());

        if ($targetWidth > $wSrc || $targetHeight > $hSrc) {
            $resizedImage = self::scaleImageBicubic($image, $targetWidth, $targetHeight);
        } else {
            $resizedImage = self::scaleImageIncrementally($image, $targetWidth, $targetHeight);
        }

        //self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());

        // Verify that the scaled image is the same type as the original.
        // FIXME is type important?
        /*if ($originalType == $resizedImage->getType()) {
            return $resizedImage;
        }

        return $this->copyImageWithType($resizedImage, $originalType);*/
        return $resizedImage;
    }

    private static function scaleImageBicubic($srcImage, $targetWidth, $targetHeight)
    {
        self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());
        $bufDst = imagecreatetruecolor($targetWidth, $targetHeight);
        $wSrc = imagesx($srcImage);
        $hSrc = imagesy($srcImage);
        self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());

        //$bufSrc = imagecreatefrompng($imageName);

        // when dst smaller than src/2, interpolate first to a multiple between 0.5 and 1.0 src, then sum squares
        $wM = max(1, floor($wSrc / $targetWidth));
        $wDst2 = intval($targetWidth * $wM);
        $hM = max(1, floor($hSrc / $targetHeight));
        $hDst2 = intval($targetHeight * $hM);

        // Pass 1 - interpolate rows
        // buf1 has width of dst2 and height of src
        //$buf1 = imagecreatetruecolor($wDst2, $hSrc);
        //imagecolorallocate($buf1, 255, 255, 255);

        //$start = microtime(true);
        self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());
        self::$logger->verbose("trying to allocate {$hSrc} x {$wSrc} pixels = " . $hSrc * $wSrc * 4 . " bytes");
        $pixels = str_repeat("\0\0\0\0", $hSrc * $wSrc);
        self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());
        $pixelsIndexBase = 0;
        for ($h = 0; $h < $hSrc; $h++) {
            for ($w = 0; $w < $wSrc; $w++) {
                $rgba = imagecolorat($srcImage, $w, $h);
                $pixels[$pixelsIndexBase++] = chr(($rgba >> 0) & 0xFF); // b
                $pixels[$pixelsIndexBase++] = chr(($rgba >> 8) & 0xFF); // g
                $pixels[$pixelsIndexBase++] = chr(($rgba >> 16) & 0xFF); // r
                $pixels[$pixelsIndexBase++] = chr(($rgba >> 24) & 0x7F); // a
            }
        }
        self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());
        imagedestroy($srcImage);

        $m = $wM * $hM;
        self::$logger->verbose("m = $m (wM = $wM ; hM = $hM)");

        $pixels2 = str_repeat("\0\0\0\0", $hSrc * $wDst2);
        self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());
        $stride = $wDst2 << 2;
        $wSrcMinus1 = $wSrc - 1;
        $wSrcMinus2 = $wSrc - 2;

        $calcX0a = function (&$pixels, $cIndex, $val, $pixXp1) {
            return ord($pixels[$cIndex]);
        };
        $calcX0b = function (&$pixels, $cIndex, $val, $pixXp1) {
            return ($val << 1) - $pixXp1;
        };
        $calcX3a = function (&$pixels, $cIndex, $val, $pixXp1) {
            return ord($pixels[$cIndex + 8]);
        };
        $calcX3b = function (&$pixels, $cIndex, $val, $pixXp1) {
            return ($pixXp1 << 1) - $val;
        };

        for ($j = 0; $j < $wDst2; $j++) {
            $x = $j * $wSrcMinus1 / $wDst2;
            $xPos = intval(floor($x));
            $t = $x - $xPos;
            $t2 = $t * $t;
            $t3 = $t2 * $t;
            $pixelsIndexBase = $xPos << 2;
            $pixelsIndex = $pixelsIndexBase;

            $calcX0 = ($xPos > 0) ? $calcX0a : $calcX0b;
            $calcX3 = ($xPos < $wSrcMinus2) ? $calcX3a : $calcX3b;

            for ($i = 0; $i < $hSrc; $i++) {
                $cIndex = $pixelsIndex;
                for ($p = 0; $p < 4; $p++) {
                    $val = ord($pixels[$cIndex]);
                    $pixXp1 = ord($pixels[$cIndex + 4]);
                    $x0 = $calcX0($pixels, $cIndex, $val, $pixXp1);
                    $x1 = $val;
                    $x2 = $pixXp1;
                    $x3 = $calcX3($pixels, $cIndex, $val, $pixXp1);

                    $a0 = $x3 - $x2 - $x0 + $x1;
                    $a1 = $x0 - $x1 - $a0;
                    $a2 = $x2 - $x0;
                    $pixels2[$cIndex] = chr(max(0, min(255, ($a0 * $t3) + ($a1 * $t2) + ($a2 * $t) + ($x1))));
                    $cIndex++;
                }
                $pixelsIndex += $stride;
            }
            if ($j % 100 == 0) {
                self::$logger->verbose("phase 1 - now handling column #$j - memory:\t" . memory_get_usage());
            }
        }

        unset($pixels);
        self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());
        $pixels3 = str_repeat("\0\0\0\0", $hDst2 * $wDst2);
        self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());
        $buf2 = imagecreatetruecolor($wDst2, $hDst2);
        self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());


        $stride = $wDst2 << 2;

        $calcY0a = function (&$pixels2, $cIndex, $val, $pixYp1) {
            return ord($pixels2[$cIndex]);
        };

        $calcY0b = function (&$pixels2, $cIndex, $val, $pixYp1) {
            return ($val << 1) - $pixYp1;
        };

        $calcY3a = function (&$pixels2, $cIndex, $val, $pixYp1) {
            return ord($pixels2[$cIndex]);
        };

        $calcY3b = function (&$pixels2, $cIndex, $val, $pixYp1) {
            return ($pixYp1 << 1) - $val;
        };

        for ($i = 0; $i < $hDst2; $i++) {
            $y = $i * ($hSrc - 1) / $hDst2;
            $yPos = intval($y);
            $t = $y - $yPos;
            $t2 = $t * $t;
            $t3 = $t2 * $t;

            $calcY0 = ($yPos > 0) ? $calcY0a : $calcY0b;
            $calcY3 = ($yPos < $hSrc - 2) ? $calcY3a : $calcY3b;
            for ($j = 0; $j < $wDst2; $j++) {
                $rgba = 0;
                $rowIndexBase = (($yPos * $wDst2 + $j) << 2);
                $nextRowIndexBase = $rowIndexBase + $stride;
                $prevRowIndexBase = $rowIndexBase - $stride;
                $index = ($i * $wDst2 + $j) << 2;
                for ($p = 0; $p < 4; $p++) {
                    $val = ord($pixels2[$rowIndexBase + $p]);
                    $pixYp1 = ord($pixels2[$nextRowIndexBase + $p]);
                    $y0 = $calcY0($pixels2, $prevRowIndexBase + $p, $val, $pixYp1);
                    $y1 = $val;
                    $y2 = $pixYp1;
                    $y3 = $calcY3($pixels2, $nextRowIndexBase + $stride + $p, $val, $pixYp1);

                    $a0 = $y3 - $y2 - $y0 + $y1;
                    $a1 = $y0 - $y1 - $a0;
                    $a2 = $y2 - $y0;
                    $pix = max(0, min(255, ($a0 * $t3) + ($a1 * $t2) + ($a2 * $t) + ($y1)));
                    if ($m > 1) {
                        $pixels3[$index + $p] = chr($pix);
                    } else {
                        $rgba |= $pix << ($p << 3);
                    }
                }
                if ($m <= 1) {
                    $a = ($rgba >> 24) & 0x7F;
                    $r = ($rgba >> 16) & 0xFF;
                    $g = ($rgba >> 8) & 0xFF;
                    $b = ($rgba >> 0) & 0xFF;
                    imagesetpixel($buf2, $j, $i, imagecolorallocatealpha($buf2, $r, $g, $b, $a));
                }
            }
            if ($i % 100 == 0) {
                self::$logger->verbose("phase 2 - now handling row #$i - memory:\t" . memory_get_usage());
            }
        }

        unset($pixels2);
        self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());

        // Pass 3 - scale to dst

        if ($m > 1) {
            for ($i = 0; $i < $targetHeight; $i++) {
                for ($j = 0; $j < $targetWidth; $j++) {
                    $r = 0;
                    $g = 0;
                    $b = 0;
                    $a = 0;
                    for ($y = 0; $y < $hM; $y++) {
                        $yPos = $i * $hM + $y;
                        for ($x = 0; $x < $wM; $x++) {
                            $xPos = $j * $wM + $x;
                            $index = intval($yPos * $wDst2 + $xPos) << 2;
                            $b += ord($pixels3[$index + 0]);
                            $g += ord($pixels3[$index + 1]);
                            $r += ord($pixels3[$index + 2]);
                            $a += ord($pixels3[$index + 3]);
                        }
                    }

                    imagesetpixel($bufDst, $j, $i, imagecolorallocatealpha($bufDst, round($r / $m), round($g / $m), round($b / $m), round($a / $m)));
                }
            }
        } else {
            $bufDst = $buf2;
        }

        unset($pixels3);

        self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());

        return $bufDst;
    }

    /**
     * @param $src
     * @param $targetWidth
     * @param $targetHeight
     * @return resource
     * @throws \Exception
     */
    private static function scaleImageIncrementally($src, $targetWidth, $targetHeight)
    {
        $hasReassignedSrc = false;
        $currentWidth = imagesx($src);
        $currentHeight = imagesy($src);

        // For ultra quality should use 7
        $fraction = 2;

        do {
            $prevCurrentWidth = $currentWidth;
            $prevCurrentHeight = $currentHeight;

            // If the current width is bigger than our target, cut it in half and sample again.
            if ($currentWidth > $targetWidth) {
                $currentWidth -= ($currentWidth / $fraction);

                // If we cut the width too far it means we are on our last iteration. Just set it to the target width and finish up.
                if ($currentWidth < $targetWidth)
                    $currentWidth = $targetWidth;
            }

            // If the current height is bigger than our target, cut it in half and sample again.
            if ($currentHeight > $targetHeight) {
                $currentHeight -= ($currentHeight / $fraction);

                // If we cut the height too far it means we are on our last iteration. Just set it to the target height and finish up.
                if ($currentHeight < $targetHeight)
                    $currentHeight = $targetHeight;
            }

            // Stop when we cannot incrementally step down anymore.
            if ($prevCurrentWidth == $currentWidth && $prevCurrentHeight == $currentHeight)
                break;

            // Render the incremental scaled image.
            /*BufferedImage*/
            $incrementalImage = self::scaleImageBicubic($src, $currentWidth, $currentHeight);

            // Before re-assigning our interim (partially scaled) incrementalImage to be the new src image before we iterate around
            // again to process it down further, we want to flush() the previous src image IF (and only IF) it was one of our own temporary
            // BufferedImages created during this incremental down-sampling cycle. If it wasn't one of ours, then it was the original
            // caller-supplied BufferedImage in which case we don't want to flush() it and just leave it alone.
            if ($hasReassignedSrc)
                imagedestroy($src);

            // Now treat our incremental partially scaled image as the src image
            // and cycle through our loop again to do another incremental scaling of it (if necessary).
            $src = $incrementalImage;

            // Keep track of us re-assigning the original caller-supplied source image with one of our interim BufferedImages
            // so we know when to explicitly flush the interim "src" on the next cycle through.
            $hasReassignedSrc = true;
        } while ($currentWidth != $targetWidth || $currentHeight != $targetHeight);

        return $src;
    }


    /**
     * Save image to local file system
     * @param resource $image The image to save.
     * @param string $filename The path to save image
     */
    public static function saveImage($image, $filename)
    {
        self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());
        self::$logger->verbose("saving image $filename");
        imagepng($image, $filename);
    }
}
