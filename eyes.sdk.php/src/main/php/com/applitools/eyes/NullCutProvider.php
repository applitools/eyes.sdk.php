<?php
namespace Applitools;
/**
 * A cut provider which does nothing.
 */
class NullCutProvider extends FixedCutProvider {

    public function __construct() {
        parent::__construct(0, 0, 0, 0);
    }
}

?>