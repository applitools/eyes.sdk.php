<?php
namespace Applitools;
/**
 * Writes log messages to the standard output stream.
 */
class PrintLogHandler implements LogHandler {

    private $isVerbose;

    /**
     * Creates a new StdoutLogHandler instance.
     *
     * @param bool $isVerbose Whether to handle or ignore verbose log messages.
     */
    public function __construct($isVerbose = false) {
        $this->isVerbose = $isVerbose;
    }

    /**
     * Does nothing.
     */
    public function open() {}

    public function onMessage($verbose, $message) {
        if (!$verbose || $this->isVerbose) {
            $t = gettimeofday();
            $mSec = round($t["usec"] / 1000);
            print_r(date("H:i:s",$t["sec"]). ".$mSec Eyes: " . $message. PHP_EOL);
        }
    }

    /**
     * Does nothing.
     */
    public function close() {}
}

?>