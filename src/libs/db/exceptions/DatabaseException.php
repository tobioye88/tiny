<?php
namespace tiny\libs\db\exceptions;

use \Exception;

class DatabaseException extends Exception {

    public function __construct($message = "Database exception occurred", $code = 500, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }


    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
    
}