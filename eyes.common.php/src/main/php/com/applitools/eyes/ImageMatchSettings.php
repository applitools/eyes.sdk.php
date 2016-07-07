<?php
require "MatchLevel.php";
/**
 * Encapsulates match settings for the a session.
 */
class ImageMatchSettings {
    private $matchLevel; //MatchLevel
    private $exact; //ExactMatchSettings

    public function __construct(MatchLevel $matchLevel = null, ExactMatchSettings $exact = null) {
        if(empty($matchLevel)){
            $matchLevel = MatchLevel::STRICT;
        }
        $this->matchLevel = $matchLevel;
        $this->exact = $exact;
    }


    /**
     *
     * @return The "strictness" level of the match.
     */
    public function getMatchLevel() {
        return $this->matchLevel;
    }

    /**
     *
     * @param matchLevel The "strictness" level of the match.
     */
    public function setMatchLevel(MatchLevel $matchLevel) {
        $this->matchLevel = $matchLevel;
    }

    /**
     *
     * @return The parameters for the "Exact" match settings.
     */
    public function getExact() {
        return $this->exact;
    }

    /**
     *
     * @param exact The parameters for the "exact" match settings.
     */
    public function setExact(ExactMatchSettings $exact) {
        $this->exact = $exact;
    }

    public function toString() {
        return sprintf("Match level: %s, Exact match settings: %s",
                $this->matchLevel, $this->exact);
    }
}
