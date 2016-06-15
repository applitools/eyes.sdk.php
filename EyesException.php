<?php
/*
* Applitools SDK for Selenium integration.
*/

/**
 * Applitools Eyes Exception.
 */
class EyesException extends RuntimeException {

    /**
     * Creates an EyesException instance.
     * @param message A description of the error.
     */
public EyesException(String message) {
super(message);
}

/**
 * Creates an EyesException instance.
 * @param message A description of the error.
 * @param e The throwable this exception should wrap.
 */
public EyesException(String message, Throwable e) {
    super(message, e);
}
}
?>