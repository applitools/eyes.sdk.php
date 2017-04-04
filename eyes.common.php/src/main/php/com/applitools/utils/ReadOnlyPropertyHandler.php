<?php
require_once "PropertyHandler.php";

/**
 * A property handler for read-only properties (i.e., set always fails).
 */
class ReadOnlyPropertyHandler implements PropertyHandler
{
    private $logger; //Logger
    private $obj;

    public function ReadOnlyPropertyHandler(Logger $logger, $obj)
    {
        $this->logger = $logger;
        $this->obj = $obj;
    }

    /**
     * This method does nothing. It simply returns false.
     * @param $obj object The object to set.
     * @return bool Always returns false.
     */
    public function set($obj)
    {
        $reflect = new ReflectionClass($obj);
        $this->logger->log(sptintf("Ignored. (%s)", $reflect->getShortName()));
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function get()
    {
        return $this->obj;
    }
}
