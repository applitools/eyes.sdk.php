<?php
require "FixedScaleProvider.php";

/**
 * A scale provider which does nothing.
 */
class NullScaleProvider extends FixedScaleProvider
{

    public function __construct()
    {
        parent::__construct(1);
    }
}
