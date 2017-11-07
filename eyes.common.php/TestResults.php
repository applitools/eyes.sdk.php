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
    private $status;

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
     * @return int The total number of test steps.
     */
    public function getSteps() {
        return $this->steps;
    }

    /**
     * @return int The total number of test steps that matched the baseline.
     */
    public function getMatches() {
        return $this->matches;
    }

    /**
     * @return int The total number of test steps that did not match the baseline.
     */
    public function getMismatches() {
        return $this->mismatches;
    }

    /**
     * @return int The total number of baseline test steps that were missing in the test.
     */
    public function getMissing() {
        return $this->missing;
    }

    /**
     * @return int The total number of test steps that exactly matched the baseline.
     */
    public function getExactMatches() {
        return $this->exactMatches;
    }

    /**
     * @return int The total number of test steps that strictly matched the baseline.
     */
    public function getStrictMatches() {
        return $this->strictMatches;
    }

    /**
     * @return int The total number of test steps that matched the baseline by content.
     */
    public function getContentMatches() {
        return $this->contentMatches;
    }

    /**
     * @return int The total number of test steps that matched the baseline by
     * layout.
     */
    public function getLayoutMatches() {
        return $this->layoutMatches;
    }

    /**
     * @return int The total number of test steps that matched the baseline without
     * performing any comparison.
     */
    public function getNoneMatches() {
        return $this->noneMatches;
    }

    /**
     * @return string The URL where test results can be viewed.
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * @return bool Whether or not this is a new test.
     */
    public function isNew() {
        return $this->isNew;
    }

    /**
     * @return bool Whether or not this test passed.
     */
    public function isPassed() {
        return ($this->status == TestResultsStatus::Passed);
    }

    /**
     * @return string The test result status.
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $steps The number of visual checkpoints in the test.
     */
    public function setSteps($steps) {
        ArgumentGuard::greaterThanOrEqualToZero($steps, "steps");
        $this->steps = $steps;
    }

    /**
     * @param int $matches The number of visual matches in the test.
     */
    public function setMatches($matches) {
        ArgumentGuard::greaterThanOrEqualToZero($matches, "matches");
        $this->matches = $matches;
    }

    /**
     * @param int $mismatches The number of mismatches in the test.
     */
    public function setMismatches($mismatches) {
        ArgumentGuard::greaterThanOrEqualToZero($mismatches, "mismatches");
        $this->mismatches = $mismatches;
    }

    /**
     * @param int $missing The number of visual checkpoints that were available in
     *                the baseline but were not found in the current test.
     */
    public function setMissing($missing) {
        ArgumentGuard::greaterThanOrEqualToZero($missing, "missing");
        $this->missing = $missing;
    }

    /**
     * @param int $exactMatches The number of matches performed with match level set to {@link MatchLevel::EXACT}
     */
    public function setExactMatches($exactMatches) {
        ArgumentGuard::greaterThanOrEqualToZero($exactMatches, "exactMatches");
        $this->exactMatches = $exactMatches;
    }

    /**
     * @param int $strictMatches The number of matches performed with match level set to {@link MatchLevel::STRICT}
     */
    public function setStrictMatches($strictMatches) {
        ArgumentGuard::greaterThanOrEqualToZero($strictMatches, "strictMatches");
        $this->strictMatches = $strictMatches;
    }

    /**
     * @param int $contentMatches The number of matches performed with match level set to {@link MatchLevel::CONTENT}
     */
    public function setContentMatches($contentMatches) {
        ArgumentGuard::greaterThanOrEqualToZero($contentMatches, "contentMatches");
        $this->contentMatches = $contentMatches;
    }

    /**
     * @param int $layoutMatches The number of matches performed with match level set to {@link MatchLevel::LAYOUT}
     */
    public function setLayoutMatches($layoutMatches) {
        ArgumentGuard::greaterThanOrEqualToZero($layoutMatches, "layoutMatches");
        $this->layoutMatches = $layoutMatches;
    }

    /**
     * @param int $noneMatches The number of matches performed with match level set to {@link MatchLevel::NONE}
     */
    public function setNoneMatches($noneMatches) {
        ArgumentGuard::greaterThanOrEqualToZero($noneMatches, "noneMatches");
        $this->noneMatches = $noneMatches;
    }

    /**
     * @param string $url The URL of the test results.
     */
    public function setUrl($url) {
        $this->url = $url;
    }

    /**
     * @param bool $isNew Whether or not this test has an existing baseline.
     */
    public function setNew($isNew) {
        $this->isNew = $isNew;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function toString() {
        $isNewTestStr = $this->isNew ? "New test" : "Existing test";
        return $isNewTestStr . "(" . $this->getStatus() . ")" . " [ steps: " . $this->getSteps()
                . ", matches: " . $this->getMatches()
                . ", mismatches:" . $this->getMismatches() . ", missing: "
                . $this->getMissing() . "] , URL: " . $this->getUrl();
    }
}