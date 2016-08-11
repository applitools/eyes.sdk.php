<?
/**
 * AppOutput with timing information.
 */
class TimedAppOutput extends AppOutput {
    private $elapsed;
    private $isPrimary;

    /**
     * @param title        The title of the window.
     * @param screenshot64 Base64 encoding of the screenshot's bytes (the
     *                     byte can be in either in compressed or
     * @param elapsed      The elapsed time from the first captured window
     *                     until this window was captured.
     * @param isPrimary    Whether this window is considered a "primary"
     *                     (e.g., if the user expected that up to this
     *                     window there should already have been a match in
     *                     a timing test).
     */
    public function __construct($title, $screenshot64, $elapsed,
                          $isPrimary) {
        parent::__construct($title, $screenshot64);
        $this->elapsed = $elapsed;
        $this->isPrimary = $isPrimary;
    }

    public function getElapsed() {
        return $this->elapsed;
    }

    public function getIsPrimary() {
        return $this->isPrimary;
    }
}
