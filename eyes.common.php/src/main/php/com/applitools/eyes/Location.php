<?php

/**
 * A location in a two-dimensional plane.
 */
class Location
{ //implements Cloneable just possible to clone?
    private $x;
    private $y;

    //private $ZERO;

    /**
     * Creates a Location instance.
     * @param other A location instance from which to create the location.
     * @param x The X coordinate of this location.
     * @param y The Y coordinate of this location.
     */
    public function __construct($x = null, $y = null, Location $other = null)
    {
        if(!empty($other)){
            ArgumentGuard::notNull($other, "other");

            $this->x = $other->getX();
            $this->y = $other->getY();
        }else{
            //ArgumentGuard::notNull($x, "x"); //FIXME need to check
            //ArgumentGuard::notNull($y, "y");
            //$this->ZERO = new Location(0, 0);
            $this->x = $x;
            $this->y = $y;
        }
    }

    public static function getZero(){ //FIXME instead of self::ZERO
        return new Location(0, 0);
    }
    public function equals(/*Object */$obj)
    {
        if ($this == $obj) {
            return true;
        }

        if (!($obj instanceof Location)) {
            return false;
        }

        $other = /*(Location)*/ $obj; // ???? clone
        return ($this->getX() == $other->getX()) && ($this->getY() == $other->getY());
    }

    public function hashCode()
    {
        return $this->getX() + $this->getY();
    }

    /**
     * Translates this location by the specified amount (in place!).
     * <p>
     * @param dx The amount to offset the x-coordinate.
     * @param dy The amount to offset the y-coordinate.
     * @param amount The amount the offset.
     */
    public function offset($dx = null, $dy = null, Location $amount = null)
    {
        if(!empty($amount)){
            $this->x += $amount->getX();
            $this->y += $amount->getY();
        }else{
            ArgumentGuard::notNull($dx, "x");
            ArgumentGuard::notNull($dy, "y");
            $this->x += $dx;
            $this->y += $dy;
        }

    }

    /**
     * Translates this location by the specified amount (in place!).
     * <p>



    /**
     * @return The X coordinate of this location.
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * @return The Y coordinate of this location.
     */
    public function getY()
    {
        return $this->y;
    }

    public function toString()
    {
        return "(" . $this->x . ", " . $this->y . ")";
    }
}
