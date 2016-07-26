<?php

/*
 * Applitools software.
 */

class EyesWebDriverScreenshot extends EyesScreenshot
{

    //private enum ScreenshotType {VIEWPORT, ENTIRE_FRAME} //FIXME

    private $logger; //Logger
    private $driver; //EyesWebDriver
    private $frameChain; //FrameChain
    private $scrollPosition; //Location
    private $screenshotType; //ScreenshotType

    // The top/left coordinates of the frame window(!) relative to the top/left
    // of the screenshot. Used for calculations, so can also be outside(!)
    // the screenshot.
    private $frameLocationInScreenshot; //Location

    // The part of the frame window which is visible in the screenshot
    private $frameWindow; //Region

    private static function calcFrameLocationInScreenshot(Logger $logger,
                                                          FrameChain $frameChain, ScreenshotType $screenshotType)
    {

        $logger->verbose("Getting first frame..");
        $frameIterator = $frameChain->iterator();
        $firstFrame = $frameIterator->next();
        $logger->verbose("Done!");
        $locationInScreenshot = new Location($firstFrame->getLocation());

        // We only consider scroll of the default content if this is
        // a viewport screenshot.
        if ($screenshotType == ScreenshotType::VIEWPORT) {
            $defaultContentScroll = $firstFrame->getParentScrollPosition();
            $locationInScreenshot->offset(-$defaultContentScroll->getX(), -$defaultContentScroll->getY());
        }

        $logger->verbose("Iterating over frames..");
        while ($frameIterator->hasNext()) {
            $logger->verbose("Getting next frame...");
            $frame = $frameIterator->next();
            $logger->verbose("Done!");
            $frameLocation = $frame->getLocation();
            // For inner frames we must consider the scroll
            $frameParentScrollPosition = $frame->getParentScrollPosition();
            // Offsetting the location in the screenshot
            $locationInScreenshot->offset(
                $frameLocation->getX() - $frameParentScrollPosition->getX(),
                $frameLocation->getY() - $frameParentScrollPosition->getY());
        }
        $logger->verbose("Done!");
        return $locationInScreenshot;
    }

    /**
     * @param logger                     A Logger instance.
     * @param driver                     The web driver used to get the screenshot.
     * @param image                      The actual screenshot image.
     * @param screenshotType             (Optional) The screenshot's type (e.g.,
     *                                   viewport/full page).
     * @param frameLocationInScreenshot  (Optional) The current frame's
     *                                   location in the screenshot.
     */
    public function __construct(Logger $logger, EyesWebDriver $driver,
                                BufferedImage $image, ScreenshotType $screenshotType = null,
                                Location $frameLocationInScreenshot = null,
                                RectangleSize $entireFrameSize = null)
    {
        if (!empty($entireFrameSize)) {
            parent::__construct($image);
            ArgumentGuard::notNull($logger, "logger");
            ArgumentGuard::notNull($driver, "driver");
            ArgumentGuard::notNull($entireFrameSize, "entireFrameSize");
            $this->logger = $logger;
            $this->driver = $driver;
            $this->frameChain = $driver->getFrameChain();
            // The frame comprises the entire screenshot.
            $this->screenshotType = ScreenshotType::ENTIRE_FRAME;
            $this->scrollPosition = new Location(0, 0);
            $this->frameLocationInScreenshot = new Location(0, 0);
            $this->frameWindow = new Region(new Location(0, 0), $entireFrameSize);
        } else {
            parent::__construct($image);
            ArgumentGuard::notNull($logger, "logger");
            ArgumentGuard::notNull($driver, "driver");
            $this->logger = $logger;
            $this->driver = $driver;
            $positionProvider = new ScrollPositionProvider($logger, $driver);
            $viewportSize = $driver->getDefaultContentViewportSize();
            $this->frameChain = $driver->getFrameChain();
            // If we're inside a frame, then the frame size is given by the frame
            // chain. Otherwise, it's the size of the entire page.
            if ($this->frameChain->size() != 0) {
                $frameSize = $this->frameChain->getCurrentFrameSize();
            } else {
                // get entire page size might throw an exception for applications
                // which don't support Javascript (e.g., Appium). In that case
                // we'll use the viewport size as the frame's size.
                try {
                    $fs = $positionProvider->getEntireSize();
                } catch (EyesDriverOperationException $e) {
                    $fs = $viewportSize;
                }
                $frameSize = $fs;
            }
            // Getting the scroll position. For native Appium apps we can't get the
            // scroll position, so we use (0,0)
            try {
                $sp = $positionProvider->getCurrentPosition();
            } catch (EyesDriverOperationException $e) {
                $sp = new Location(0, 0);
            }
            $scrollPosition = $sp;

            if ($screenshotType == null) {
                if ($image->getWidth() <= $viewportSize->getWidth()
                    && $image->getHeight() <= $viewportSize->getHeight()
                ) {
                    $screenshotType = ScreenshotType::VIEWPORT;
                } else {
                    $screenshotType = ScreenshotType::ENTIRE_FRAME;
                }
            }
            $this->screenshotType = $screenshotType;

            // This is used for frame related calculations.
            if ($frameLocationInScreenshot == null) {
                if ($this->frameChain->size() > 0) {
                    $frameLocationInScreenshot =
                        calcFrameLocationInScreenshot($logger, $this->frameChain,
                            $this->screenshotType);
                } else {
                    $frameLocationInScreenshot = new Location(0, 0);
                    if ($this->screenshotType == ScreenshotType::VIEWPORT) {
                        $frameLocationInScreenshot->offset(-$scrollPosition->getX(),
                            -$scrollPosition->getY());
                    }
                }
            }
            $this->frameLocationInScreenshot = $frameLocationInScreenshot;
            $logger->verbose("Calculating frame window..");
            $this->frameWindow = new Region(null, null, null, null,
                                        $frameLocationInScreenshot, $frameSize);

            $this->frameWindow->intersect(new Region(/*FIXME*/0, 0, $image->getWidth(), $image->getHeight()));

            if ($this->frameWindow->getWidth() <= 0 ||
                $this->frameWindow->getHeight() <= 0
            ) {
                throw new EyesException("Got empty frame window for screenshot!");
            }

            $logger->verbose("Done!");
        }

    }

    /**
     * @return The region of the frame which is available in the screenshot,
     * in screenshot coordinates.
     */
    public function getFrameWindow()
    {
        return $this->frameWindow;
    }

    /**
     * @return A copy of the frame chain which was available when the
     * screenshot was created.
     */
    public function getFrameChain()
    {
        return new FrameChain($this->logger, $this->frameChain);
    }

    public function getSubScreenshot(Region $region, CoordinatesType $coordinatesType, $throwIfClipped)
    {

        $this->logger->verbose(sprintf("getSubScreenshot([%s], %s, %b)",
            $region, $coordinatesType, $throwIfClipped));

        ArgumentGuard::notNull($region, "region");
        ArgumentGuard::notNull($coordinatesType, "coordinatesType");

        // We calculate intersection based on as-is coordinates.
        $asIsSubScreenshotRegion = $this->getIntersectedRegion($region,
            $coordinatesType, CoordinatesType::SCREENSHOT_AS_IS);

        if ($asIsSubScreenshotRegion->isEmpty() ||
            ($throwIfClipped && !$asIsSubScreenshotRegion->getSize()->equals(
                    $region->getSize()))
        ) {
            throw new OutOfBoundsException(sprintf(
                "Region [%s, (%s)] is out of screenshot bounds [%s]",
                $region, $coordinatesType, $this->frameWindow));
        }

        $subScreenshotImage = ImageUtils::getImagePart($this->image, $asIsSubScreenshotRegion);

        // The frame location in the sub screenshot is the negative of the
        // context-as-is location of the region.
        $contextAsIsRegionLocation =
            convertLocation($asIsSubScreenshotRegion->getLocation(),
                CoordinatesType::SCREENSHOT_AS_IS,
                CoordinatesType::CONTEXT_AS_IS);

        $frameLocationInSubScreenshot =
            new Location(-$contextAsIsRegionLocation->getX(),
                -$contextAsIsRegionLocation->getY());

        $result = new EyesWebDriverScreenshot($this->logger,
            $this->driver, $subScreenshotImage, $this->screenshotType,
            $frameLocationInSubScreenshot);
        $this->logger->verbose("Done!");
        return $result;
    }

    public function convertLocation(Location $location,
                                    CoordinatesType $from, CoordinatesType $to)
    {

        ArgumentGuard::notNull($location, "location");
        ArgumentGuard::notNull($from, "from");
        ArgumentGuard::notNull($to, "to");

        $result = new Location($location);

        if ($from == $to) {
            return $result;
        }

        // If we're not inside a frame, and the screenshot is the entire
        // page, then the context as-is/relative are the same (notice
        // screenshot as-is might be different, e.g.,
        // if it is actually a sub-screenshot of a region).
        if ($this->frameChain->size() == 0 &&
            $this->screenshotType == ScreenshotType::ENTIRE_FRAME
        ) {
            if (($from == CoordinatesType::CONTEXT_RELATIVE
                    || $from == CoordinatesType::CONTEXT_AS_IS)
                && $to == CoordinatesType::SCREENSHOT_AS_IS
            ) {

                // If this is not a sub-screenshot, this will have no effect.
                $result->offset($this->frameLocationInScreenshot->getX(),
                    $this->frameLocationInScreenshot->getY());

            } else if ($from == CoordinatesType::SCREENSHOT_AS_IS &&
                ($to == CoordinatesType::CONTEXT_RELATIVE
                    || $to == CoordinatesType::CONTEXT_AS_IS)
            ) {

                $result->offset(-$this->frameLocationInScreenshot->getX(),
                    -$this->frameLocationInScreenshot->getY());
            }
            return $result;
        }

        switch ($from) {
            case CONTEXT_AS_IS:
                switch ($to) {
                    case CONTEXT_RELATIVE:
                        $result->offset($this->scrollPosition->getX(),
                            $this->scrollPosition->getY());
                        break;

                    case SCREENSHOT_AS_IS:
                        $result->offset($this->frameLocationInScreenshot->getX(),
                            $this->frameLocationInScreenshot->getY());
                        break;

                    default:
                        throw new CoordinatesTypeConversionException(from, to);
                }
                break;

            case CONTEXT_RELATIVE:
                switch ($to) {
                    case SCREENSHOT_AS_IS:
                        // First, convert context-relative to context-as-is.
                        $result->offset(-$this->scrollPosition->getX(),
                            -$this->scrollPosition->getY());
                        // Now convert context-as-is to screenshot-as-is.
                        $result->offset($this->frameLocationInScreenshot->getX(),
                            $this->frameLocationInScreenshot->getY());
                        break;

                    case CONTEXT_AS_IS:
                        $result->offset(-$this->scrollPosition->getX(),
                            -$this->scrollPosition->getY());
                        break;

                    default:
                        throw new CoordinatesTypeConversionException($from, $to);
                }
                break;

            case SCREENSHOT_AS_IS:
                switch ($to) {
                    case CONTEXT_RELATIVE:
                        // First convert to context-as-is.
                        $result->offset(-$this->frameLocationInScreenshot->getX(),
                            -$this->frameLocationInScreenshot->getY());
                        // Now convert to context-relative.
                        $result->offset($this->scrollPosition->getX(),
                            $this->scrollPosition->getY());
                        break;

                    case CONTEXT_AS_IS:
                        $result->offset(-$this->frameLocationInScreenshot->getX(),
                            -$this->frameLocationInScreenshot->getY());
                        break;

                    default:
                        throw new CoordinatesTypeConversionException($from, $to);
                }
                break;

            default:
                throw new CoordinatesTypeConversionException($from, $to);
        }
        return $result;
    }

    public function getLocationInScreenshot(Location $location,
                                            CoordinatesType $coordinatesType)/* throws OutOfBoundsException FIXME*/
    {

        $location = $this->convertLocation($location, $coordinatesType,
            CoordinatesType::SCREENSHOT_AS_IS);

        // Making sure it's within the screenshot bounds
        if (!$this->frameWindow->contains($location)) {
            throw new OutOfBoundsException(sprintf(
                "Location %s ('%s') is not visible in screenshot!", $location,
                $coordinatesType));
        }
        return $location;
    }

    public function getIntersectedRegion(Region $region,
                                         CoordinatesType $originalCoordinatesType,
                                         CoordinatesType $resultCoordinatesType)
    {
        if ($region->isEmpty()) {
            return new Region($region);
        }

        $intersectedRegion = $this->convertRegionLocation($region,
            $originalCoordinatesType, CoordinatesType::SCREENSHOT_AS_IS);

        switch ($originalCoordinatesType) {
            // If the request was context based, we intersect with the frame
            // window.
            case CONTEXT_AS_IS:
            case CONTEXT_RELATIVE:
                $intersectedRegion->intersect($this->frameWindow);
                break;

            // If the request is screenshot based, we intersect with the image
            case SCREENSHOT_AS_IS:
                $intersectedRegion->intersect(new Region(0, 0,
                    $this->image->getWidth(), $this->image->getHeight()));
                break;

            default:
                throw new CoordinatesTypeConversionException(
                    sprintf("Unknown coordinates type: '%s'",
                        $originalCoordinatesType));

        }

        // If the intersection is empty we don't want to convert the
        // coordinates.
        if ($intersectedRegion->isEmpty()) {
            return $intersectedRegion;
        }

        // Converting the result to the required coordinates type.
        $intersectedRegion = convertRegionLocation($intersectedRegion,
            CoordinatesType::SCREENSHOT_AS_IS, $resultCoordinatesType);

        return $intersectedRegion;
    }

    /**
     * Gets the elements region in the screenshot.
     *
     * @param element The element which region we want to intersect.
     * @return The intersected region, in {@code SCREENSHOT_AS_IS} coordinates
     * type.
     */
    public function getIntersectedRegionElement(WebElement $element) //FIXME need to change back the title
    {
        ArgumentGuard::notNull($element, "element");

        $pl = $element->getLocation();
        $ds = $element->getSize();

        $elementRegion = new Region($pl->getX(), $pl->getY(), $ds->getWidth(), $ds->getHeight());

        // Since the element coordinates are in context relative
        $elementRegion = $this->getIntersectedRegion($elementRegion,
            CoordinatesType::CONTEXT_RELATIVE);

        if (!$elementRegion->isEmpty()) {
            $elementRegion = $this->convertRegionLocation($elementRegion,
                CoordinatesType::CONTEXT_RELATIVE,
                CoordinatesType::SCREENSHOT_AS_IS);
        }

        return $elementRegion;
    }
}
