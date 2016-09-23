<?php
/**
 * Encapsulates a screenshot taken by the images SDK.
 */
class EyesImagesScreenshot extends EyesScreenshot
{

    // The screenshot region in coordinates relative to the "entire screen"
    // (e.g., relative to the default content in case of a web page).
    protected $bounds; //Region

    /**
     * @param image The screenshot image.
     * @param location The top/left coordinates of the screenshot in context
     *                 relative coordinates type.
     */
    public function __construct(Gregwar\Image\Image $image = null, Location $location = null)
    {
        if (!empty($image)) {
            if (empty($location)) {
                $location = new Location(0, 0);
                $rectangleSize = new RectangleSize($image->getWidth(), $image->getHeight());
                $this->bounds = new Region($location, $rectangleSize);
            }
        }
    }


    public function getSubScreenshot(Region $region, CoordinatesType $coordinatesType, $throwIfClipped)
    {

        ArgumentGuard::notNull($region, "region");
        ArgumentGuard::notNull($coordinatesType, "coordinatesType");

        // We want to get the sub-screenshot in as-is coordinates type.
        $subScreenshotRegion = getIntersectedRegion($region, $coordinatesType, CoordinatesType::SCREENSHOT_AS_IS);

        if ($subScreenshotRegion->isEmpty() || ($throwIfClipped && (!$subScreenshotRegion->getSize() == $region->getSize()))) {
            throw new OutOfBoundsException(sptintf(
                "Region [%s, (%s)] is out of screenshot bounds [%s]",
                $region, json_encode($coordinatesType), $bounds));
        }

        $subScreenshotImage = ImageUtils::getImagePart($image, $subScreenshotRegion);

        // Notice that we need the bounds-relative coordinates as parameter
        // for new sub-screenshot.
        $relativeSubScreenshotRegion = convertRegionLocation($subScreenshotRegion,
            CoordinatesType::SCREENSHOT_AS_IS, CoordinatesType::CONTEXT_RELATIVE);

        return new EyesImagesScreenshot($subScreenshotImage,
            $relativeSubScreenshotRegion->getLocation());
    }

    protected function convertLocation(Location $location, CoordinatesType $from, CoordinatesType $to)
    {

        ArgumentGuard::notNull($location, "location");
        ArgumentGuard::notNull($from, "from");
        ArgumentGuard::notNull($to, "to");

        $result = new Location($location);

        if ($from == $to) {
            return $result;
        }

        switch ($from) {
            case SCREENSHOT_AS_IS:
                if ($to == CoordinatesType::CONTEXT_RELATIVE) {
                    $result->offset($this->bounds->getLeft(), $this->bounds->getTop());
                } else {
                    throw new CoordinatesTypeConversionException($from, $to);
                }
                break;

            case CONTEXT_RELATIVE:
                if ($to == CoordinatesType::SCREENSHOT_AS_IS) {
                    $result->offset(-$this->bounds->getLeft(), -$this->bounds->getTop());
                } else {
                    throw new CoordinatesTypeConversionException($from, $to);
                }
                break;

            default:
                throw new CoordinatesTypeConversionException($from, $to);
        }
        return $result;
    }

    public function getLocationInScreenshot(Location $location,
                                            CoordinatesType $coordinatesType)
    {
        ArgumentGuard::notNull($location, "location");
        ArgumentGuard::notNull($coordinatesType, "coordinatesType");

        $location = convertLocation($location, $coordinatesType,
            CoordinatesType::CONTEXT_RELATIVE);

        if (!bounds . contains(location)) {
            throw new OutOfBoundsException(sprintf(
                "Location %s ('%s') is not visible in screenshot!", location,
                coordinatesType));
        }

        return $this->convertLocation($location, CoordinatesType::CONTEXT_RELATIVE, CoordinatesType::SCREENSHOT_AS_IS);
    }

    protected function getIntersectedRegion(Region $region,
                                            CoordinatesType $originalCoordinatesType,
                                            CoordinatesType $resultCoordinatesType)
    {

        ArgumentGuard::notNull($region, "region");
        ArgumentGuard::notNull($originalCoordinatesType, "coordinatesType");

        if ($region->isEmpty()) {
            return new Region($region);
        }

        $intersectedRegion = $this->convertRegionLocation($region, $originalCoordinatesType, CoordinatesType::CONTEXT_RELATIVE);

        $intersectedRegion->intersect($this->bounds);

        // If the intersection is empty we don't want to convert the
        // coordinates.
        if ($region->isEmpty()) {
            return $region;
        }

        // The returned result should be in the coordinatesType given as
        // parameter.
        $intersectedRegion->setLocation(convertLocation($intersectedRegion->getLocation(),
            CoordinatesType::CONTEXT_RELATIVE, $resultCoordinatesType));

        return $intersectedRegion;
    }
}
