<?php

/**
 * Encapsulates getter/setter behavior. (e.g., set only once etc.).
 */
interface PropertyHandler
{
    /**
     *
     * @param obj The object to set.
     * @return {@code true} if the object was set, {@code false} otherwise.
     */
    public function set($obj);

    /**
     *
     * @return The object that was set. (Note that object might also be set
     * in the constructor of an implementation class).
     */
    public function get();
}
