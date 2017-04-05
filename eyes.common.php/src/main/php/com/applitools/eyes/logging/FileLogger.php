<?php

namespace Applitools;

/**
 * Writes log messages to a file.
 */
class FileLogger implements LogHandler {

    private $isVerbose;
    private $filename;
    private $append;
    private $file;

    /**
     * Creates a new FileHandler instance.
     * @param filename The file in which to save the logs.
     * @param append Whether to append the logs if the current file exists,
     *               or to overwrite the existing file.
     * @param isVerbose Whether to handle or ignore verbose log messages.
     */
    public function __construct($filename = "eyes.log", $append = true, $isVerbose) {
        $this->filename = $filename;
        $this->append = $append;
        $this->isVerbose = $isVerbose;
        $file = null;
    }


    /**
     * Open the log file for writing.
     */
    public function open() {
        try {
            if ($this->file != null) {
                //noinspection EmptyCatchBlock
                try {
                    $this->file->close();
                } catch (Exception $e) {}
            }
            //FIXME
           $this->file = ""/* FIXME new BufferedWriter(new FileWriter(new File(filename),
                    append))*/;
        } catch (IOException $e) {
            throw new EyesException("Failed to create log file!", $e);
        }
    }

    /**
     * Handle a message to be logged.
     * @param verbose Whether this message is flagged as verbose or not.
     * @param logString The string to log.
     */
    public function onMessage($verbose, $logString) {
        if ($this->file != null && (!$verbose || $this->isVerbose)) {

            $currentTime = date("H:i:s");

            try {//FIXME
                $this->write($currentTime . " Eyes: " . $logString);
            } catch (IOException $e) {
                throw new EyesException("Failed to write log to file!", $e);
            }
        }
    }

    /**
     * Close the log file for writing.
     */
    public function close() {
        //noinspection EmptyCatchBlock
        try {
            if ($this->file !=null) {
                //FIXME
                $this->file->close();
            }
        } catch (IOException $e) {}
        $this->file = null;
    }
}
