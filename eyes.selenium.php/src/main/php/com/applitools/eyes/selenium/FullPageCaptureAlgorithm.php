<?php

class FullPageCaptureAlgorithm {
    // This should pretty much cover all scroll bars (and some fixed position
    // footer elements :).
    const MAX_SCROLL_BAR_SIZE = 0; //FIXME and test
    const MIN_SCREENSHOT_PART_HEIGHT = 0;
    // TODO use scaling overlap offset.
    const SCALE_MARGIN_PX = 5;

    private $logger; //Logger

    public function __construct(Logger $logger) {
        ArgumentGuard::notNull($logger, "logger");
        $this->logger = $logger;
    }

    /**
     * Returns a stitching of a region.
     *
     * @param ImageProvider $imageProvider The provider for the screenshot.
     * @param RegionProvider $regionProvider A provider of the region to stitch. If {@code
     *                       getRegion} returns {@code Region.EMPTY}, the entire image will be stitched.
     * @param PositionProvider $originProvider A provider for scrolling to initial position
     *                       before starting the actual stitching.
     * @param PositionProvider $positionProvider A provider of the scrolling implementation.
     * @param ScaleProviderFactory $scaleProviderFactory The provider which performs the necessary scaling.
     * @param CutProvider $cutProvider
     * @param int $waitBeforeScreenshots Time to wait before each screenshot (milliseconds).
     * @param EyesScreenshotFactory $screenshotFactory The factory to use for creating screenshots
     *                          from the images.
     * @return An image which represents the stitched region.
     * @throws EyesException
     */

    public function getStitchedRegion(ImageProvider $imageProvider,
               RegionProvider $regionProvider, PositionProvider $originProvider,
               PositionProvider $positionProvider, ScaleProviderFactory $scaleProviderFactory,
               CutProvider $cutProvider, $waitBeforeScreenshots,
               EyesScreenshotFactory $screenshotFactory) {
        $this->logger->verbose("getStitchedRegion()");

        ArgumentGuard::notNull($regionProvider, "regionProvider");
        ArgumentGuard::notNull($positionProvider, "positionProvider");

        $this->logger->verbose(sprintf("Region to check: %s",
                json_encode($regionProvider->getRegion())));
        $this->logger->verbose(sprintf("Coordinates type: %s",
                $regionProvider->getCoordinatesType()));

        // Saving the original position (in case we were already in the
        // outermost frame).
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
            throw new EyesException(
                    "Couldn't set position to the top/left corner!");
        }

        $this->logger->verbose("Getting top/left image...");
        $this->image = $imageProvider->getImage();

        // FIXME - scaling should be refactored
        $scaleProvider = $scaleProviderFactory->getScaleProvider($this->image->width());
        // Notice that we want to cut/crop an image before we scale it, we need to change
        $pixelRatio = 1 / $scaleProvider->getScaleRatio();

        // FIXME - cropping should be overlaid, so a single cut provider will only handle a single part of the image.

        $cutProvider = $cutProvider->scale($pixelRatio);
        $this->image = $cutProvider->cut($this->image);

        $this->logger->verbose("Done! Creating screenshot object...");
        // We need the screenshot to be able to convert the region to
        // screenshot coordinates.

        $screenshot = $screenshotFactory->makeScreenshot($this->image);

        $this->logger->verbose("Done! Getting region in screenshot...");

        $regionInScreenshot = $screenshot->convertRegionLocation(
                        $regionProvider->getRegion(),
                        $regionProvider->getCoordinatesType(),
                        CoordinatesType::SCREENSHOT_AS_IS);

        $this->logger->verbose("Done! Region in screenshot: " . json_encode($regionInScreenshot));

        // Handling a specific case where the region is actually larger than
        // the screenshot (e.g., when body width/height are set to 100%, and
        // an internal div is set to value which is larger than the viewport).
        $regionInScreenshot->intersect(
                new Region(0, 0, $this->image->width(),
                    $this->image->height()));
        $this->logger->verbose("Region after intersect: " . json_encode($regionInScreenshot));

        $partWidth = $this->image->width();
        $partHeight = $this->image->height();

        if (!$regionInScreenshot->isEmpty()) {//  FIXME do not crop image before full screenshot be prepared
            $this->image = ImageUtils::getImagePart($this->image, $regionInScreenshot);
            $partWidth = $regionInScreenshot->getWidth();
            $partHeight = $regionInScreenshot->getHeight();
        }

        try {
            $entireSize = $positionProvider->getEntireSize();
            $this->logger->verbose("Entire size of region context: " . json_encode($entireSize));
        } catch (EyesDriverOperationException $e) {
            $this->logger->log(
                    "WARNING: Failed to extract entire size of region context"
                            . $e->getMessage());
            $this->logger->log("Using image size instead: "
                    . $this->image->width() . "x" . $this->image->height());
            $entireSize = new RectangleSize($this->image->width(), $this->image->height());
        }
/*
        // Notice that this might still happen even if we used
        // "getImagePart", since "entirePageSize" might be that of a frame.
        if ($this->image->width() >= $entireSize->getWidth() &&
            $this->image->height() >= $entireSize->getHeight()) {
            $originProvider->restoreState($originalPosition);
            return $this->image;
        }*/

        // These will be used for storing the actual stitched size (it is
        // sometimes less than the size extracted via "getEntireSize").

        // The screenshot part is a bit smaller than the screenshot sxxize,
        // in order to eliminate duplicate bottom scroll bars, as well as fixed
        // position footers.
        $partImageSize =
                new RectangleSize($partWidth,
                        max($partHeight - self::MAX_SCROLL_BAR_SIZE,
                                self::MIN_SCREENSHOT_PART_HEIGHT));

        $this->logger->verbose(sprintf("Total size: %s, image part size: %s",
                json_encode($entireSize), json_encode($partImageSize)));

        // Getting the list of sub-regions composing the whole region (we'll
        // take screenshot for each one).
        $entirePage = new Region(null, null, null, null, Location::getZero(), $entireSize); //FIXME Region construct was merged


        $imageParts = $entirePage->getSubRegions($partImageSize);
        $this->logger->verbose("Creating stitchedImage container. Size: " . json_encode($entireSize));
        //Notice stitchedImage uses the same type of image as the screenshots.

        $stitchedImage = Gregwar\Image\Image::create(
                $entireSize->getWidth(), $entireSize->getHeight());

        $this->logger->verbose("Done! Adding initial screenshot..");
        // Starting with the screenshot we already captured at (0,0).
        $initialPart = clone $this->image;
        $this->logger->verbose(sprintf("Initial part:(0,0)[%d x %d]",
                $initialPart->width(), $initialPart->height()));
        $stitchedImage->merge($initialPart,0,0);
        //$stitchedImage->getRaster()->setRect(0, 0, $initialPart); FIXME need to check
        $this->logger->verbose("Done!");

        $lastSuccessfulLocation = new Location(0, $initialPart->height());
        $lastSuccesfulPartSize = new RectangleSize($initialPart->width(),
                $initialPart->height());

        $originalStitchedState = $positionProvider->getState();

        // Take screenshot and stitch for each screenshot part.
        $this->logger->verbose("Getting the rest of the image parts...");
        $partImage = null;

        foreach($imageParts as $partRegion) {
            // Skipping screenshot for 0,0 (already taken)
            if ($partRegion->getLeft() == 0 && $partRegion->getTop() == 0) {
                continue;
            }
            $this->logger->verbose(sprintf("Taking screenshot for %s",
                    json_encode($partRegion)));
            // Set the position to the part's top/left.
            $positionProvider->setPosition($partRegion->getLocation());
            // Giving it time to stabilize.
            GeneralUtils::sleep($waitBeforeScreenshots);
            // Screen size may cause the scroll to only reach part of the way.

            $currentPosition = $positionProvider->getCurrentPosition();
            $this->logger->verbose(sprintf("Set position to %s",
                    json_encode($currentPosition)));

            // Actually taking the screenshot.
            $this->logger->verbose("Getting image...");
            $partImage = $imageProvider->getImage();
//$partImage = $scaleProvider->scaleImage($partImage);

            // FIXME - cropping should be overlaid (see previous comment re cropping)
            $partImage = $cutProvider->cut($partImage);

            $this->logger->verbose("Done!");

            if (!$regionInScreenshot->isEmpty()) {
                $partImage = ImageUtils::getImagePart($partImage,
                        $regionInScreenshot);
            }
//            $partImage = ImageUtils::scaleImage($partImage, $scaleProvider->getScaleRatio());
            // Stitching the current part.
            $this->logger->verbose("Stitching part into the image container...");
            //$stitchedImage->getRaster()->setRect($currentPosition->getX(),
            //        $currentPosition->getY(), $partImage->getData());
            $stitchedImage->merge($partImage,$currentPosition->getX(),$currentPosition->getY());
            $this->logger->verbose("Done!");

            $lastSuccessfulLocation = $currentPosition;
        }

        $stitchedImage = ImageUtils::scaleImage($stitchedImage, $scaleProvider->getScaleRatio());
        if ($partImage != null) {
            $lastSuccesfulPartSize = new RectangleSize($partImage->width(),
                    $partImage->height());
        }

        $this->logger->verbose("Stitching done!");
        $positionProvider->restoreState($originalStitchedState);
        $originProvider->restoreState($originalPosition);

        // If the actual image size is smaller than the extracted size, we
        // crop the image.
        $actualImageWidth = $lastSuccessfulLocation->getX() +
                $lastSuccesfulPartSize->getWidth();
        $actualImageHeight = $lastSuccessfulLocation->getY() +
                $lastSuccesfulPartSize->getHeight();
        $this->logger->verbose("Extracted entire size: " . json_encode($entireSize));
        $this->logger->verbose("Actual stitched size: " . $actualImageWidth . "x" .
                $actualImageHeight);

        if ($actualImageWidth < $stitchedImage->width() ||
                $actualImageHeight < $stitchedImage->height()) {
            $this->logger->verbose("Trimming unnecessary margins..");
            $stitchedImage = ImageUtils::getImagePart($stitchedImage,
                    new Region(0, 0, $actualImageWidth, $actualImageHeight));
            $this->logger->verbose("Done!");
        }
        return $stitchedImage;
    }
}
