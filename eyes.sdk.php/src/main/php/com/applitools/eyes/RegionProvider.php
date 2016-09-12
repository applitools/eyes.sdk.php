<?php

/**
 * Encapsulates a getRegion "callback" and how the region's coordinates
 * should be used.
 */
class RegionProvider
{
    protected $region; //FIXME absent in java
    public function __construct()
    {//FIXME absent in java
        $this->region = Region::getEmpty();
    }

    /**
     *
     * @return A region with "as is" viewport coordinates.
     */
    public function getRegion()
    {//FIXME
        return $this->region;
    }

    /**
     *
     * @return The type of coordinates on which the region is based.
     */
    public function getCoordinatesType()
    {
        return null;
    }

}
