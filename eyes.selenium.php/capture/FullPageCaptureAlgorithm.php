<?php

namespace Applitools\Selenium;

use Applitools\ArgumentGuard;
use Applitools\CoordinatesType;
use Applitools\DebugScreenshotsProvider;
use Applitools\EyesScreenshot;
use Applitools\Selenium\Exceptions\EyesDriverOperationException;
use Applitools\Exceptions\EyesException;
use Applitools\EyesScreenshotFactory;
use Applitools\GeneralUtils;
use Applitools\ImageProvider;
use Applitools\ImageUtils;
use Applitools\Location;
use Applitools\Logger;
use Applitools\PositionProvider;
use Applitools\RectangleSize;
use Applitools\Region;
use Applitools\ScaleProviderFactory;

class FullPageCaptureAlgorithm
{
    // This should pretty much cover all scroll bars (and some fixed position footer elements :).
    const MAX_SCROLL_BAR_SIZE = 0; //FIXME and test
    const MIN_SCREENSHOT_PART_HEIGHT = 0;
    // TODO use scaling overlap offset.
    const SCALE_MARGIN_PX = 5;

    /** @var Logger */
    private $logger;

    public function __construct(Logger $logger)
    {
        ArgumentGuard::notNull($logger, "logger");
        $this->logger = $logger;
    }


    private static function saveDebugScreenshotPart(DebugScreenshotsProvider $debugScreenshotsProvider, $image, Region $region, $name)
    {
        $suffix = "part-{$name}-{$region->getLeft()}_{$region->getTop()}_{$region->getWidth()}x{$region->getHeight()}";
        $debugScreenshotsProvider->save($image, $suffix);
    }

    /**
     * Returns a stitching of a region.
     *
     * @param ImageProvider $imageProvider The provider for the screenshot.
     * @param Region $region The region to stitch. If {@code Region::EMPTY}, the entire image will be stitched.
     * @param PositionProvider $originProvider A provider for scrolling to initial position
     *                       before starting the actual stitching.
     * @param PositionProvider $positionProvider A provider of the scrolling implementation.
     * @param ScaleProviderFactory $scaleProviderFactory The provider which performs the necessary scaling.
     * @param int $waitBeforeScreenshots Time to wait before each screenshot (milliseconds).
     * @param DebugScreenshotsProvider $debugScreenshotsProvider The factory to use for creating debug screenshots
     * @param EyesScreenshotFactory $screenshotFactory The factory to use for creating screenshots from the images.
     * @param IRegionPositionCompensation $regionPositionCompensation
     * @return resource
     * @throws EyesException
     */

    public function getStitchedRegion(ImageProvider $imageProvider,
                                      Region $region, PositionProvider $originProvider,
                                      PositionProvider $positionProvider, ScaleProviderFactory $scaleProviderFactory,
                                      $waitBeforeScreenshots,
                                      DebugScreenshotsProvider $debugScreenshotsProvider,
                                      EyesScreenshotFactory $screenshotFactory,
                                      IRegionPositionCompensation $regionPositionCompensation)
    {
        $this->logger->verbose("getStitchedRegion()");

        ArgumentGuard::notNull($region, "region");
        ArgumentGuard::notNull($positionProvider, "positionProvider");

        $this->logger->verbose("Region to check: $region");

        // Saving the original position (in case we were already in the outermost frame).
        $originalPosition = $originProvider->getState();

        $setPositionRetries = 3;
        do {
            $originProvider->setPosition(new Location(0, 0));
            // Give the scroll time to stabilize
            GeneralUtils::sleep($waitBeforeScreenshots);
            $currentPosition = $originProvider->getCurrentPosition();
        } while ($currentPosition->getX() != 0
        && $currentPosition->getY() != 0
        && (--$setPositionRetries > 0));

        if ($currentPosition->getX() != 0 || $currentPosition->getY() != 0) {
            $originProvider->restoreState($originalPosition);
            throw new EyesException("Couldn't set position to the top/left corner!");
        }

        $this->logger->verbose("Getting top/left image...");

        $image = $imageProvider->getImage();
        $debugScreenshotsProvider->save($image, "original");

        // FIXME - scaling should be refactored
        $imageWidth = imagesx($image);
        $scaleProvider = $scaleProviderFactory->getScaleProvider($imageWidth);
        // Notice that we want to cut/crop an image before we scale it, we need to change
        $pixelRatio = 1 / $scaleProvider->getScaleRatio();

        $this->logger->verbose("Done! Creating screenshot object...");
        // We need the screenshot to be able to convert the region to
        // screenshot coordinates.

        $screenshot = $screenshotFactory->makeScreenshot($image);

        $this->logger->verbose("Done! Getting region in screenshot...");

        $regionInScreenshot = $this->getRegionInScreenshot($region, $image, $pixelRatio, $screenshot, $regionPositionCompensation);

        if (!$regionInScreenshot->isEmpty()) {//  FIXME do not crop image before full screenshot is prepared
            $image = ImageUtils::getImagePart($image, $regionInScreenshot);
            self::saveDebugScreenshotPart($debugScreenshotsProvider, $image, $region, "before-scaled");
        }

        $image = ImageUtils::scaleImage($image, $scaleProvider->getScaleRatio());
        $debugScreenshotsProvider->save($image, "scaled");

        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);

        try {
            $entireSize = $positionProvider->getEntireSize();
            $this->logger->verbose("Entire size of region context: $entireSize");
        } catch (EyesDriverOperationException $e) {
            $this->logger->log("WARNING: Failed to extract entire size of region context" . $e->getMessage());
            $entireSize = new RectangleSize($imageWidth, $imageHeight);
            $this->logger->log("Using image size instead: $entireSize");
        }

        // Notice that this might still happen even if we used
        // "getImagePart", since "entirePageSize" might be that of a frame.
        if ($imageWidth >= $entireSize->getWidth() && $imageHeight >= $entireSize->getHeight()) {
            $originProvider->restoreState($originalPosition);
            return $image;
        }

        $partWidth = $imageWidth;
        $partHeight = $imageHeight;

        // These will be used for storing the actual stitched size (it is
        // sometimes less than the size extracted via "getEntireSize").

        // The screenshot part is a bit smaller than the screenshot, in order to eliminate
        // duplicate bottom scroll bars, as well as fixed position footers.
        $partImageSize = new RectangleSize($partWidth, max($partHeight - self::MAX_SCROLL_BAR_SIZE, self::MIN_SCREENSHOT_PART_HEIGHT));

        $this->logger->verbose("Total size: {$entireSize}, image part size: {$partImageSize}");

        // Getting the list of sub-regions composing the whole region (we'll
        // take screenshot for each one).
        $entirePage = Region::CreateFromLocationAndSize(Location::getZero(), $entireSize);

        $imageParts = $entirePage->getSubRegions($partImageSize);
        //$this->logger->verbose("imageParts: " . var_export($imageParts, true));

        $originalEntirePage = $entireSize;
        $this->logger->verbose("pixelRatio: $pixelRatio");
        $entireSize = $entireSize->scale($pixelRatio);

        $this->logger->verbose("Creating stitchedImage container. Size: $entireSize");
        //Notice stitchedImage uses the same type of image as the screenshots.

        $this->logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());
        $stitchedImage = imagecreatetruecolor($entireSize->getWidth(), $entireSize->getHeight());
        $this->logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());

        $lastSuccessfulLocation = new Location(0, 0);
        $originalStitchedState = $positionProvider->getState();

        // Take screenshot and stitch for each screenshot part.
        $this->logger->verbose("Getting the rest of the image parts...");
        $partImage = null;
        $imagePartWidth = 0;
        $imagePartHeight = 0;
        foreach ($imageParts as $partRegion) {
            $this->logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());

            $this->logger->verbose("Taking screenshot for $partRegion");
            // Set the position to the part's top/left.
            $positionProvider->setPosition($partRegion->getLocation());
            // Giving it time to stabilize.
            GeneralUtils::sleep($waitBeforeScreenshots);
            // Screen size may cause the scroll to only reach part of the way.

            $currentPosition = $positionProvider->getCurrentPosition();
            $this->logger->verbose("Set position to $currentPosition");

            // Actually taking the screenshot.
            $this->logger->verbose("Getting image...");
            $partImage = $imageProvider->getImage();
            //$partImage = $scaleProvider->scaleImage($partImage);

            $this->logger->verbose("Done!");

            if (!$regionInScreenshot->isEmpty()) {
                $partImage = ImageUtils::getImagePart($partImage, $regionInScreenshot);
                $pos = $positionProvider->getCurrentPosition();
                self::saveDebugScreenshotPart($debugScreenshotsProvider, $partImage, $partRegion, "original-scrolled-{$pos->getX()}_{$pos->getY()}");
            }
            //$partImage = ImageUtils::scaleImage($partImage, $scaleProvider->getScaleRatio());
            // Stitching the current part.
            $this->logger->verbose("Stitching part into the image container...");

            $imagePartWidth = imagesx($partImage);
            $imagePartHeight = imagesy($partImage);
            $this->logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());

            imagecopy($stitchedImage, $partImage,
                $currentPosition->getX() * $pixelRatio, $currentPosition->getY() * $pixelRatio,
                0, 0, $imagePartWidth, $imagePartHeight);

            $this->logger->verbose(__FILE__ . ":" . __LINE__ . ":\t" . memory_get_usage());

            //self::saveDebugScreenshotPart($debugScreenshotsProvider, $stitchedImage, $partRegion, "stitched-unscaled-{$currentPosition->getX()}_{$currentPosition->getY()}");

            imagedestroy($partImage);

            $this->logger->verbose("Done!");

            $lastSuccessfulLocation = $currentPosition;
        }

        $debugScreenshotsProvider->save($stitchedImage, "stitched_unscaled");

        $stitchedImage = ImageUtils::scaleImage($stitchedImage, $scaleProvider->getScaleRatio());

        $this->logger->verbose("Stitching done!");
        $positionProvider->restoreState($originalStitchedState);
        $originProvider->restoreState($originalPosition);

        // If the actual image size is smaller than the extracted size, we crop the image.
        $actualImageWidth = $lastSuccessfulLocation->getX() + $imagePartWidth;
        $actualImageHeight = $lastSuccessfulLocation->getY() + $imagePartHeight;
        $this->logger->verbose("Extracted entire size: $entireSize");
        $this->logger->verbose("Actual stitched size: {$actualImageWidth}x{$actualImageHeight}");

        if ($actualImageWidth < $entireSize->getWidth() || $actualImageHeight < $entireSize->getHeight()) {
            $this->logger->verbose("Trimming unnecessary margins...");
            $stitchedImage = ImageUtils::getImagePart($stitchedImage, Region::CreateFromLTWH(0, 0, $originalEntirePage->getWidth(), $originalEntirePage->getHeight()));
            $this->logger->verbose("Done!");
        }
        $debugScreenshotsProvider->save($stitchedImage, "stitched");
        return $stitchedImage;
    }

    /**
     * @param Region $region
     * @param resource $image
     * @param double $pixelRatio
     * @param EyesScreenshot $screenshot
     * @param IRegionPositionCompensation $regionPositionCompensation
     * @return Region
     */
    private function getRegionInScreenshot(Region $region, $image, $pixelRatio,
                                           EyesScreenshot $screenshot,
                                           IRegionPositionCompensation $regionPositionCompensation)
    {
        /** @var Region */
        $regionInScreenshot = $screenshot->getIntersectedRegion(
            $region,
            $region->getCoordinatesType(),
            CoordinatesType::SCREENSHOT_AS_IS);

        $this->logger->verbose("Done! Region in screenshot: $regionInScreenshot");
        $regionInScreenshot = $regionInScreenshot->scale($pixelRatio);
        $this->logger->verbose("Scaled region: $regionInScreenshot");

        if ($regionPositionCompensation == null) {
            $regionPositionCompensation = new NullRegionPositionCompensation();
        }

        $regionInScreenshot = $regionPositionCompensation->compensateRegionPosition($regionInScreenshot, $pixelRatio);

        // Handling a specific case where the region is actually larger than
        // the screenshot (e.g., when body width/height are set to 100%, and
        // an internal div is set to value which is larger than the viewport).

        $w = imagesx($image);
        $h = imagesy($image);
        $regionInScreenshot->intersect(Region::CreateFromLTWH(0, 0, $w, $h));
        $this->logger->verbose("Region after intersect: $regionInScreenshot");

        return $regionInScreenshot;
    }
}
