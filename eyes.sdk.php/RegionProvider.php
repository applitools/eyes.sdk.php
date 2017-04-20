<?php

namespace Applitools;

/**
 * Encapsulates a getRegion "callback" and how the region's coordinates
 * should be used.
 */
class RegionProvider
{
    private $coordinatesType = null;
    protected $region;

    public function __construct(Region $region = null)
    {
        if (empty($region)) {
            $this->region = Region::getEmpty();
        } else {
            $this->region = $region;
        }
    }

    /**
     *
     * @return Region A region with "as is" viewport coordinates.
     */
    public function getRegion()
    {
        return $this->region;
    }

    public function setRegion($region)
    {
        $this->region = $region;
    }

    /**
     *
     * @return CoordinatesType The type of coordinates on which the region is based.
     */
    public function getCoordinatesType()
    {
        return $this->coordinatesType;
    }

    public function setCoordinatesType($coordinatesType)
    {
        $this->coordinatesType = $coordinatesType;
    }

}
