<?php

namespace Applitools;

/**
 * Represents a region.
 */
class Region
{
    private $left;
    private $top;
    private $width;
    private $height;

    /** @var string $coordinatesType */
    private $coordinatesType = CoordinatesType::SCREENSHOT_AS_IS;

    public static $empty;

    /** @var Logger */
    private static $logger;

    public static function initLogger($logger)
    {
        self::$logger = $logger;
    }

    public static function getEmpty()
    {
        if (self::$empty == null) {
            self::$empty = Region::CreateFromLTWH(0, 0, 0, 0);
        }
        return self::$empty;
    }

    protected function makeEmpty()
    {
        $this->left = self::$empty->left;
        $this->top = self::$empty->top;
        $this->width = self::$empty->width;
        $this->height = self::$empty->height;
        $this->coordinatesType = self::$empty->coordinatesType;
    }

    private function __construct()
    {
    }

    /**
     * @param float $left
     * @param float $top
     * @param float $width
     * @param float $height
     * @return Region
     */
    public static function CreateFromLTWH($left, $top, $width, $height)
    {
        ArgumentGuard::greaterThanOrEqualToZero($width, "width");
        ArgumentGuard::greaterThanOrEqualToZero($height, "height");
        $region = new Region();
        $region->left = $left;
        $region->top = $top;
        $region->width = $width;
        $region->height = $height;
        return $region;
    }

    public static function CreateFromLocationAndSize(Location $location, RectangleSize $size)
    {
        ArgumentGuard::notNull($location, "location");
        ArgumentGuard::notNull($size, "size");
        $region = new Region();
        $region->left = $location->getX();
        $region->top = $location->getY();
        $region->width = $size->getWidth();
        $region->height = $size->getHeight();
        return $region;
    }

    /**
     *
     * @return true if the region is empty, false otherwise.
     */
    public function isEmpty()
    {
        return $this->left == self::getEmpty()->getLeft()
            && $this->top == self::getEmpty()->getTop()
            && $this->width == self::getEmpty()->getWidth()
            && $this->height == self::getEmpty()->getHeight();
    }

    public function equals($obj)
    {
        if ($obj == null) {
            return false;
        }

        if (!($obj instanceof Region)) {
            return false;
        }
        $other = clone $obj; // clone????

        return ($this->getLeft() == $other->getLeft())
            && ($this->getTop() == $other->getTop())
            && ($this->getWidth() == $other->getWidth())
            && ($this->getHeight() == $other->getHeight())
            && ($this->getCoordinatesType() == $other->getCoordinatesType());
    }

    /**
     * @return string The type of coordinates on which the region is based.
     */
    public function getCoordinatesType()
    {
        return $this->coordinatesType;
    }

    /** @var string $coordinatesType */
    public function setCoordinatesType($coordinatesType)
    {
        $this->coordinatesType = $coordinatesType;
    }

    public function hashCode()
    {
        return ($this->left . $this->top . $this->width . $this->height);
    }

    /**
     *
     * @return Location The (top,left) position of the current region.
     */
    public function getLocation()
    {
        return new Location($this->left, $this->top);
    }

    /**
     * Offsets the region's location.
     *
     * @param int $dx The X axis offset.
     * @param int $dy The Y axis offset.
     * @return Region
     */
    public function offset($dx, $dy)
    {
        return Region::CreateFromLTWH($this->left + $dx, $this->top + $dy, $this->width, $this->height);
    }

    /**
     * Get a region which is a scaled version of the current region.
     * IMPORTANT: This also scales the LOCATION(!!) of the region (not just its size).
     *
     * @param double $scaleRatio The ratio by which to scale the region.
     * @return Region A new region which is a scaled version of the current region.
     */
    public function scale($scaleRatio)
    {
        return Region::CreateFromLocationAndSize($this->getLocation()->scale($scaleRatio), $this->getSize()->scale($scaleRatio));
    }

    /**
     *
     * @return RectangleSize The (top,left) position of the current region.
     */
    public function getSize()
    {
        return new RectangleSize($this->width, $this->height);
    }

    /**
     * Set the (top,left) position of the current region
     * @param Location $location The (top,left) position to set.
     */
    public function setLocation(Location $location)
    {
        ArgumentGuard::notNull($location, "location");
        $this->left = $location->getX();
        $this->top = $location->getY();
    }

    /**
     *
     * @param Region $containerRegion The region to divide into sub-regions.
     * @param RectangleSize $subRegionSize The maximum size of each sub-region.
     * @return Region[] The sub-regions composing the current region. If subRegionSize
     * is equal or greater than the current region,  only a single region is
     * returned.
     */
    private static function getSubRegionsWithFixedSize(Region $containerRegion, RectangleSize $subRegionSize)
    {
        ArgumentGuard::notNull($containerRegion, "containerRegion");
        ArgumentGuard::notNull($subRegionSize, "subRegionSize");

        $subRegions = array();

        $subRegionWidth = $subRegionSize->getWidth();
        $subRegionHeight = $subRegionSize->getHeight();

        ArgumentGuard::greaterThanZero($subRegionWidth, "subRegionSize width");
        ArgumentGuard::greaterThanZero($subRegionHeight, "subRegionSize height");

        // Normalizing.
        if ($subRegionWidth > $containerRegion->width) {
            $subRegionWidth = $containerRegion->width;
        }
        if ($subRegionHeight > $containerRegion->height) {
            $subRegionHeight = $containerRegion->height;
        }

        // If the requested size is greater or equal to the entire region size,
        // we return a copy of the region.
        if ($subRegionWidth == $containerRegion->width &&
            $subRegionHeight == $containerRegion->height
        ) {
            $subRegions[] = clone $containerRegion;
            return $subRegions;
        }

        $currentTop = $containerRegion->top;
        $bottom = $containerRegion->top + $containerRegion->height - 1;
        $right = $containerRegion->left + $containerRegion->width - 1;

        while ($currentTop <= $bottom) {

            if ($currentTop + $subRegionHeight > $bottom) {
                $currentTop = ($bottom - $subRegionHeight) + 1;
            }

            $currentLeft = $containerRegion->left;
            while ($currentLeft <= $right) {
                if ($currentLeft + $subRegionWidth > $right) {
                    $currentLeft = ($right - $subRegionWidth) + 1;
                }

                $subRegions[] = Region::CreateFromLTWH($currentLeft, $currentTop, $subRegionWidth, $subRegionHeight);

                $currentLeft += $subRegionWidth;
            }
            $currentTop += $subRegionHeight;
        }
        return $subRegions;
    }

    /**
     * @param Region $containerRegion The region to divide into sub-regions.
     * @param RectangleSize $maxSubRegionSize The maximum size of each sub-region (some regions might be smaller).
     * @return Region[] The sub-regions composing the current region. If
     * maxSubRegionSize is equal or greater than the current region,
     * only a single region is returned.
     */
    private static function getSubRegionsWithVaryingSize(Region $containerRegion, RectangleSize $maxSubRegionSize)
    {
        self::$logger->verbose("getSubRegionsWithVaryingSize {$containerRegion}, {$maxSubRegionSize}");

        ArgumentGuard::notNull($containerRegion, "containerRegion");
        ArgumentGuard::notNull($maxSubRegionSize, "maxSubRegionSize");
        ArgumentGuard::greaterThanZero($maxSubRegionSize->getWidth(), "maxSubRegionSize.getWidth()");
        ArgumentGuard::greaterThanZero($maxSubRegionSize->getHeight(), "maxSubRegionSize.getHeight()");

        $subRegions = array();

        $currentTop = $containerRegion->top;
        $bottom = $containerRegion->top + $containerRegion->height;
        $right = $containerRegion->left + $containerRegion->width;

        while ($currentTop < $bottom) {

            $currentBottom = $currentTop + $maxSubRegionSize->getHeight();
            if ($currentBottom > $bottom) {
                $currentBottom = $bottom;
            }
            $currentLeft = $containerRegion->left;
            while ($currentLeft < $right) {
                $currentRight = $currentLeft + $maxSubRegionSize->getWidth();
                if ($currentRight > $right) {
                    $currentRight = $right;
                }

                $currentHeight = $currentBottom - $currentTop;
                $currentWidth = $currentRight - $currentLeft;

                $subRegions[] = Region::CreateFromLTWH($currentLeft, $currentTop, $currentWidth, $currentHeight);

                $currentLeft += $maxSubRegionSize->getWidth();
            }
            $currentTop += $maxSubRegionSize->getHeight();
        }

        $count = count($subRegions);
        self::$logger->verbose("returning {$count} sub regions");

        return $subRegions;
    }

    /**
     * Returns a list of sub-regions which compose the current region.
     * @param RectangleSize $subRegionSize The default sub-region size to use.
     * @param bool $isFixedSize If {@code false}, then sub-regions might have a
     *                      size which is smaller then {@code subRegionSize}
     *                      (thus there will be no overlap of regions).
     *                      Otherwise, all sub-regions will have the same
     *                      size, but sub-regions might overlap.
     * @return Region[] The sub-regions composing the current region. If {@code
     * subRegionSize} is equal or greater than the current region,
     * only a single region is returned.
     */
    public function getSubRegions(RectangleSize $subRegionSize, $isFixedSize = false)
    {
        if ($isFixedSize) {
            return $this->getSubRegionsWithFixedSize($this, $subRegionSize);
        }

        return $this->getSubRegionsWithVaryingSize($this, $subRegionSize);
    }

    /**
     * See {@link #getSubRegions(RectangleSize, boolean)}.
     * {@code isFixedSize} defaults to {@code false}.
     */
    /* public function getSubRegions(RectangleSize $subRegionSize) {
         return $this->getSubRegions($subRegionSize, false);
     }*/

    /**
     * Check if a region is contained within the current region.
     * @param Region $other The region to check if it is contained within the current region.
     * @return bool True if {@code other} is contained within the current region, false otherwise.
     */

    public function containsRegion(Region $other)
    {
        $right = $this->left + $this->width;
        $otherRight = $other->getLeft() + $other->getWidth();

        $bottom = $this->top + $this->height;
        $otherBottom = $other->getTop() + $other->getHeight();

        return $this->top <= $other->getTop() && $this->left <= $other->getLeft()
            && $bottom >= $otherBottom && $right >= $otherRight;
    }

    /**
     * Check if a specified location is contained within this region.
     * <p>
     * @param Location $location The location to test.
     * @return bool True if the location is contained within this region,
     *          false otherwise.
     */
    public function containsLocation(Location $location)
    {               //FIXME
        return $location->getX() >= $this->left
            && $location->getX() <= ($this->left + $this->width)
            && $location->getY() >= $this->top
            && $location->getY() <= ($this->top + $this->height);
    }

    /**
     * Check if a region is intersected with the current region.
     * @param Region $other The region to check intersection with.
     * @return bool True if the regions are intersected, false otherwise.
     */
    public function isIntersected(Region $other)
    {
        $right = $this->left + $this->width;
        $bottom = $this->top + $this->height;

        $otherLeft = $other->getLeft();
        $otherTop = $other->getTop();
        $otherRight = $otherLeft + $other->getWidth();
        $otherBottom = $otherTop + $other->getHeight();

        return ((($this->left <= $otherLeft && $otherLeft <= $right)
                || ($otherLeft <= $this->left && $this->left <= $otherRight))
            && (($this->top <= $otherTop && $otherTop <= $bottom)
                || ($otherTop <= $this->top && $this->top <= $otherBottom)));
    }

    /**
     * Replaces this region with the intersection of itself and {@code other}
     * @param Region $other The region with which to intersect.
     */
    public function intersect(Region $other)
    {
        self::$logger->verbose("intersecting this region ($this) with $other ...");

        // If there's no intersection set this as the Empty region.
        if (!$this->isIntersected($other)) {
            $this->makeEmpty();
            return;
        }
        // The regions intersect. So let's first find the left & top values
        $otherLeft = $other->getLeft();
        $otherTop = $other->getTop();

        $intersectionLeft = ($this->left >= $otherLeft) ? $this->left : $otherLeft;
        $intersectionTop = ($this->top >= $otherTop) ? $this->top : $otherTop;

        // Now the width and height of the intersect
        $right = $this->left + $this->width;
        $otherRight = $otherLeft + $other->getWidth();
        $intersectionRight = ($right <= $otherRight) ? $right : $otherRight;
        $intersectionWidth = $intersectionRight - $intersectionLeft;
        $bottom = $this->top + $this->height;
        $otherBottom = $otherTop + $other->getHeight();
        $intersectionBottom = ($bottom <= $otherBottom) ? $bottom : $otherBottom;
        $intersectionHeight = $intersectionBottom - $intersectionTop;

        $this->left = $intersectionLeft;
        $this->top = $intersectionTop;
        $this->width = $intersectionWidth;
        $this->height = $intersectionHeight;
    }


    public function getLeft()
    {
        return $this->left;
    }

    public function getTop()
    {
        return $this->top;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getMiddleOffset()
    {
        $middleX = $this->width / 2;
        $middleY = $this->height / 2;

        return new Location($middleX, $middleY);
    }

    public function __toString()
    {
        return "({$this->left}, {$this->top }) {$this->width}x{$this->height}, {$this->coordinatesType}";
    }
}
