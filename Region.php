<?php
require "ArgumentGuard.php";

/**
 * Represents a region.
 */
class Region {
    private $left;
    private $top;
    private $width;
    private $height;

    const EMPTY = new Region(0, 0, 0, 0);

    protected function makeEmpty() {
        $this->left = self::EMPTY->getLeft();
        $this->top = self::EMPTY->getTop();
        $this->width = self::EMPTY->getWidth();
        $this->height = self::EMPTY->getHeight();
    }

    public function __construct($left, $top, $width, $height) {
        ArgumentGuard::greaterThanOrEqualToZero($width, "width");
        ArgumentGuard::greaterThanOrEqualToZero($height, "height");

        $this->left = $left;
        $this->top = $top;
        $this->width = $width;
        $this->height = $height;
    }

    /**
     *
     * @return true if the region is empty, false otherwise.
     */
    public function isEmpty() {
        return $this.getLeft() == self::EMPTY->getLeft()
        && $this->getTop() == self::EMPTY->getTop()
        && $this->getWidth() == self::EMPTY->getWidth()
        && $this->getHeight() == self::EMPTY->getHeight();
    }

    public function equals(Object $obj) {
        if ($obj == null) {
            return false;
        }

        if (!($obj instanceof Region)) {
            return  false;
        }
        $other = (Region) $obj; // clone????

        return ($this->getLeft() == $other->getLeft())
        && ($this->getTop() == $other->getTop())
        && ($this->getWidth() == $other->getWidth())
        && ($this->getHeight() == $other->getHeight());
    }

    public function hashCode() {
        return ($this->left . $this->top . $this->width + $this->height);
    }

    public function __construct(Location $location, RectangleSize $size) {
        ArgumentGuard::notNull($location, "location");
        ArgumentGuard::notNull($size, "size");

        $this->left = $location->getX();
        $this->top = $location->getY();
        $this->width = $size->getWidth();
        $this->height = $size->getHeight();
    }

    public function __construct(Region $other) {
        ArgumentGuard::notNull($other, "other");

        $this->left = $other->getLeft();
        $this->top = $other->getTop();
        $this->width = $other->getWidth();
        $this->height = $other->getHeight();
    }

    /**
     *
     * @return The (top,left) position of the current region.
     */
    public function getLocation() {
        return new Location($this->left, $this->top);
    }

    /**
     * Offsets the region's location (in place).
     *
     * @param dx The X axis offset.
     * @param dy The Y axis offset.
     */
    public function offset($dx, $dy) {
        $this->left += $dx;
        $this->top += $dy;
    }

    /**
     *
     * @return The (top,left) position of the current region.
     */
    public function getSize() {
        return new RectangleSize($this->width, $this->height);
    }

    /**
     * Set the (top,left) position of the current region
     * @param location The (top,left) position to set.
     */
    public function setLocation(Location $location) {
        ArgumentGuard::notNull(location, "location");
        $this->left = $location->getX();
        $this->top = $location->getY();
    }

    /**
     *
     * @param containerRegion The region to divide into sub-regions.
     * @param subRegionSize The maximum size of each sub-region.
     * @return The sub-regions composing the current region. If subRegionSize
     * is equal or greater than the current region,  only a single region is
     * returned.
     */
    private static function getSubRegionsWithFixedSize(
                            Region $containerRegion, RectangleSize $subRegionSize) {
        ArgumentGuard::notNull($containerRegion, "containerRegion");
        ArgumentGuard::notNull($subRegionSize, "subRegionSize");

        $subRegions = array();

        $subRegionWidth = $subRegionSize->getWidth();
        $subRegionHeight = $subRegionSize->getHeight();

        ArgumentGuard::greaterThanZero($subRegionWidth, "subRegionSize width");
        ArgumentGuard::greaterThanZero($subRegionHeight, "subRegionSize height");

        // Normalizing.
        if ($subRegionWidth > $containerRegion->width) {
            $subRegionWidth = containerRegion->width;
        }
        if ($subRegionHeight > $containerRegion->height) {
            $subRegionHeight = $containerRegion->height;
        }

        // If the requested size is greater or equal to the entire region size,
        // we return a copy of the region.
        if ($subRegionWidth == $containerRegion->width &&
            $subRegionHeight == $containerRegion->height) {
            $subRegions->add(new Region($containerRegion));
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
            while ($currentLeft <= $this->right) {
                if ($currentLeft + $subRegionWidth > $this->right) {
                    $currentLeft = ($this->right - $subRegionWidth) + 1;
                }

                $subRegions.add(new Region($currentLeft, $currentTop,
                    $subRegionWidth, $subRegionHeight));

                $currentLeft += $subRegionWidth;
            }
            $currentTop += $subRegionHeight;
        }
        return $subRegions;
    }

    /**
     * @param containerRegion The region to divide into sub-regions.
     * @param maxSubRegionSize The maximum size of each sub-region (some
     *                         regions might be smaller).
     * @return The sub-regions composing the current region. If
     * maxSubRegionSize is equal or greater than the current region,
     * only a single region is returned.
     */
    private static /*Iterable<Region>*/ getSubRegionsWithVaryingSize(Region containerRegion, RectangleSize maxSubRegionSize) {
        ArgumentGuard.notNull(containerRegion, "containerRegion");
        ArgumentGuard.notNull(maxSubRegionSize, "maxSubRegionSize");
        ArgumentGuard.greaterThanZero(maxSubRegionSize.getWidth(),
            "maxSubRegionSize.getWidth()");
        ArgumentGuard.greaterThanZero(maxSubRegionSize.getHeight(),
            "maxSubRegionSize.getHeight()");

        List<Region> subRegions = new LinkedList<Region>();

        int currentTop = containerRegion.top;
        int bottom = containerRegion.top + containerRegion.height;
        int right = containerRegion.left + containerRegion.width;

        while (currentTop < bottom) {

            int currentBottom = currentTop + maxSubRegionSize.getHeight();
            if (currentBottom > bottom) { currentBottom = bottom; }

            int currentLeft = containerRegion.left;
            while (currentLeft < right) {
                int currentRight = currentLeft + maxSubRegionSize.getWidth();
                if (currentRight > right) { currentRight = right; }

                int currentHeight = currentBottom - currentTop;
                int currentWidth = currentRight - currentLeft;

                subRegions.add(new Region(currentLeft, currentTop,
                    currentWidth, currentHeight));

                currentLeft += maxSubRegionSize.getWidth();
            }
            currentTop += maxSubRegionSize.getHeight();
        }
        return subRegions;
    }

    /**
     * Returns a list of sub-regions which compose the current region.
     * @param subRegionSize The default sub-region size to use.
     * @param isFixedSize If {@code false}, then sub-regions might have a
     *                      size which is smaller then {@code subRegionSize}
     *                      (thus there will be no overlap of regions).
     *                      Otherwise, all sub-regions will have the same
     *                      size, but sub-regions might overlap.
     * @return The sub-regions composing the current region. If {@code
     * subRegionSize} is equal or greater than the current region,
     * only a single region is returned.
     */
    public Iterable<Region> getSubRegions(RectangleSize subRegionSize,
                                          boolean isFixedSize) {
    if (isFixedSize) {
        return getSubRegionsWithFixedSize(this, subRegionSize);
    }

    return getSubRegionsWithVaryingSize(this, subRegionSize);
}

    /**
     * See {@link #getSubRegions(RectangleSize, boolean)}.
     * {@code isFixedSize} defaults to {@code false}.
     */
    public Iterable<Region> getSubRegions(RectangleSize subRegionSize) {
    return getSubRegions(subRegionSize, false);
}

    /**
     * Check if a region is contained within the current region.
     * @param other The region to check if it is contained within the current
     *              region.
     * @return True if {@code other} is contained within the current region,
     *          false otherwise.
     */
    @SuppressWarnings("UnusedDeclaration")
    public boolean contains(Region other) {
    int right = left + width;
        int otherRight = other.getLeft() + other.getWidth();

        int bottom = top + height;
        int otherBottom = other.getTop() + other.getHeight();

        return top <= other.getTop() && left <= other.getLeft()
        && bottom >= otherBottom && right >= otherRight;
    }

    /**
     * Check if a specified location is contained within this region.
     * <p>
     * @param location The location to test.
     * @return True if the location is contained within this region,
     *          false otherwise.
     */
    public boolean contains(Location location) {
    return location.getX() >= left
    && location.getX() <= (left + width)
    && location.getY() >= top
    && location.getY() <= (top + height);
}

    /**
     * Check if a region is intersected with the current region.
     * @param other The region to check intersection with.
     * @return True if the regions are intersected, false otherwise.
     */
    public boolean isIntersected(Region other) {
    int right = left + width;
        int bottom = top + height;

        int otherLeft = other.getLeft();
        int otherTop = other.getTop();
        int otherRight = otherLeft + other.getWidth();
        int otherBottom = otherTop + other.getHeight();

        return (((left <= otherLeft && otherLeft <= right)
                ||  (otherLeft <= left && left <= otherRight))
            && ((top <= otherTop && otherTop <= bottom)
                ||  (otherTop <= top && top <= otherBottom)));
    }

    /**
     * Replaces this region with the intersection of itself and
     * {@code other}
     * @param other The region with which to intersect.
     */
    public void intersect(Region other) {

    // If there's no intersection set this as the Empty region.
    if (!isIntersected(other)) {
        makeEmpty();
        return;
    }

    // The regions intersect. So let's first find the left & top values
    int otherLeft = other.getLeft();
        int otherTop = other.getTop();

        int intersectionLeft = (left >= otherLeft) ? left : otherLeft;
        int intersectionTop = (top >= otherTop) ? top : otherTop;

        // Now the width and height of the intersect
        int right = left + width;
        int otherRight = otherLeft + other.getWidth();
        int intersectionRight = (right <= otherRight) ? right : otherRight;
        int intersectionWidth = intersectionRight - intersectionLeft;

        int bottom = top + height;
        int otherBottom = otherTop + other.getHeight();
        int intersectionBottom = (bottom <= otherBottom) ? bottom : otherBottom;
        int intersectionHeight = intersectionBottom - intersectionTop;

        left = intersectionLeft;
        top = intersectionTop;
        width = intersectionWidth;
        height = intersectionHeight;

    }


    public int getLeft() {
        return left;
    }

    public int getTop() {
        return top;
    }

    public int getWidth() {
        return width;
    }

    public int getHeight() {
        return height;
    }

    public Location getMiddleOffset() {
int middleX = width / 2;
        int middleY = height / 2;

        return new Location(middleX, middleY);
    }

    @Override
    public String toString() {
        return "(" + left + ", " + top + ") " + width + "x" + height;
    }
}
