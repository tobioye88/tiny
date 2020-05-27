<?php

namespace tiny\exceptions;

use Exception;

class HttpBadRequestException extends HttpException {
    
    public function __construct($message, $code = 400, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }


    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}