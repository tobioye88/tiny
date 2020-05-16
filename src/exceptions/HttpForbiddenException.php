<?php

namespace Tiny\exceptions;

use Exception;

class HttpForbiddenException extends HttpException {
     
     public function __construct($message, $code = 403, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }


    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}