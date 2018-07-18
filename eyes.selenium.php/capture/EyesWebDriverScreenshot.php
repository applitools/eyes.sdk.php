<?php

/*
 * Applitools software.
 */

namespace Applitools\Selenium;

use Applitools\ArgumentGuard;
use Applitools\CoordinatesType;
use Applitools\Exceptions\CoordinatesTypeConversionException;
use Applitools\Exceptions\EyesException;
use Applitools\PositionProvider;
use Applitools\Exceptions\OutOfBoundsException;
use Applitools\EyesScreenshot;
use Applitools\ImageUtils;
use Applitools\Location;
use Applitools\Logger;
use Applitools\RectangleSize;
use Applitools\Region;

class EyesWebDriverScreenshot extends EyesScreenshot
{

    /** @var Logger */
    private $logger;

    /** @var EyesWebDriver */
    private $driver;

    /** @var FrameChain */
    private $frameChain;

    /** @var Location */
    private $currentFrameScrollPosition;

    /** @var string */
    private $screenshotType;

    // The top/left coordinates of the frame window(!) relative to the top/left
    // of the screenshot. Used for calculations, so can also be outside(!)
    // the screenshot.
    /** @var Location */
    private $frameLocationInScreenshot;

    // The part of the frame window which is visible in the screenshot
    /** @var Region */
    private $frameWindow;

    private static function calcFrameLocationInScreenshot(Logger $logger,
                                                          FrameChain $frameChain,
                                                          $screenshotType = ScreenshotType::ENTIRE_FRAME)
    {
        $frames = $frameChain->getFrames();
        $firstFrame = reset($frames);
        $locationInScreenshot = clone $firstFrame->getLocation();
        // We only consider scroll of the default content if this is
        // a viewport screenshot.
        if ($screenshotType == ScreenshotType::VIEWPORT) {
            $defaultContentScroll = $firstFrame->getParentScrollPosition();
            $locationInScreenshot->offset(-$defaultContentScroll->getX(), -$defaultContentScroll->getY());
        }

        $logger->verbose("Iterating over frames...");
        $firstFrameIterated = false;
        foreach ($frames as $frame) {
            if (!$firstFrameIterated) {
                $firstFrameIterated = true;
                continue;
            }
            $logger->verbose("Getting next frame...");
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
     * @param Logger $logger A Logger instance.
     * @param EyesWebDriver $driver The web driver used to get the screenshot.
     * @param resource $image The actual screenshot image.
     * @param string $screenshotType (Optional) The screenshot's type (e.g., viewport/full page).
     * @param Location $frameLocationInScreenshot (Optional) The current frame's location in the screenshot.
     * @param RectangleSize $entireFrameSize
     * @throws EyesException
     */
    public function __construct(Logger $logger, EyesWebDriver $driver,
                                $image = null,
                                $screenshotType = null,
                                Location $frameLocationInScreenshot = null,
                                RectangleSize $entireFrameSize = null)
    {
        ArgumentGuard::notNull($logger, "logger");
        ArgumentGuard::notNull($driver, "driver");

        parent::__construct($image);  //FIXME need to check

        $this->logger = $logger;
        $this->driver = $driver;

        if (!empty($entireFrameSize)) {
            ArgumentGuard::notNull($entireFrameSize, "entireFrameSize");
            $this->frameChain = $driver->getFrameChain();
            // The frame comprises the entire screenshot.
            $this->screenshotType = ScreenshotType::ENTIRE_FRAME;
            $this->currentFrameScrollPosition = new Location(0, 0);
            $this->frameLocationInScreenshot = new Location(0, 0);
            $this->frameWindow = Region::CreateFromLocationAndSize(Location::getZero(), $entireFrameSize);
        } else {

            $this->screenshotType = $this->updateScreenshotType($screenshotType, $image);
            $positionProvider = new ScrollPositionProvider($logger, $driver);

            $this->frameChain = $driver->getFrameChain();
            $frameSize = $this->getFrameSize($positionProvider);
            $this->currentFrameScrollPosition = $this->getUpdatedScrollPosition($positionProvider);
            $this->frameLocationInScreenshot = $this->getUpdatedFrameLocationInScreenshot($logger, $frameLocationInScreenshot);

            $logger->verbose("Calculating frame window...");
            $this->frameWindow = Region::CreateFromLocationAndSize($this->frameLocationInScreenshot, $frameSize);

            $w = imagesx($image);
            $h = imagesy($image);
            $this->frameWindow->intersect(Region::CreateFromLTWH(0, 0, $w, $h));

            if ($this->frameWindow->getWidth() <= 0 || $this->frameWindow->getHeight() <= 0) {
                throw new EyesException("Got empty frame window for screenshot!");
            }

            $logger->verbose("Done!");
        }
    }

    /**
     * @param Logger $logger
     * @param Location|null $frameLocationInScreenshot
     * @return Location
     */
    private function getUpdatedFrameLocationInScreenshot(Logger $logger, Location $frameLocationInScreenshot = null)
    {
        $this->logger->verbose("\$frameLocationInScreenshot: $frameLocationInScreenshot");
        // This is used for frame related calculations.
        if ($frameLocationInScreenshot == null) {
            if ($this->frameChain->size() > 0) {
                $frameLocationInScreenshot = $this->calcFrameLocationInScreenshot($logger, $this->frameChain, $this->screenshotType);
            } else {
                $frameLocationInScreenshot = new Location(0, 0);
            }
        }
        return $frameLocationInScreenshot;
    }

    /**
     * @return Region The region of the frame which is available in the screenshot,
     * in screenshot coordinates.
     */
    public function getFrameWindow()
    {
        return $this->frameWindow;
    }

    /**
     * @param string|null $screenshotType
     * @param resource $image
     * @return string
     */
    private function updateScreenshotType($screenshotType = null, $image)
    {
        if ($screenshotType == null) {
            $viewportSize = $this->driver->getDefaultContentViewportSize();

            $scaleViewport = $this->driver->getEyes()->shouldStitchContent();

            if ($scaleViewport) {
                $pixelRatio = $this->driver->getEyes()->getDevicePixelRatio();
                $viewportSize = $viewportSize->scale($pixelRatio);
            }

            $w = imagesx($image);
            $h = imagesy($image);
            if ($w <= $viewportSize->getWidth() && $h <= $viewportSize->getHeight()) {
                $screenshotType = ScreenshotType::VIEWPORT;
            } else {
                $screenshotType = ScreenshotType::ENTIRE_FRAME;
            }
        }
        return $screenshotType;
    }


    /**
     * @return FrameChain A copy of the frame chain which was available when the
     * screenshot was created.
     */
    public function getFrameChain()
    {
        return new FrameChain($this->logger, $this->frameChain);
    }

    /**
     * @param Region $region
     * @param bool $throwIfClipped
     * @return EyesScreenshot|EyesWebDriverScreenshot
     * @throws CoordinatesTypeConversionException
     * @throws EyesException
     * @throws OutOfBoundsException
     */
    public function getSubScreenshot(Region $region, $throwIfClipped)
    {
        $this->logger->verbose("getSubScreenshot($region, $throwIfClipped)");

        ArgumentGuard::notNull($region, "region");

        // We calculate intersection based on as-is coordinates.
        /*          $asIsSubScreenshotRegion = $this->getIntersectedRegion($region,
                    $coordinatesType, CoordinatesType::SCREENSHOT_AS_IS);
        */
        $asIsSubScreenshotRegion = $this->getIntersectedRegion($region,
            $region->getCoordinatesType(), CoordinatesType::SCREENSHOT_AS_IS);

        if ($asIsSubScreenshotRegion->isEmpty() || ($throwIfClipped && !$asIsSubScreenshotRegion->getSize()->equals($region->getSize()))) {
            throw new OutOfBoundsException("Region $region is out of screenshot bounds {$this->frameWindow}");
        }

        $subScreenshotImage = ImageUtils::getImagePart($this->image, $asIsSubScreenshotRegion);

        // The frame location in the sub screenshot is the negative of the
        // context-as-is location of the region.
        $contextAsIsRegionLocation =
            $this->convertLocation($asIsSubScreenshotRegion->getLocation(),
                CoordinatesType::SCREENSHOT_AS_IS,
                CoordinatesType::CONTEXT_AS_IS);

        $frameLocationInSubScreenshot = new Location(-$contextAsIsRegionLocation->getX(), -$contextAsIsRegionLocation->getY());

        $result = new EyesWebDriverScreenshot($this->logger,
            $this->driver, $subScreenshotImage, $this->screenshotType,
            $frameLocationInSubScreenshot);
        $this->logger->verbose("Done!");

        return $result;
    }

    /**
     * @param Location $location The location to convert.
     * @param string $from Origin CoordinatesType.
     * @param string $to Target CoordinatesType.
     * @return Location The Converted location.
     * @throws CoordinatesTypeConversionException
     */
    public function convertLocation(Location $location, $from, $to)
    {
        ArgumentGuard::notNull($location, "location");
        ArgumentGuard::notNull($from, "from");
        ArgumentGuard::notNull($to, "to");

        $this->logger->verbose("convertLocation ($location, $from, $to)");
        $this->logger->verbose("scroll position: $this->currentFrameScrollPosition");
        $this->logger->verbose("frame location in screenshot: $this->frameLocationInScreenshot");

        $result = clone $location;

        if ($from == $to) {
            $this->logger->verbose("'from' and 'to' are the same. returning a clone of original location.");

            return $result;
        }

        // If we're not inside a frame, and the screenshot is the entire
        // page, then the context as-is/relative are the same (notice
        // screenshot as-is might be different, e.g.,
        // if it is actually a sub-screenshot of a region).
        if ($this->frameChain->size() == 0 &&
            $this->screenshotType == ScreenshotType::ENTIRE_FRAME
        ) {
            $this->logger->verbose("frameChain size: {$this->frameChain->size()}");

            if (($from == CoordinatesType::CONTEXT_RELATIVE || $from == CoordinatesType::CONTEXT_AS_IS) &&
                $to == CoordinatesType::SCREENSHOT_AS_IS
            ) {
                // If this is not a sub-screenshot, this will have no effect.
                $result->offset($this->frameLocationInScreenshot->getX(), $this->frameLocationInScreenshot->getY());

            } else if ($from == CoordinatesType::SCREENSHOT_AS_IS &&
                ($to == CoordinatesType::CONTEXT_RELATIVE || $to == CoordinatesType::CONTEXT_AS_IS)
            ) {

                $result->offset(-$this->frameLocationInScreenshot->getX(), -$this->frameLocationInScreenshot->getY());
            }

            $this->logger->verbose("result (inside frame): $result");

            return $result;
        }

        switch ($from) {
            case CoordinatesType::CONTEXT_AS_IS:
                switch ($to) {
                    case CoordinatesType::CONTEXT_RELATIVE:
                        $result->offset($this->currentFrameScrollPosition->getX(), $this->currentFrameScrollPosition->getY());
                        break;

                    case CoordinatesType::SCREENSHOT_AS_IS:
                        $result->offset($this->frameLocationInScreenshot->getX(), $this->frameLocationInScreenshot->getY());
                        break;

                    default:
                        throw new CoordinatesTypeConversionException($from, $to);
                }
                break;

            case CoordinatesType::CONTEXT_RELATIVE:
                switch ($to) {
                    case CoordinatesType::SCREENSHOT_AS_IS:
                        // First, convert context-relative to context-as-is.
                        $result->offset(-$this->currentFrameScrollPosition->getX(), -$this->currentFrameScrollPosition->getY());
                        // Now convert context-as-is to screenshot-as-is.
                        $result->offset($this->frameLocationInScreenshot->getX(), $this->frameLocationInScreenshot->getY());
                        break;

                    case CoordinatesType::CONTEXT_AS_IS:
                        $result->offset(-$this->currentFrameScrollPosition->getX(), -$this->currentFrameScrollPosition->getY());
                        break;

                    default:
                        throw new CoordinatesTypeConversionException($from, $to);
                }
                break;

            case CoordinatesType::SCREENSHOT_AS_IS:
                switch ($to) {
                    case CoordinatesType::CONTEXT_RELATIVE:
                        // First convert to context-as-is.
                        $result->offset(-$this->frameLocationInScreenshot->getX(), -$this->frameLocationInScreenshot->getY());
                        // Now convert to context-relative.
                        $result->offset($this->currentFrameScrollPosition->getX(), $this->currentFrameScrollPosition->getY());
                        break;

                    case CoordinatesType::CONTEXT_AS_IS:
                        $result->offset(-$this->frameLocationInScreenshot->getX(), -$this->frameLocationInScreenshot->getY());
                        break;

                    default:
                        throw new CoordinatesTypeConversionException($from, $to);
                }
                break;

            default:
                throw new CoordinatesTypeConversionException($from, $to);
        }

        $this->logger->verbose("result: $result");

        return $result;
    }

    /**
     * @param Location $location
     * @param string $coordinatesType
     * @return Location
     * @throws CoordinatesTypeConversionException
     * @throws OutOfBoundsException
     */
    public function getLocationInScreenshot(Location $location, $coordinatesType)
    {
        $location = $this->convertLocation($location, $coordinatesType, CoordinatesType::SCREENSHOT_AS_IS);

        // Making sure it's within the screenshot bounds
        if (!$this->frameWindow->containsLocation($location)) {
            throw new OutOfBoundsException("Location $location ('$coordinatesType') is not visible in screenshot!");
        }
        return $location;
    }

    /**
     * @param Region $region
     * @param string $originalCoordinatesType
     * @param null $resultCoordinatesType
     * @return Region
     * @throws CoordinatesTypeConversionException
     */
    public function getIntersectedRegion(Region $region,
                                         $originalCoordinatesType,
                                         $resultCoordinatesType = null)
    {
        if ($region->isEmpty()) {
            return clone $region;
        }

        if ($resultCoordinatesType == null) {
            $resultCoordinatesType = $originalCoordinatesType;
        }

        $intersectedRegion = $this->convertRegionLocation($region, $originalCoordinatesType, CoordinatesType::SCREENSHOT_AS_IS);

        switch ($originalCoordinatesType) {
            // If the request was context based, we intersect with the frame
            // window.
            case CoordinatesType::CONTEXT_AS_IS:
            case CoordinatesType::CONTEXT_RELATIVE:
                $intersectedRegion->intersect($this->frameWindow);
                break;

            // If the request is screenshot based, we intersect with the image
            case CoordinatesType::SCREENSHOT_AS_IS:
                $w = imagesx($this->image);
                $h = imagesy($this->image);
                $intersectedRegion->intersect(Region::CreateFromLTWH(0, 0, $w, $h));
                break;

            default:
                throw new CoordinatesTypeConversionException("Unknown coordinates type: '$originalCoordinatesType'");

        }

        // If the intersection is empty we don't want to convert the
        // coordinates.
        if ($intersectedRegion->isEmpty()) {
            return $intersectedRegion;
        }

        // Converting the result to the required coordinates type.
        $intersectedRegion = $this->convertRegionLocation($intersectedRegion, CoordinatesType::SCREENSHOT_AS_IS, $resultCoordinatesType);

        return $intersectedRegion;
    }

    /**
     * Gets the elements region in the screenshot.
     *
     * @param EyesRemoteWebElement $element The element which region we want to intersect.
     * @return Region The intersected region, in {@code SCREENSHOT_AS_IS} coordinates type.
     * @throws CoordinatesTypeConversionException
     */
    public function getIntersectedRegionElement(EyesRemoteWebElement $element) //FIXME need to change back the title
    {
        ArgumentGuard::notNull($element, "element");

        /*$pl = $element->getLocation();
        $ds = $element->getSize();

        $elementRegion = Region::CreateFromLTWH($pl->getX(), $pl->getY(), $ds->getWidth(), $ds->getHeight());*/


        $elementRegion = $element->getClientAreaBounds();

        // Since the element coordinates are in context relative
        $elementRegion = $this->getIntersectedRegion($elementRegion, CoordinatesType::CONTEXT_RELATIVE);

        if (!$elementRegion->isEmpty()) {
            $elementRegion = $this->convertRegionLocation($elementRegion,
                CoordinatesType::CONTEXT_RELATIVE,
                CoordinatesType::SCREENSHOT_AS_IS);
        }

        return $elementRegion;
    }

    /**
     * @param PositionProvider $positionProvider
     * @return RectangleSize
     */
    private function getFrameSize(PositionProvider $positionProvider)
    {
        if ($this->frameChain->size() != 0) {
            $frameSize = $this->frameChain->getCurrentFrameInnerSize();
        } else {
            // get entire page size might throw an exception for applications
            // which don't support Javascript (e.g., Appium). In that case
            // we'll use the viewport size as the frame's size.
            try {
                $frameSize = $positionProvider->getEntireSize();
            } catch (EyesDriverOperationException $e) {
                $frameSize = $this->driver->getDefaultContentViewportSize();
            }
        }
        return $frameSize;
    }

    /**
     * @param PositionProvider $positionProvider
     * @return Location
     */
    private function getUpdatedScrollPosition(PositionProvider $positionProvider)
    {
        // Getting the scroll position. For native Appium apps we can't get the
        // scroll position, so we use (0,0)
        try {
            $sp = $positionProvider->getCurrentPosition();
        } catch (EyesDriverOperationException $e) {
            $sp = new Location(0, 0);
        }
        return $sp;
    }
}
