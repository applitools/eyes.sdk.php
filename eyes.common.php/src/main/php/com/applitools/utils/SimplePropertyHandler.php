<?php
require_once "PropertyHandler.php";

/**
 * A simple implementation of {@link PropertyHandler}. Allows get/set.
 */
class SimplePropertyHandler implements PropertyHandler
{
    private $obj;

    /**
     * {@inheritDoc}
     */
    public function set($obj)
    {
        $this->obj = $obj;
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function get()
    {
        return $obj;
    }
}
