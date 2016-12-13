<?php
/**
 * Writes log messages to the standard output stream.
 */
class PrintLogHandler implements LogHandler {

    private $isVerbose;

    /**
     * Creates a new StdoutLogHandler instance.
     *
     * @param isVerbose Whether to handle or ignore verbose log messages.
     */
    public function __construct($isVerbose = null) {
        $this->isVerbose = $isVerbose;
    }

    /**
     * Does nothing.
     */
    public function open() {}

    public function onMessage($verbose, $message) {
        if (!$verbose || $this->isVerbose) {
            print_r(date("H:i:s") . " Eyes: " . $message. "\r\n");
        }
    }

    /**
     * Does nothing.
     */
    public function close() {}
}