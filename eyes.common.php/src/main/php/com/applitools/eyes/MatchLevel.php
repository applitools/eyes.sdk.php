<?php

namespace Applitools;

/**
 * The extent in which two images match (or are expected to match).
 */
class MatchLevel
{
    /**
     * Images do not necessarily match.
     */
    const NONE = "NONE";

    /**
     * Images have the same layout.
     */
    const LAYOUT = "LAYOUT";

    /**
     * Images have the same layout.
     */
    const LAYOUT2 = "LAYOUT2";

    /**
     * Images have the same outline.
     */
    const CONTENT = "CONTENT";

    /**
     * Images are nearly identical.
     */
    const STRICT = "STRICT";

    /**
     * Images are identical.
     */
    const EXACT = "EXACT";
}
