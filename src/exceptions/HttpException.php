<?php

namespace Tiny\Exceptions;
use \Exception;

class HttpException extends Exception {
  public function __construct($message, $code = 400, Throwable $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}