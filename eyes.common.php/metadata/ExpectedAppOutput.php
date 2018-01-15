<?php
/**
 * Applitools software
 */

namespace Applitools;


class ExpectedAppOutput
{
    /** @var string */
    private $tag;

    /** @var ImageIdentifier */
    private $image;

    /** @var ImageIdentifier */
    private $thumbprint;

    /** @var \DateTime */
    private $occurredAt;

    /** @var Annotations */
    private $annotations;

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
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @return ImageIdentifier
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return ImageIdentifier
     */
    public function getThumbprint()
    {
        return $this->thumbprint;
    }

    /**
     * @return \DateTime
     */
    public function getOccurredAt()
    {
        return $this->occurredAt;
    }

    /**
     * @return Annotations
     */
    public function getAnnotations()
    {
        return $this->annotations;
    }

}