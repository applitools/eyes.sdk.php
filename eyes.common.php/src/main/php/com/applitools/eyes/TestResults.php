<?php

namespace Applitools;

/**
 * Eyes test results.
 */
class TestResults {
    private $steps;
    private $matches;
    private $mismatches;
    private $missing;
    private $exactMatches;
    private $strictMatches;
    private $contentMatches;
    private $layoutMatches;
    private $noneMatches;
    private $url;
    private $isNew;

    function __construct($data = null) {
        if(is_array($data)){
            foreach($data as $key => $val) {
                if(property_exists(__CLASS__,$key)) {
                    $this->$key = $val;
                }
            }
        }

    }

    /**
     * @return The total number of test steps.
     */
    public function getSteps() {
        return $this->steps;
    }

    /**
     * @return The total number of test steps that matched the baseline.
     */
    public function getMatches() {
        return $this->matches;
    }

    /**
     * @return The total number of test steps that did not match the baseline.
     */
    public function getMismatches() {
        return $this->mismatches;
    }

    /**
     * @return The total number of baseline test steps that were missing in
     * the test.
     */
    public function getMissing() {
        return $this->missing;
    }

    /**
     * @return The total number of test steps that exactly matched the baseline.
     */
    public function getExactMatches() {
        return $this->exactMatches;
    }

    /**
     * @return The total number of test steps that strictly matched the
     * baseline.
     */
    public function getStrictMatches() {
        return $this->strictMatches;
    }

    /**
     * @return The total number of test steps that matched the baseline by
     * content.
     */
    public function getContentMatches() {
        return $this->contentMatches;
    }

    /**
     * @return The total number of test steps that matched the baseline by
     * layout.
     */
    public function getLayoutMatches() {
        return $this->layoutMatches;
    }

    /**
     * @return The total number of test steps that matched the baseline without
     * performing any comparison.
     */
    public function getNoneMatches() {
        return $this->noneMatches;
    }

    /**
     * @return The URL where test results can be viewed.
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * @return Whether or not this is a new test.
     */
    public function isNew() {
        return $this->isNew;
    }

    /**
     * @return Whether or not this test passed.
     */
    public function isPassed() {
        return (!$this->isNew() && $this->getMismatches() == 0 && $this->getMissing() == 0);
    }

    /**
     * @param steps The number of visual checkpoints in the test.
     */
    public function setSteps($steps) {
        ArgumentGuard::greaterThanOrEqualToZero($steps, "steps");
        $this->steps = $steps;
    }

    /**
     * @param matches The number of visual matches in the test.
     */
    public function setMatches($matches) {
        ArgumentGuard::greaterThanOrEqualToZero($matches, "matches");
        $this->matches = $matches;
    }

    /**
     * @param mismatches The number of mismatches in the test.
     */
    public function setMismatches($mismatches) {
        ArgumentGuard::greaterThanOrEqualToZero($mismatches, "mismatches");
        $this->mismatches = $mismatches;
    }

    /**
     * @param missing The number of visual checkpoints that were available in
     *                the baseline but were not found in the current test.
     */
    public function setMissing($missing) {
        ArgumentGuard::greaterThanOrEqualToZero($missing, "missing");
        $this->missing = $missing;
    }

    /**
     * @param exactMatches The number of matches performed with match
     *                     level set to
     *                     {@link com.applitools.eyes.MatchLevel#EXACT}
     */
    public function setExactMatches($exactMatches) {
        ArgumentGuard::greaterThanOrEqualToZero($exactMatches, "exactMatches");
        $this->exactMatches = $exactMatches;
    }

    /**
     * @param strictMatches The number of matches performed with match
     *                     level set to
     *                     {@link com.applitools.eyes.MatchLevel#STRICT}
     */
    public function setStrictMatches($strictMatches) {
        ArgumentGuard::greaterThanOrEqualToZero($strictMatches, "strictMatches");
        $this->strictMatches = $strictMatches;
    }

    /**
     * @param contentMatches The number of matches performed with match
     *                     level set to
     *                     {@link com.applitools.eyes.MatchLevel#CONTENT}
     */
    public function setContentMatches($contentMatches) {
        ArgumentGuard::greaterThanOrEqualToZero($contentMatches, "contentMatches");
        $this->contentMatches = $contentMatches;
    }

    /**
     * @param layoutMatches The number of matches performed with match
     *                     level set to
     *                     {@link com.applitools.eyes.MatchLevel#LAYOUT}
     */
    public function setLayoutMatches($layoutMatches) {
        ArgumentGuard::greaterThanOrEqualToZero($layoutMatches, "layoutMatches");
        $this->layoutMatches = $layoutMatches;
    }

    /**
     * @param noneMatches The number of matches performed with match
     *                     level set to
     *                     {@link com.applitools.eyes.MatchLevel#NONE}
     */
    public function setNoneMatches($noneMatches) {
        ArgumentGuard::greaterThanOrEqualToZero($noneMatches, "noneMatches");
        $this->noneMatches = $noneMatches;
    }

    /**
     * @param url The URL of the test results.
     */
    public function setUrl($url) {
        $this->url = $url;
    }

    /**
     * @param isNew Whether or not this test has an existing baseline.
     */
    public function setNew($isNew) {
        $this->isNew = $isNew;
    }

    public function toString() {
        $isNewTestStr = $this->isNew ? "New test" : "Existing test";
        return $isNewTestStr . " [ steps: " . $this->getSteps()
                . ", matches: " . $this->getMatches()
                . ", mismatches:" . $this->getMismatches() . ", missing: "
                . $this->getMissing() . "] , URL: " . $this->getUrl();
    }
}