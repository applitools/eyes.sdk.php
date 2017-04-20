<?php

namespace Applitools;

/**
 * Encapsulates getter/setter behavior. (e.g., set only once etc.).
 */
interface PropertyHandler
{
    /**
     *
     * @param mixed $obj The object to set.
     * @return bool {@code true} if the object was set, {@code false} otherwise.
     */
    public function set($obj);

    /**
     *
     * @return mixed The object that was set. (Note that object might also be set
     * in the constructor of an implementation class).
     */
    public function get();
}
