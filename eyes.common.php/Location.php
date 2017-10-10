<?php

namespace Applitools;

/**
 * A location in a two-dimensional plane.
 */
class Location
{
    private $x;
    private $y;

    private static $ZERO;

    /**
     * Creates a Location instance.
     * @param int $x The X coordinate of this location.
     * @param int $y The Y coordinate of this location.
     */
    public function __construct($x = null, $y = null)
    {
        $this->x = $x;
        $this->y = $y;
    }

    public static function getZero()
    {
        if (self::$ZERO == null) {
            self::$ZERO = new Location(0, 0);
        }
        return self::$ZERO;
    }

    public function equals($other)
    {
        if ($this === $other) {
            return true;
        }

        if (!($other instanceof Location)) {
            return false;
        }

        return ($this->x == $other->x) && ($this->y == $other->y);
    }

    public function hashCode()
    {
        return $this->x + $this->y;
    }

    /**
     * Translates this location by the specified amount (in place!).
     * @param int $dx The amount to offset the x-coordinate.
     * @param int $dy The amount to offset the y-coordinate.
     * @param Location $amount The amount the offset.
     */
    public function offset($dx = null, $dy = null, Location $amount = null)
    {
        if (!empty($amount)) {
            $this->x += $amount->x;
            $this->y += $amount->y;
        } else {
            ArgumentGuard::notNull($dx, "x");
            ArgumentGuard::notNull($dy, "y");
            $this->x += $dx;
            $this->y += $dy;
        }
    }

    /**
     * @return int The X coordinate of this location.
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * @return int The Y coordinate of this location.
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * Get a scaled location.
     *
     * @param double $scaleRatio The ratio by which to scale the results.
     * @return Location A scaled copy of the current location.
     */
    public function scale($scaleRatio)
    {
        return new Location((int)ceil($this->x * $scaleRatio), (int)ceil($this->y * $scaleRatio));
    }


    public function __toString()
    {
        return "({$this->x}, {$this->y})";
    }
}
