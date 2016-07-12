<?php

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
     * @param name      Name of batch or {@code null} if anonymous.
     * @param startedAt Batch start time
     */
    public function __construct($name, $startedAt = null)
    {
        //ArgumentGuard::notNull($startedAt, "startedAt");
        $this->id = rand();
        $this->name = $name;
        $this->startedAt = (empty($startedAt) ? date("Y-m-d\TH:i:s\Z") : $startedAt);
    }


    /**
     * @return The name of the batch or {@code null} if anonymous.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return The id of the current batch.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets a unique identifier for the batch. Sessions with batch info which
     * includes the same ID will be grouped together.
     * @param id The batch's ID
     */
    public function setId($id)
    {
        ArgumentGuard::notNullOrEmpty($id, "id");
        $this->id = $id;
    }

    /**
     * @return The batch start date and time in ISO 8601 format.
     */
    public function getStartedAt()
    {
        return $this->startedAt;
    }

    public function toString()
    {
        return "'" . $this->name . "' - " . $this->startedAt;
    }
}