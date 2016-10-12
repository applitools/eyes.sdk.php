<?php

/**
 * Encapsulates a getRegion "callback" and how the region's coordinates
 * should be used.
 */
class RegionProvider
{
    private $coordinatesType = null; //FIXME absent in java
    protected $region; //FIXME absent in java
    public function __construct(Region $region = null)
    {//FIXME absent in java
        if(empty($region)){
            $this->region = Region::getEmpty();
        }else{
            $this->region = $region;
        }

    }

    /**
     *
     * @return A region with "as is" viewport coordinates.
     */
    public function getRegion()
    {//FIXME
        return $this->region;
    }

    public function setRegion($region){
        $this->region = $region;
    }
    /**
     *
     * @return The type of coordinates on which the region is based.
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
