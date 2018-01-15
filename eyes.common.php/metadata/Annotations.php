<?php
/**
 * Applitools software
 */

namespace Applitools;


class Annotations
{
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


}