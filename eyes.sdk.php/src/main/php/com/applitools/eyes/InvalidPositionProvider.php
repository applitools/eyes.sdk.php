<?php

namespace Applitools;

use Applitools\Exceptions\IllegalStateException;

/**
 * An implementation of {@link PositionProvider} which throws an exception
 * for every method. Can be used as a placeholder until an actual
 * implementation is set.
 */
class InvalidPositionProvider implements PositionProvider
{

    public function getCurrentPosition()
    {
        throw new IllegalStateException("This class does not implement methods!");
    }

    public function setPosition(Location $location)
    {
        throw new IllegalStateException("This class does not implement methods!");
    }

    public function getEntireSize()
    {
        throw new IllegalStateException("This class does not implement methods!");
    }

    public function getState()
    {
        throw new IllegalStateException("This class does not implement methods!");
    }

    public function restoreState(PositionMemento $state)
    {
        throw new IllegalStateException("This class does not implement methods!");
    }
}
