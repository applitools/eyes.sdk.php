<?php

namespace Applitools;

use Applitools\Exceptions\EyesException;

/**
 * Writes log messages to a file.
 */
class FileLogger implements LogHandler
{

    private $isVerbose;
    private $filename;
    private $append;
    private $file;

    /**
     * Creates a new FileHandler instance.
     * @param string $filename The file in which to save the logs.
     * @param bool $append Whether to append the logs if the current file exists,  or to overwrite the existing file.
     * @param bool $isVerbose Whether to handle or ignore verbose log messages.
     */
    public function __construct($filename = "eyes.log", $append = true, $isVerbose)
    {
        $this->filename = $filename;
        $this->append = $append;
        $this->isVerbose = $isVerbose;
        $file = null;
    }

    /**
     * Open the log file for writing.
     * @throws EyesException
     */
    public function open()
    {
        try {
            if ($this->file != null) {
                try {
                    fclose($this->file);
                } catch (\Exception $e) {
                }
            }
            $logDir = dirname($this->filename);
            if (!file_exists($logDir)) {
                mkdir($logDir, 0777, true);
            }
            $this->file = fopen($this->filename, $this->append ? "a" : "c");
        } catch (\Exception $e) {
            throw new EyesException("Failed to create log file: {$this->filename}", 0, $e);
        }
    }

    /**
     * Handle a message to be logged.
     * @param bool $verbose Whether this message is flagged as verbose or not.
     * @param string $logString The string to log.
     * @throws EyesException
     */
    public function onMessage($verbose, $logString)
    {
        if ($this->file != null && (!$verbose || $this->isVerbose)) {

            $currentTime = date("H:i:s");

            try {
                fwrite($this->file, "$currentTime Eyes: $logString" . PHP_EOL);
            } catch (\Exception $e) {
                throw new EyesException("Failed to write log to file!", $e);
            }
        }
    }

    /**
     * Close the log file for writing.
     */
    public function close()
    {
        try {
            if ($this->file != null) {
                fclose($this->file);
            }
        } catch (\Exception $e) {
        }
        $this->file = null;
    }
}
