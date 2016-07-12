<?php

/**
 * Argument validation utilities.
 */
class ArgumentGuard
{
    /**
     * Fails if the input parameter is null.
     * @param param The input parameter.
     * @param paramName The input parameter name.
     */
    public static function notNull($param, $paramName)
    {
        if (null == $param) {
            throw new Exception($paramName . " is null");// IllegalArgumentException
        }
    }

    /**
     * Fails if the input parameter equals the input value.
     * @param param The input parameter.
     * @param value The input value.
     * @param paramName The input parameter name.
     */
    public static function notEqual($param, $value, $paramName)
    {
        if (($param == $value) && ($param != null)) {
            throw new IllegalArgumentException($paramName . " == " . $value);
        }
    }

    /**
     * Fails if the input parameter string is null or empty.
     * @param param The input parameter.
     * @param paramName The input parameter name.
     */
    public static function notNullOrEmpty($param, $paramName)
    {
        if (empty($param)) {
            throw new IllegalArgumentException($paramName . " is empty");
        }
    }

    /**
     * Fails if the input parameter is not null.
     * @param param The input parameter.
     * @param paramName The input parameter name.
     */
    public static function isNull($param, $paramName)
    {
        if (null !== $param) {
            throw new IllegalArgumentException(paramName + " is not null");
        }
    }

    /**
     * Fails if the input integer parameter is negative.
     * @param param The input parameter.
     * @param paramName The input parameter name.
     */
    public static function greaterThanOrEqualToZero($param, $paramName)
    {
        if (0 > $param) {
            throw new IllegalArgumentException($paramName . " < 0");
        }
    }

    /**
     * Fails if the input integer parameter is smaller than 1.
     * @param param The input parameter.
     * @param paramName The input parameter name.
     */
    public static function greaterThanZero($param, $paramName)
    {
        if (0 >= $param) {
            throw new IllegalArgumentException($paramName . " < 1");
        }
    }


    /**
     * Fails if the input integer parameter is equal to 0.
     * @param param The input parameter.
     * @param paramName The input parameter name.
     */
    public static function notZero($param, $paramName)
    {
        if (0 == $param) {
            throw new IllegalArgumentException($paramName . " == 0");
        }
    }

    /**
     * Fails if isValid is false.
     * @param isValid Whether the current state is valid.
     * @param errMsg A description of the error.
     */
    public static function isValidState($isValid, $errMsg)
    {
        if (!$isValid) {
            throw new IllegalStateException($errMsg);
        }
    }
}
