<?php

namespace Applitools;

/**
 * Encapsulates the "Options" section of the MatchExpectedOutput body data.
 */
class Options
{
    private $userInputs; //Trigger[]
    private $name;
    private $ignoreMismatch;
    private $ignoreMatch;
    private $forceMismatch;
    private $forceMatch;

    /**
     * @param string $name The tag of the window to be matched.
     * @param array $userInputs A list of triggers between the previous matchWindow call and the current matchWindow call. Can be array of size 0, but MUST NOT be null.
     * @param bool $ignoreMismatch Tells the server whether or not to store a mismatch for the current window as window in the session.
     * @param bool $ignoreMatch Tells the server whether or not to store a match for the current window as window in the session.
     * @param bool $forceMismatch Forces the server to skip the comparison process and mark the current window as a mismatch.
     * @param bool $forceMatch Forces the server to skip the comparison process and mark the current window as a match.
     */
    public function __construct($name, /*Trigger[]*/
                                $userInputs, $ignoreMismatch,
                                $ignoreMatch, $forceMismatch, $forceMatch)
    {
        ArgumentGuard::notNull($userInputs, "userInputs");

        $this->name = $name;
        $this->userInputs = $userInputs;
        $this->ignoreMismatch = $ignoreMismatch;
        $this->ignoreMatch = $ignoreMatch;
        $this->forceMismatch = $forceMismatch;
        $this->forceMatch = $forceMatch;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getUserInputs()
    {
        return $this->userInputs;
    }

    public function getIgnoreMismatch()
    {
        return $this->ignoreMismatch;
    }

    public function getIgnoreMatch()
    {
        return $this->ignoreMatch;
    }

    public function getForceMismatch()
    {
        return $this->forceMismatch;
    }

    public function getForceMatch()
    {
        return $this->forceMatch;
    }
}

?>