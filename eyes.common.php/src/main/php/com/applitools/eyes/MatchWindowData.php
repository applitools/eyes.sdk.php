<?php
/*
 * Applitools SDK for Selenium integration.
 */

/**
 * Encapsulates the data to be sent to the agent on a "matchWindow" command.
 */
class MatchWindowData
{

    // TODO Remove redundancy: userInputs and ignoreMismatch should only be inside Options. (requires server version update).
    private $userInputs; //Trigger[]
    private $appOutput; //AppOutput
    private $tag;
    private $ignoreMismatch;
    private $options; //Options

    /**
     * @param userInputs     A list of triggers between the previous matchWindow
     *                       call and the current matchWindow call. Can be array
     *                       of size 0, but MUST NOT be null.
     * @param appOutput      The appOutput for the current matchWindow call.
     * @param tag            The tag of the window to be matched.
     */
    public function __construct(/*Trigger[]*/$userInputs, AppOutput $appOutput,
                                $tag, $ignoreMismatch, Options $options)
    {

        ArgumentGuard::notNull(userInputs, "userInputs");

        $this->userInputs = $userInputs;
        $this->appOutput = $appOutput;
        $this->tag = $tag;
        $this->ignoreMismatch = $ignoreMismatch;
        $this->options = $options;
    }

    public function getAppOutput()
    {
        return $this->appOutput;
    }

    public function getUserInputs()
    {
        return $this->userInputs;
    }

    public function getTag()
    {
        return $this->tag;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getIgnoreMismatch()
    {
        return $this->ignoreMismatch;
    }
}
