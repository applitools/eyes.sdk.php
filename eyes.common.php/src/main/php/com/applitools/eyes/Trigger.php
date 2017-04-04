<?php
/*
 * Applitools SDK for Selenium integration.
 */

namespace Applitools;

/**
 * A base class for triggers.
 */
abstract class Trigger {

    private $TriggerType; //FIXME
    const Unknown = "Unknown";
    const Mouse = "Mouse";
    const Text = "Text";
    const Keyboard = "Keyboard"; 

    public abstract function getTriggerType();
}

?>