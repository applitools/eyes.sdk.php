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
     * The ratio of differing pixels above which images are considered
     * mismatching.
     */
    private $matchThreshold;

    /**
     *
     * @return The minimal non-ignorable pixel intensity difference.
     */
    public function getMinDiffIntensity() {
        return $this->minDiffIntensity;
    }

    /**
     *
     * @param minDiffIntensity The minimal non-ignorable pixel intensity
     *                         difference.
     */
    public function setMinDiffIntensity($minDiffIntensity) {
        $this->minDiffIntensity = $minDiffIntensity;
    }

    /**
     *
     * @return The minimal non-ignorable diff region width.
     */
    public function getMinDiffWidth() {
        return $this->minDiffWidth;
    }

    /**
     *
     * @param minDiffWidth The minimal non-ignorable diff region width.
     */
    public function setMinDiffWidth($minDiffWidth) {
        $this->minDiffWidth = $minDiffWidth;
    }

    /**
     *
     * @return The minimal non-ignorable diff region height.
     */
    public function getMinDiffHeight() {
        return $this->minDiffHeight;
    }

    /**
     *
     * @param minDiffHeight The minimal non-ignorable diff region height.
     */
    public function setMinDiffHeight($minDiffHeight) {
        $this->minDiffHeight = $minDiffHeight;
    }

    /**
     *
     * @return The ratio of differing pixels above which images are
     * considered mismatching.
     */
    public function getMatchThreshold() {
        return $this->matchThreshold;
    }

    /**
     *
     * @param matchThreshold The ratio of differing pixels above which images
     *                       are considered mismatching.
     */
    public function setMatchThreshold($matchThreshold) {
        $this->matchThreshold = $matchThreshold;
    }

    public function toString() {
        return sprintf("[min diff intensity: %d, min diff width %d, " .
                "min diff height %d, match threshold: %f]", $this->minDiffIntensity,
                $this->minDiffWidth, $this->minDiffHeight, $this->matchThreshold);
    }
}

?>