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
     * @throws \Exception
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

        $scaledImage = self::resizeImage($image, $scaledWidth, $scaledHeight);

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

        return imagescale($image, $targetWidth, $targetHeight);
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
