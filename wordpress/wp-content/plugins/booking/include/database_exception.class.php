<?php

/**
 * Exception occurs during a database operation.
 */
class DatabaseException extends Exception {

    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 0) {
        // make sure everything is assigned properly
        parent::__construct($message, $code);
    }

    // custom string representation of object
    public function __toString() {
        return __CLASS__ . ": {$this->message}\n";
    }
}

?>