<?php

namespace Applitools;

/**
 * Encapsulates the "Options" section of the MatchExpectedOutput body data.
 */
class Options
{
    /** @var Trigger[] */
    private $userInputs;

    /** @var string */
    private $name;

    /** @var bool */
    private $ignoreMismatch;

    /** @var bool */
    private $ignoreMatch;

    /** @var bool */
    private $forceMismatch;

    /** @var bool */
    private $forceMatch;

    /** @var ImageMatchSettings */
    private $imageMatchSettings;

    /**
     * @param string $name The tag of the window to be matched.
     * @param Trigger[] $userInputs A list of triggers between the previous matchWindow call and the current matchWindow call. Can be array of size 0, but MUST NOT be null.
     * @param bool $ignoreMismatch Tells the server whether or not to store a mismatch for the current window as window in the session.
     * @param bool $ignoreMatch Tells the server whether or not to store a match for the current window as window in the session.
     * @param bool $forceMismatch Forces the server to skip the comparison process and mark the current window as a mismatch.
     * @param bool $forceMatch Forces the server to skip the comparison process and mark the current window as a match.
     * @param ImageMatchSettings $imageMatchSettings
     */
    public function __construct($name, /*Trigger[]*/
                                $userInputs, $ignoreMismatch,
                                $ignoreMatch, $forceMismatch, $forceMatch,
                                ImageMatchSettings $imageMatchSettings)
    {
        ArgumentGuard::notNull($userInputs, "userInputs");

        $this->name = $name;
        $this->userInputs = $userInputs;
        $this->ignoreMismatch = $ignoreMismatch;
        $this->ignoreMatch = $ignoreMatch;
        $this->forceMismatch = $forceMismatch;
        $this->forceMatch = $forceMatch;
        $this->imageMatchSettings = $imageMatchSettings;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Trigger[]
     */
    public function getUserInputs()
    {
        return $this->userInputs;
    }

    /**
     * @return bool
     */
    public function getIgnoreMismatch()
    {
        return $this->ignoreMismatch;
    }

    /**
     * @return bool
     */
    public function getIgnoreMatch()
    {
        return $this->ignoreMatch;
    }

    /**
     * @return bool
     */
    public function getForceMismatch()
    {
        return $this->forceMismatch;
    }

    /**
     * @return bool
     */
    public function getForceMatch()
    {
        return $this->forceMatch;
    }

    /**
     * @return ImageMatchSettings
     */
    public function getImageMatchSettings()
    {
        return $this->imageMatchSettings;
    }

    public function getUserInputsAsFormattedArray()
    {
        $retVal = [];

        foreach ($this->userInputs as $trigger) {
            $retVal[] = $trigger->getAsFormattedArray();
        }

        return $retVal;
    }
}

?>