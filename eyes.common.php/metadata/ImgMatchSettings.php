<?php
/**
 * Applitools software
 */

namespace Applitools;


class ImgMatchSettings
{
    /** @var string */
    private $matchLevel;

    /** @var FloatingMatchSettings[] */
    private $floating;

    /** @var Region[] */
    private $ignore;

    /** @var Region[] */
    private $strict;

    /** @var Region[] */
    private $content;

    /** @var Region[] */
    private $layout;

    /** @var integer */
    private $splitTopHeight;

    /** @var integer */
    private $splitBottomHeight;

    /** @var boolean */
    private $ignoreCaret;

    /** @var integer */
    private $scale;

    /** @var integer */
    private $remainder;

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
    public function getMatchLevel()
    {
        return $this->matchLevel;
    }

    /**
     * @return FloatingMatchSettings[]
     */
    public function getFloating()
    {
        return $this->floating;
    }

    /**
     * @return Region[]
     */
    public function getIgnore()
    {
        return $this->ignore;
    }

    /**
     * @return Region[]
     */
    public function getStrict()
    {
        return $this->strict;
    }

    /**
     * @return Region[]
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return Region[]
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @return int
     */
    public function getSplitTopHeight()
    {
        return $this->splitTopHeight;
    }

    /**
     * @return int
     */
    public function getSplitBottomHeight()
    {
        return $this->splitBottomHeight;
    }

    /**
     * @return bool
     */
    public function isIgnoreCaret()
    {
        return $this->ignoreCaret;
    }

    /**
     * @return int
     */
    public function getScale()
    {
        return $this->scale;
    }

    /**
     * @return int
     */
    public function getRemainder()
    {
        return $this->remainder;
    }


}