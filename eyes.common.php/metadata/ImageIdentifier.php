<?php
/**
 * Applitools software
 */

namespace Applitools;


class ImageIdentifier
{
    /** @var string */
    private $id;

    /** @var RectangleSize */
    private $size;

    function __construct($data = null) {
        if(is_array($data)){
            foreach($data as $key => $val) {
                if(property_exists(__CLASS__,$key)) {
                    $this->$key = $val;
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return RectangleSize
     */
    public function getSize()
    {
        return $this->size;
    }

}