<?php
/*
 * Applitools software.
 */
class ImageUtils {
    /**
     * Encodes a given image as PNG.
     *
     * @param image The image to encode.
     * @return The PNG bytes representation of the image.
     */
    public static function encodeAsPng(Gregwar\Image\Image $image) {
return "somestring"; //FIXME
        ArgumentGuard::notNull($image, "image");
        $pngBytesStream = new ByteArrayOutputStream();

        try {
            // Get the clipped image in PNG encoding.
            ImageIO::write($image, "png", $pngBytesStream);
            $pngBytesStream->flush();
            $encodedImage = $pngBytesStream->toByteArray();
        } catch (IOException $e) {
            throw new EyesException("Failed to encode image", $e);
        } finally {
            try{
                $pngBytesStream->close();
            } catch (IOException $e) {
                //noinspection ThrowFromFinallyBlock
                throw new EyesException("Failed to close png byte stream", $e);
            }
        }
        return $encodedImage;
    }

    /**
     * Creates a {@code BufferedImage} from an image file specified by {@code
     * path}.
     *
     * @param path The path to the image file.
     * @return A {@code BufferedImage} instance.
     * @throws com.applitools.eyes.EyesException If there was a problem
     * creating the {@code BufferedImage} instance.
     */

    public static function imageFromFile($path){
        try {
            $result = ImageIO::read(new File($path));
        } catch (IOException $e) {
            throw new EyesException("Failed to to load the image bytes from " . $path, $e);
        }
        return $result;
    }

    /**
     * Creates a {@link BufferedImage} from an image file specified by {@code
     * resource}.
     *
     * @param resource The resource path.
     * @return A {@code BufferedImage} instance.
     * @throws EyesException If there was a problem
     * creating the {@code BufferedImage} instance.
     */
    public static function imageFromResource($resource){
        try { //FIXME
            $result = ImageIO::read(ImageUtils::class.getClassLoader() .getResourceAsStream(resource));
        } catch (IOException $e) {
            throw new EyesException(
                    "Failed to to load the image from resource: " . $resource, $e);
        }
        return $result;
    }

    /**
     * Creates a {@code BufferedImage} instance from a base64 encoding of an
     * image's bytes.
     *
     * @param image64 The base64 encoding of an image's bytes.
     * @return A {@code BufferedImage} instance.
     * @throws com.applitools.eyes.EyesException If there was a problem
     * creating the {@code BufferedImage} instance.
     */
   /* public static function imageFromBase64($image64){
        ArgumentGuard::notNullOrEmpty($image64, "image64");

        // Get the image bytes
        //FIXME need to check
        //$imageBytes = Base64::decodeBase64($image64.getBytes(Charset::forName("UTF-8")));
        $imageBytes = base64_decode($image64);
        return self::imageFromBytes($imageBytes);
    }*/

    /**
     *
     * @param image The image from which to get its base64 representation.
     * @return The base64 representation of the image (bytes encoded as PNG).
     */
    public static function base64FromImage(Gregwar\Image\Image $image) {
        ArgumentGuard::notNull($image, "image");

        $imageBytes = self::encodeAsPng($image);
        return Base64::encodeBase64String($imageBytes);
    }

    /**
     * Creates a BufferedImage instance from raw image bytes.
     *
     * @param imageBytes The raw bytes of the image.
     * @return A BufferedImage instance representing the image.
     * @throws com.applitools.eyes.EyesException If there was a problem
     * creating the {@code BufferedImage} instance.
     */
    public static function imageFromBytes($imageBytes){
        try {
            //FIXME need to check
            $image = new Gregwar\Image\Image();
            $image->setResource(imagecreatefromstring($imageBytes));
        } catch (IOException $e) {
            throw new EyesException("Failed to create buffered image!", $e);
        }
        return $image;
    }

    /**
     * Get a copy of the part of the image given by region.
     *
     * @param image The image from which to get the part.
     * @param region The region which should be copied from the image.
     * @return The part of the image.
     */
    public static function getImagePart(Gregwar\Image\Image $image, Region $region) {
        ArgumentGuard::notNull($image, "image");

        // Get the clipped region as a BufferedImage.
        $imagePart = $image->getSubimage($region->getLeft(),
            $region->getTop(), $region->getWidth(), $region->getHeight());
        // IMPORTANT We copy the image this way because just using getSubImage
        // created a later problem (maybe an actual Java bug): the pixels
        // weren't what they were supposed to be.
        $imagePartBytes = self::encodeAsPng($imagePart);
        return self::imageFromBytes($imagePartBytes);
    }

    /**
     * Rotates an image by the given degrees.
     *
     * @param image The image to rotate.
     * @param deg The degrees by which to rotate the image.
     * @return A rotated image.
     */
    public static function rotateImage(Gregwar\Image\Image $image, $deg) {
        ArgumentGuard::notNull($image, "image");

        $radians = Math::toRadians($deg);
//FIXME
        // We need this to calculate the width/height of the rotated image.
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
        return $rotatedImage;
    }

    /**
     * Creates a copy of an image with an updated image type.
     *
     * @param src The image to copy.
     * @param updatedType The type of the copied image.
     *                    See {@link BufferedImage#getType()}.
     * @return A copy of the {@code src} of the requested type.
     */
    public static function copyImageWithType(Gregwar\Image\Image $src, $updatedType) {
        ArgumentGuard::notNull($src, "src");
        $result = new Gregwar\Image\Image($src->getWidth(),
                $src->getHeight(), $updatedType);
        $g2 = $result->createGraphics();
        $g2->drawRenderedImage($src, null);
        $g2->dispose();
        return $result;
    }

    /**
     * Scales an image by the given ratio
     *
     * @param image The image to scale.
     * @param scaleMethod The method used in order to scale the image.
     * @param scaleRatio The ratio by which to scale the image.
     * @return If the scale ratio != 1, returns a new scaled image,
     * otherwise, returns the original image.
     */
    public static function scaleImage(Gregwar\Image\Image $image, ScaleMethod $scaleMethod, $scaleRatio) {
        ArgumentGuard::notNull($image, "image");
        ArgumentGuard::greaterThanZero($scaleRatio, "scaleRatio");

        if ($scaleRatio == 1) {
            return $image;
        }
//FIXME need to check if not scale method
        $scaledWidth = (int) Math::ceil($image->getWidth() * $scaleRatio);
        // doesn't really matter, scale is according to the image width anyways.
        $scaledHeight = (int) Math::ceil($image->getHeight() * $scaleRatio);

        // IMPORTANT you should use the "SPEED" method, which seems to cause the
        // least issues when scaling the image (e.g, off-by-one with region
        // locations after scale down).
        $scaledImage = Scalr::resize($image, $scaleMethod->getMethod(),
                        Scalr::Mode.FIT_TO_WIDTH, $scaledWidth, $scaledHeight);


        // Verify that the scaled image is the same type as the original.
        if ($image->getType() == $scaledImage->getType()) {
            return $scaledImage;

        }
        return self::copyImageWithType($scaledImage, $image->getType());
    }

    /**
     * Removes a given region from the image.
     * @param image The image to crop.
     * @param regionToCrop The region to crop from the image.
     * @return A new image without the cropped region.
     */
    public static function cropImage(Gregwar\Image\Image $image,
                                          Region $regionToCrop) {
        $croppedImage = Scalr::crop($image, $regionToCrop->getLeft(),
                $regionToCrop->getTop(), regionToCrop.getWidth(),
                $regionToCrop->getHeight());

        if ($image->getType() == $croppedImage->getType()) {
            return $croppedImage;
        }

        return copyImageWithType($croppedImage, $image->getType());
    }
}
