<?php
/**
 * Applitools software
 */

namespace Applitools;


class BaselineEnv
{
    /** @var string */
    private $inferred;

    /** @var string */
    private $os;

    /** @var string */
    private $hostingApp;

    /** @var RectangleSize */
    private $displaySize;

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
    public function getInferred()
    {
        return $this->inferred;
    }

    /**
     * @return string
     */
    public function getOs()
    {
        return $this->os;
    }

    /**
     * @return string
     */
    public function getHostingApp()
    {
        return $this->hostingApp;
    }

    /**
     * @return RectangleSize
     */
    public function getDisplaySize()
    {
        return $this->displaySize;
    }


}