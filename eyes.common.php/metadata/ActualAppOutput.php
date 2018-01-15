<?php
/**
 * Applitools software
 */

namespace Applitools;


class ActualAppOutput
{

    /** @var string */
    private $tag;

    /** @var ImageIdentifier */
    private $image;

    /** @var ImageIdentifier */
    private $thumbprint;

    /** @var \DateTime */
    private $occurredAt;

    /** @var ImgMatchSettings */
    private $imageMatchSettings;

    /** @var boolean */
    private $ignoreExpectedOutputSettings;

    /** @var boolean */
    private $isMatching;

    /** @var boolean */
    private $areImagesMatching;

    /** @var array */
    private $userInputs;

    /** @var string */
    private $windowTitle;

    /** @var boolean */
    private $isPrimary;

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
     * @return ImgMatchSettings
     */
    public function getImageMatchSettings()
    {
        return $this->imageMatchSettings;
    }

    /**
     * @return bool
     */
    public function isIgnoreExpectedOutputSettings()
    {
        return $this->ignoreExpectedOutputSettings;
    }

    /**
     * @return bool
     */
    public function isIsMatching()
    {
        return $this->isMatching;
    }

    /**
     * @return bool
     */
    public function isAreImagesMatching()
    {
        return $this->areImagesMatching;
    }

    /**
     * @return array
     */
    public function getUserInputs()
    {
        return $this->userInputs;
    }

    /**
     * @return string
     */
    public function getWindowTitle()
    {
        return $this->windowTitle;
    }

    /**
     * @return bool
     */
    public function isIsPrimary()
    {
        return $this->isPrimary;
    }

}