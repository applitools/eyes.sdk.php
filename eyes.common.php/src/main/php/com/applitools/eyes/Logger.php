<?php
//require "NullLogHandler.php";

class Logger
{
    private $logHandler;

    public function Logger() {
        $this->logHandler = new NullLogHandler(); // Default.
    }

    public static function log($string)
    {
        var_dump($string);
        echo "<br>";
    }


    /**
     * @return The currently set log handler.
     */
    public function getLogHandler() {
        return $this->logHandler;
    }

    public static function verbose($string)
    {
        echo $string."\r\n";
       // var_dump($string);
       // echo "<br>";
    }
    /**
     * Sets the log handler.
     * @param handler The log handler to set. If you want a log handler which
     *                does nothing, use {@link
     *                com.applitools.eyes.NullLogHandler}.
     */
    public function setLogHandler(LogHandler $handler) {
        ArgumentGuard::notNull($handler, "handler");
        $this->logHandler = $handler;
    }
}