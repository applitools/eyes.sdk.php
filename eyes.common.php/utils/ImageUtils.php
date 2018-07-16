<?php
/*
 * Applitools software.
 */

namespace Applitools;

use Applitools\Exceptions\EyesException;
use Gregwar\Image\Image;
use SplFixedArray;

class ImageUtils
{

    /** @var  Logger */
    private static $logger;

    public static function initLogger(Logger $logger)
    {
        self::$logger = $logger;
    }

    /**
     * Encodes a given image as PNG.
     *
     * @param Image $image The image to encode.
     * @return string The PNG bytes representation of the image.
     * @throws EyesException
     */
    public static function encodeAsPng(Image $image)
    {
        ArgumentGuard::notNull($image, "image");
        $pngData = $image->get('png');
        return $pngData;
    }

    /**
     *
     * @param Image $image The image from which to get its base64 representation.
     * @return string The base64 representation of the image (bytes encoded as PNG).
     */
    public static function base64FromImage(Image $image)
    {
        ArgumentGuard::notNull($image, "image");

        $imageBytes = $image->get('png');
        return base64_encode($imageBytes);
    }

    /**
     * Creates a BufferedImage instance from raw image bytes.
     *
     * @param string $imageBytes The raw bytes of the image.
     * @return Image A BufferedImage instance representing the image.
     * @throws EyesException If there was a problem
     * creating the {@code BufferedImage} instance.
     */
    public static function imageFromBytes($imageBytes)
    {
        try {
            //FIXME need to check
            //self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());
            $image = new Image();
            //self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());
            $image->setResource(imagecreatefromstring($imageBytes));
            //self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());
        } catch (\Exception $e) {
            throw new EyesException("Failed to create buffered image!", $e);
        }
        return $image;
    }

    /**
     * Get a copy of the part of the image given by region.
     *
     * @param Image $image The image from which to get the part.
     * @param Region $region The region which should be copied from the image.
     * @return Image The part of the image.
     */
    public static function getImagePart(Image $image, Region $region)
    {
        ArgumentGuard::notNull($image, "image");

        if (self::$logger != null) {
            self::$logger->verbose("getImagePart (image [{$image->width()}x{$image->height()}], $region)");
        }

        //self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());
        $image->crop($region->getLeft(), $region->getTop(), $region->getWidth(), $region->getHeight());
        //self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());

        return $image;
    }

    /**
     * Rotates an image by the given degrees.
     *
     * @param Image $image The image to rotate.
     * @param float $deg The degrees by which to rotate the image.
     * @return Image A rotated image.
     */
    public static function rotateImage(Image $image, $deg)
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
     * @param Image $image The image to scale.
     * @param float $scaleRatio Factor to multiply the image dimensions by
     * @return Image If the scale ratio != 1, returns a new scaled image,
     * otherwise, returns the original image.
     */
    public static function scaleImage(Image $image, $scaleRatio)
    {
        //if you have ScaleProvider use  $scaleProvider->getScaleRatio();
        ArgumentGuard::notNull($image, "image");
        ArgumentGuard::notNull($scaleRatio, "scaleRatio");
        if ($scaleRatio == 1) {
            return $image;
        }

        $imageRatio = $image->height() / $image->width();
        $scaledWidth = ceil($image->width() * $scaleRatio);
        $scaledHeight = ceil($scaledWidth * $imageRatio);
        //self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());

        $scaledImage = self::resizeImage($image, $scaledWidth, $scaledHeight);

        //self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());

        // Verify that the scaled image is the same type as the original.
        //if ($image->getType() == $scaledImage->getType()) {
        /*FIXME need to check*/
        return $scaledImage;
        //}

        //return $this->copyImageWithType($scaledImage, $image->getType());
    }

    /**
     * Scales an image by the given ratio
     *
     * @param Image $image The image to scale.
     * @param int $targetWidth The width to resize the image to
     * @param int $targetHeight The height to resize the image to
     * @return Image If the size of image equal to target size, returns the original image,
     * otherwise, returns a new resized image.
     */
    public static function resizeImage(Image $image, $targetWidth, $targetHeight)
    {
        ArgumentGuard::notNull($image, "image");
        ArgumentGuard::notNull($targetWidth, "targetWidth");
        ArgumentGuard::notNull($targetHeight, "targetHeight");

        if ($image->width() == $targetWidth && $image->height() == $targetHeight) {
            return $image;
        }

        self::$logger->verbose("original size: {$image->width()}x{$image->height()} ; target size: {$targetWidth}x{$targetHeight}");
        //self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());

        // Save original image type
        //$originalType = $image->getType();

        // If type is different then replace it
        /*if ($originalType != BufferedImage::TYPE_4BYTE_ABGR) {
            $image = $this->copyImageWithType($image, BufferedImage::TYPE_4BYTE_ABGR);
        }*///FIXME is type important?

        //$resizedImage;
        if ($targetWidth > $image->width() || $targetHeight > $image->height()) {
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

    private static function interpolateCubic($x0, $x1, $x2, $x3, $t)
    {
        $a0 = $x3 - $x2 - $x0 + $x1;
        $a1 = $x0 - $x1 - $a0;
        $a2 = $x2 - $x0;
        return max(0, min(255, ($a0 * $t * $t * $t) + ($a1 * $t * $t) + ($a2 * $t) + ($x1)));
    }

    private static function scaleImageBicubic(Image $srcImage, $targetWidth, $targetHeight)
    {
        $bufDst = imagecreatetruecolor($targetWidth, $targetHeight);//new DataBufferByte($targetWidth * $targetHeight * 4);
        //$wSrc = $srcImage->width();
        //$hSrc = $srcImage->height();
        $imageName = tempnam(sys_get_temp_dir(), "scale_image_") . ".png";
        $srcImage->save($imageName, "png", 100);
        $size = getimagesize($imageName);
        $wSrc = $size[0];
        $hSrc = $size[1];

        $bufSrc = imagecreatefrompng($imageName);

        // when dst smaller than src/2, interpolate first to a multiple between 0.5 and 1.0 src, then sum squares
        $wM = max(1, floor($wSrc / $targetWidth));
        $wDst2 = $targetWidth * $wM;
        $hM = max(1, floor($hSrc / $targetHeight));
        $hDst2 = $targetHeight * $hM;

        // Pass 1 - interpolate rows
        // buf1 has width of dst2 and height of src
        //$buf1 = imagecreatetruecolor($wDst2, $hSrc);
        //imagecolorallocate($buf1, 255, 255, 255);

        //$start = microtime(true);
        $pixels = new SplFixedArray($hSrc * $wSrc);
        //self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());
        for ($h = 0; $h < $hSrc; $h++) {
            for ($w = 0; $w < $wSrc; $w++) {
                $pixels[$h * $wSrc + $w] = imagecolorat($bufSrc, $w, $h);
            }
            //if ($h % 100 == 0) {
            //    self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());
            //}
        }
        imagedestroy($bufSrc);
        //self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());

        $m = $wM * $hM;

        $pixels2 = new SplFixedArray($hSrc * $wDst2);
        //self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());
        $pixels2Index = 0;
        $pixelsIndexBase = 0;
        for ($i = 0; $i < $hSrc; $i++) {
            for ($j = 0; $j < $wDst2; $j++) {
                $x = $j * ($wSrc - 1) / $wDst2;
                $xPos = floor($x);
                $t = $x - $xPos;

                $pixels2[$pixels2Index] = 0;

                $pixelsIndex = $pixelsIndexBase + $xPos;
                for ($p = 0; $p <= 24; $p += 8) {
                    $val = (($pixels[$pixelsIndex] >> $p) & 0xFF);
                    $x0 = ($xPos > 0) ? (($pixels[$pixelsIndex - 1] >> $p) & 0xFF) : 2 * $val - (($pixels[$pixelsIndex + 1] >> $p) & 0xFF);
                    $x1 = $val;
                    $x2 = (($pixels[$pixelsIndex + 1] >> $p) & 0xFF);
                    $x3 = ($xPos < $wSrc - 2) ? (($pixels[$pixelsIndex + 2] >> $p) & 0xFF) : 2 * (($pixels[$pixelsIndex + 1] >> $p) & 0xFF) - $val;

                    $a0 = $x3 - $x2 - $x0 + $x1;
                    $a1 = $x0 - $x1 - $a0;
                    $a2 = $x2 - $x0;
                    $pixels2[$pixels2Index] |= (max(0, min(255, ($a0 * $t * $t * $t) + ($a1 * $t * $t) + ($a2 * $t) + ($x1))) << $p);
                }
                ++$pixels2Index;
            }
            $pixelsIndexBase += $wSrc;
            //if ($i % 100 == 0) {
            //    self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());
            //}
        }

        unset($pixels);
        $pixels3 = new SplFixedArray($hDst2 * $wDst2);

        $buf2 = imagecreatetruecolor($wDst2, $hDst2);
        //imagecolorallocate($buf2, 255, 255, 255);

        for ($i = 0; $i < $hDst2; $i++) {
            for ($j = 0; $j < $wDst2; $j++) {
                $y = $i * ($hSrc - 1) / $hDst2;
                $yPos = intval($y);
                $t = $y - $yPos;
                $pix = 0;
                for ($p = 0; $p <= 24; $p += 8) {
                    $val = ($pixels2[$yPos * $wDst2 + $j] >> $p) & 0xFF;
                    $y0 = ($yPos > 0) ? (($pixels2[($yPos - 1) * $wDst2 + $j] >> $p) & 0xFF) : 2 * $val - (($pixels2[($yPos + 1) * $wDst2 + $j] >> $p) & 0xFF);
                    $y1 = $val;
                    $y2 = (($pixels2[($yPos + 1) * $wDst2 + $j] >> $p) & 0xFF);
                    $y3 = ($yPos < $hSrc - 2) ? (($pixels2[($yPos + 2) * $wDst2 + $j] >> $p) & 0xFF) : 2 * (($pixels2[($yPos + 1) * $wDst2 + $j] >> $p) & 0xFF) - $val;

                    $a0 = $y3 - $y2 - $y0 + $y1;
                    $a1 = $y0 - $y1 - $a0;
                    $a2 = $y2 - $y0;
                    $pix |= (max(0, min(255, ($a0 * $t * $t * $t) + ($a1 * $t * $t) + ($a2 * $t) + ($y1))) << $p);
                }
                if ($m > 1) {
                    $pixels3[$i * $wDst2 + $j] = $pix;
                } else {
                    $a = ($pix >> 24) & 0x7F;
                    $r = ($pix >> 16) & 0xFF;
                    $g = ($pix >> 8) & 0xFF;
                    $b = ($pix >> 0) & 0xFF;
                    imagesetpixel($buf2, $j, $i, imagecolorallocatealpha($buf2, $r, $g, $b, $a));
                }
            }
        }

        unset($pixels2);

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
                            $pix = $pixels3[$yPos * $wDst2 + $xPos];
                            $a += ($pix >> 24) & 0x7F;
                            $r += ($pix >> 16) & 0xFF;
                            $g += ($pix >> 8) & 0xFF;
                            $b += ($pix >> 0) & 0xFF;
                        }
                    }

                    imagesetpixel($bufDst, $j, $i, imagecolorallocatealpha($bufDst, round($r / $m), round($g / $m), round($b / $m), round($a / $m)));
                }
            }
        } else {
            $bufDst = $buf2;
        }
        $dstImage = new Image();

        $dstImage->setResource($bufDst);
        return $dstImage;
    }

    private static function scaleImageIncrementally(Image $src, $targetWidth, $targetHeight)
    {
        $hasReassignedSrc = false;
        $currentWidth = $src->width();
        $currentHeight = $src->height();

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
                $src->flush();

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
     * @param Image $image The image to save.
     * @param string $filename The path to save image
     * @return Image
     * @throws EyesException
     */
    public static function saveImage(Image &$image, $filename)
    {
        try {
            //self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());
            $image->save($filename, "png", 100);
            //self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());
            self::$logger->verbose("saving image $filename");
            $image = new Image($filename);
            //self::$logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());
            return $image;
        } catch (\Exception $e) {
            throw new EyesException("Failed to save image", $e);
        }
    }
}
