<?php

namespace Applitools;

/**
 * The environment in which the application under test is executing.
 */
class AppEnvironment
{
    /** @var string */
    private $inferred;

    /** @var string */
    private $os;

    /** @var string */
    private $hostingApp;

    /** @var RectangleSize */
    private $displaySize;

    /**
     * Creates a new AppEnvironment instance.
     * @param string $os
     * @param string $hostingApp
     * @param RectangleSize $displaySize
     * @param string $inferred
     */
    public function __construct($os = null, $hostingApp = null, RectangleSize $displaySize = null, $inferred = null)
    {
        if(!empty($os) && !empty($hostingApp) && !empty($displaySize)){
            $this->setOs($os);
            $this->setHostingApp($hostingApp);
            $this->setDisplaySize($displaySize);
        }
        else if (!empty($inferred)){
            $this->setInferred($inferred);
        }
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
     * @param string $inferred
     */
    public function setInferred($inferred)
    {
        $this->inferred = $inferred;
    }

    /**
     * Gets the OS hosting the application under test or {@code null} if unknown.
     */
    public function getOs()
    {
        return $this->os;
    }

    /**
     * Sets the OS hosting the application under test or {@code null} if unknown.
     * @param string $os
     */
    public function setOs($os)
    {
        $this->os = $os;
    }

    /**
     * Gets the application hosting the application under test or {@code null} if unknown.
     */
    public function getHostingApp()
    {
        return $this->hostingApp;
    }

    /**
     * Sets the application hosting the application under test or {@code null} if unknown.
     * @param string $hostingApp
     */
    public function setHostingApp($hostingApp)
    {
        $this->hostingApp = $hostingApp;
    }

    /**
     * Gets the display size of the application or {@code null} if unknown.
     * @return RectangleSize|null
     */
    public function getDisplaySize()
    {
        return $this->displaySize;
    }

    /**
     * Sets the display size of the application or {@code null} if unknown.
     * @param RectangleSize|null $size
     */
    public function setDisplaySize(RectangleSize $size = null)
    {
        $this->displaySize = $size;
    }

    public function __toString()
    {
        return "[os = " . ($this->os == null ? "?" : "'" . $this->os . "'") . " hostingApp = "
        . ($this->hostingApp == null ? "?" : "'" . $this->hostingApp . "'")
        . " displaySize = {$this->displaySize}]";
    }
}