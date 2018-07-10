<?php

namespace Applitools;

/**
 * A batch of tests.
 */
class BatchInfo
{

    const BATCH_TIMEZONE = "UTC";
    private $id;
    private $name;
    private $startedAt;

    /**
     * Creates a new BatchInfo instance.
     *
     * @param string $name Name of batch or {@code null} if anonymous.
     * @param mixed $startedAt Batch start time
     */
    public function __construct($name = null, $startedAt = null)
    {
        //ArgumentGuard::notNull($startedAt, "startedAt"); //FIXME
        $this->id = isset($_SERVER["APPLITOOLS_BATCH_ID"]) ? $_SERVER["APPLITOOLS_BATCH_ID"] : uniqid();
        $this->name = isset($name) ? $name : (isset($_SERVER["APPLITOOLS_BATCH_NAME"]) ? $_SERVER["APPLITOOLS_BATCH_NAME"] : null);
        $this->startedAt = (empty($startedAt) ? date("Y-m-d\TH:i:s\Z") : $startedAt);
    }

    /**
     * @return string The name of the batch or {@code null} if anonymous.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int The id of the current batch.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets a unique identifier for the batch. Sessions with batch info which
     * includes the same ID will be grouped together.
     * @param string $id The batch's ID
     */
    public function setId($id)
    {
        ArgumentGuard::notNullOrEmpty($id, "id");
        $this->id = $id;
    }

    /**
     * @return mixed The batch start date and time in ISO 8601 format.
     */
    public function getStartedAt()
    {
        return $this->startedAt;
    }

    public function __toString()
    {
        return "'$this->name' - $this->startedAt";
    }

    public function getAsArray()
    {
        return get_object_vars($this);
    }
}