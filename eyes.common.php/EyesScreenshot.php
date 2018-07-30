<?php

namespace Applitools;

/**
* Base class for handling screenshots.
*/
abstract class EyesScreenshot {
    protected $image;

    public function __construct($image) {
        ArgumentGuard::notNull($image, "image");
        $this->image = $image;
    }

    /**
    * @return resource The screenshot image.
    */
    public function getImage() {
        return $this->image;
    }

    /**
    * Returns a part of the screenshot based on the given region.
    *
    * @param Region $region          The region for which we should get the sub screenshot.
    * @param bool $throwIfClipped  Throw an EyesException if the region is not fully contained in the screenshot.
    * @return EyesScreenshot A screenshot instance containing the given region.
    */
    public abstract function getSubScreenshot(Region $region, $throwIfClipped);

    /**
    * Converts a location's coordinates with the {@code from} coordinates type
    * to the {@code to} coordinates type.
    *
    * @param Location $location The location which coordinates needs to be converted.
    * @param string $from The current coordinates type for {@code location}.
    * @param string $to The target coordinates type for {@code location}.
    * @return Location A new location which is the transformation of {@code location} to the {@code to} coordinates type.
    */
    protected abstract function convertLocation(Location $location, $from, $to);

    /**
    * Calculates the location in the screenshot of the location given as parameter.
    *
    * @param Location $location The location as coordinates inside the current frame.
    * @param string $coordinatesType The coordinates type of {@code location}.
    * @return Location The corresponding location inside the screenshot,
    * in screenshot as-is coordinates type.
    * @throws \Applitools\Exceptions\OutOfBoundsException If the location is
    * not inside the frame's region in the screenshot.
    */
    public abstract function getLocationInScreenshot(Location $location, $coordinatesType);


    /**
     * Get the intersection of the given region with the screenshot.
     *
     * @param Region $region The region to intersect.
     * @param string $originalCoordinatesType The coordinates type of {@code region}.
     * @param $resultCoordinatesType
     * @return Region The intersected region, in  coordinates {@code $resultCoordinatesType}.
     */
    public abstract function getIntersectedRegion(Region $region,    $originalCoordinatesType,    $resultCoordinatesType);

    /*
    /**
    * Get the intersection of the given region with the screenshot.
    *
    * @param Region $region The region to intersect.
    * @param CoordinatesType $coordinatesType The coordinates type of {@code region}.
    * @return Region The intersected region, in {@code coordinatesType} coordinates.
    */
    /*protected function getIntersectedRegion(Region $region, CoordinatesType $coordinatesType) {
        return getIntersectedRegion($region, $coordinatesType, $coordinatesType);
    }*/

    /**
    * Converts a region's location coordinates with the {@code from}
    * coordinates type to the {@code to} coordinates type.
    *
    * @param Region $region The region which location's coordinates needs to be converted.
    * @param string $from The current coordinates type for {@code region}.
    * @param string $to The target coordinates type for {@code region}.
    * @return Region A new region which is the transformation of {@code region} to the {@code to} coordinates type.
    */
    public function convertRegionLocation(Region $region, /*CoordinatesType */$from, /*CoordinatesType */$to) {
        ArgumentGuard::notNull($region, "region");

        if ($region->isEmpty()) {
            return clone $region;
        }

        ArgumentGuard::notNull($from, "from");
        ArgumentGuard::notNull($to, "to");

        $updatedLocation = $this->convertLocation($region->getLocation(), $from, $to);
        return Region::CreateFromLocationAndSize($updatedLocation, $region->getSize());
    }
}