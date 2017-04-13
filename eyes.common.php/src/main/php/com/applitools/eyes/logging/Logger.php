<?php

namespace Applitools;

class Logger
{
    /** @var LogHandler */
    private $logHandler;

    public function __construct(LogHandler $handler = null)
    {
        if (empty($handler)) {
            $this->logHandler = new NullLogHandler();
        } else {
            $this->logHandler = $handler;
        }
    }

    public function log($message)
    {
        $aa = $this->getPrefix() . $message;
        $this->logHandler->onMessage(false, $aa);
    }


    /**
     * @return LogHandler The currently set log handler.
     */
    public function getLogHandler()
    {
        return $this->logHandler;
    }

    public function verbose($message)
    {
        $this->logHandler->onMessage(true, $this->getPrefix() . $message);
    }

    /**
     * Sets the log handler.
     * @param LogHandler $handler The log handler to set. If you want a log handler which
     *                does nothing, use {@link Applitools\NullLogHandler}.
     */
    public function setLogHandler(LogHandler $handler)
    {
        ArgumentGuard::notNull($handler, "handler");
        $this->logHandler = $handler;
    }

    /**
     *
     * @return string The name of the method which called the logger, if possible, or an empty string.
     */
    private function getPrefix()
    {
        $deb = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $prefix = "";
        if (count($deb) >= 3) {
            $prefix = "caller: " . $deb[2]['class'] . '->' . $deb[2]['function'] . " ";
        }

        return $prefix;
    }

}

?>