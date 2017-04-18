<?

namespace Applitools;

/**
 * Encapsulates settings for the "Exact" match level.
 */
class ExactMatchSettings {

    /**
     * Minimal non-ignorable pixel intensity difference.
     */
    private $minDiffIntensity;

    /**
     * Minimal non-ignorable diff region width.
     */
    private $minDiffWidth;

    /**
     * Minimal non-ignorable diff region height.
     */
    private $minDiffHeight;

    /**
     * The ratio of differing pixels above which images are considered mismatching.
     */
    private $matchThreshold;

    /**
     *
     * @return float The minimal non-ignorable pixel intensity difference.
     */
    public function getMinDiffIntensity() {
        return $this->minDiffIntensity;
    }

    /**
     *
     * @param float $minDiffIntensity The minimal non-ignorable pixel intensity difference.
     */
    public function setMinDiffIntensity($minDiffIntensity) {
        $this->minDiffIntensity = $minDiffIntensity;
    }

    /**
     *
     * @return int The minimal non-ignorable diff region width.
     */
    public function getMinDiffWidth() {
        return $this->minDiffWidth;
    }

    /**
     *
     * @param int $minDiffWidth The minimal non-ignorable diff region width.
     */
    public function setMinDiffWidth($minDiffWidth) {
        $this->minDiffWidth = $minDiffWidth;
    }

    /**
     *
     * @return int The minimal non-ignorable diff region height.
     */
    public function getMinDiffHeight() {
        return $this->minDiffHeight;
    }

    /**
     *
     * @param int $minDiffHeight The minimal non-ignorable diff region height.
     */
    public function setMinDiffHeight($minDiffHeight) {
        $this->minDiffHeight = $minDiffHeight;
    }

    /**
     *
     * @return float The ratio of differing pixels above which images are
     * considered mismatching.
     */
    public function getMatchThreshold() {
        return $this->matchThreshold;
    }

    /**
     *
     * @param float $matchThreshold The ratio of differing pixels above which images are considered mismatching.
     */
    public function setMatchThreshold($matchThreshold) {
        $this->matchThreshold = $matchThreshold;
    }

    public function __toString() {
        return "[min diff intensity: $this->minDiffIntensity, min diff width: $this->minDiffWidth, " .
                "min diff height: $this->minDiffHeight, match threshold: $this->matchThreshold]";
    }
}

?>