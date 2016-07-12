<?php

/**
 * The environment in which the application under test is executing.
 */
class AppEnvironment
{
    private $inferred;
    private $os;
    private $hostingApp;
    private $displaySize; //RectangleSize class

    /**
     * Creates a new AppEnvironment instance.
     */
    public function __construct($inferred = null)
    {
        $this->inferred = $inferred;
    }


    /**
     * Creates a new AppEnvironment instance.
     */
    public function AppEnvironment($os, $hostingApp, $displaySize/*RectangleSize*/)
    {
        $this->setOs($os);
        $this->setHostingApp($hostingApp);
        $this->setDisplaySize($displaySize);
    }

    /**
     * Gets the information inferred from the execution environment or {@code null} if no
     * information could be inferred.
     */
    public function getInferred()
    {
        return $this->inferred;
    }

    /**
     * Sets the inferred environment information.
     */
    public function setInferred($inferred)
    {
        $this->inferred = $inferred;
    }

    /**
     * Gets the OS hosting the application under test or {@code null} if
     * unknown.
     */
    public function getOs()
    {
        return $this->os;
    }

    /**
     * Sets the OS hosting the application under test or {@code null} if
     * unknown.
     */
    public function setOs($os)
    {
        $this->os = $os;
    }

    /**
     * Gets the application hosting the application under test or {@code null}
     * if unknown.
     */
    public function getHostingApp()
    {
        return $this->hostingApp;
    }

    /**
     * Sets the application hosting the application under test or {@code null}
     * if unknown.
     */
    public function setHostingApp($hostingApp)
    {
        $this->hostingApp = $hostingApp;
    }

    /**
     * Gets the display size of the application or {@code null} if unknown.
     */
    public function getDisplaySize()
    {
        return $this->displaySize;
    }

    /**
     * Sets the display size of the application or {@code null} if unknown.
     */
    public function setDisplaySize($size)
    {
        $this->displaySize = $size; /*RectangleSize*/
    }

    public function toString()
    {
        return "[os = " . ($this->os == null ? "?" : "'" . $this->os . "'") . " hostingApp = "
        . ($this->hostingApp == null ? "?" : "'" . $this->hostingApp . "'")
        . " displaySize = " . serialize($this->displaySize) . "]";
    }
}