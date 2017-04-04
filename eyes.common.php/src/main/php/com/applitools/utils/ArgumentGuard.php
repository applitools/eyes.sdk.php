<?php

namespace Applitools;

use InvalidArgumentException;

/**
 * Argument validation utilities.
 */
class ArgumentGuard
{
    /**
     * Fails if the input parameter is null.
     * @param mixed $param The input parameter.
     * @param string $paramName The input parameter name.
     * @throws InvalidArgumentException
     */
    public static function notNull($param, $paramName)
    {
        if (null === $param) { //FIXME
            throw new InvalidArgumentException ($paramName . " is null");
        }
    }

    /**
     * Fails if the input parameter equals the input value.
     * @param mixed $param The input parameter.
     * @param mixed $value The input value.
     * @param string $paramName The input parameter name.
     * @throws InvalidArgumentException
     */
    public static function notEqual($param, $value, $paramName)
    {
        if (($param == $value) && ($param != null)) {
            throw new InvalidArgumentException($paramName . " == " . $value);
        }
    }

    /**
     * Fails if the input parameter string is null or empty.
     * @param mixed $param The input parameter.
     * @param string $paramName The input parameter name.
     * @throws InvalidArgumentException
     */
    public static function notNullOrEmpty($param, $paramName)
    {
        if (empty($param)) {
            throw new InvalidArgumentException($paramName . " is empty");
        }
    }

    /**
     * Fails if the input parameter is not null.
     * @param mixed $param The input parameter.
     * @param string $paramName The input parameter name.
     * @throws InvalidArgumentException
     */
    public static function isNull($param, $paramName)
    {
        if (null !== $param) {
            throw new InvalidArgumentException($paramName . " is not null");
        }
    }

    /**
     * Fails if the input integer parameter is negative.
     * @param mixed $param The input parameter.
     * @param string $paramName string The input parameter name.
     * @throws InvalidArgumentException
     */
    public static function greaterThanOrEqualToZero($param, $paramName)
    {
        if (0 > $param) {
            throw new InvalidArgumentException($paramName . " < 0");
        }
    }

    /**
     * Fails if the input integer parameter is smaller than 1.
     * @param mixed $param The input parameter.
     * @param string $paramName The input parameter name.
     * @throws InvalidArgumentException
     */
    public static function greaterThanZero($param, $paramName)
    {
        if (0 >= $param) {
            throw new InvalidArgumentException($paramName . " < 1");
        }
    }


    /**
     * Fails if the input integer parameter is equal to 0.
     * @param mixed $param The input parameter.
     * @param string $paramName The input parameter name.
     * @throws InvalidArgumentException
     */
    public static function notZero($param, $paramName)
    {
        if (0 == $param) {
            throw new InvalidArgumentException($paramName . " == 0");
        }
    }

    /**
     * Fails if isValid is false.
     * @param bool $isValid Whether the current state is valid.
     * @param string $errMsg A description of the error.
     * @throws InvalidArgumentException
     */
    public static function isValidState($isValid, $errMsg)
    {
        if (!$isValid) {
            throw new InvalidArgumentException($errMsg);
        }
    }
}
