<?php

namespace Applitools;

use Applitools\Selenium\Exceptions\EyesDriverOperationException;

/**
 * Encapsulates page/element positioning.
 */
interface PositionProvider
{
    /**
     *
     * @return Location The current position, or {@code null} if position is not available.
     */
    function getCurrentPosition();

    /**
     * Go to the specified location.
     * @param Location $location The position to set.
     */
    function setPosition(Location $location);

    /**
     *
     * @return RectangleSize The entire size of the container which the position is relative to.
     * @throws EyesDriverOperationException
     */
    function getEntireSize();

    /**
     * Get the current state of the position provider. This is different from
     * {@link #getCurrentPosition()} in that the state of the position provider
     * might include other data than just the coordinates. For example a CSS
     * translation based position provider (in WebDriver based SDKs), might
     * save the entire "transform" style value as its state.
     *
     * @return PositionMemento The current state of the position provider, which can later be
     * restored by  passing it as a parameter to {@link #restoreState}.
     */
    function getState();

    /**
     * Restores the state of the position provider to the state provided as a
     * parameter.
     *
     * @param PositionMemento $state The state to restore to.
     */
    function restoreState(PositionMemento $state);
}
