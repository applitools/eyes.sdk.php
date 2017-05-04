<?

namespace Applitools;

/**
 * AppOutput with timing information.
 */
class TimedAppOutput extends AppOutput {
    private $elapsed;
    private $isPrimary;

    /**
     * @param string $title        The title of the window.
     * @param string $screenshot64 Base64 encoding of the screenshot's bytes.
     * @param int $elapsed         The elapsed time from the first captured window until this window was captured.
     * @param bool $isPrimary      Whether this window is considered a "primary" (e.g., if the user expected that up to this
     *                             window there should already have been a match in a timing test).
     */
    public function __construct($title, $screenshot64, $elapsed, $isPrimary) {
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

?>