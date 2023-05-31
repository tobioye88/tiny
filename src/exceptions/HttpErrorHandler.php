<?php

namespace Tiny\Exceptions;

use Exception;
use Throwable;
use Tiny\Libs\App;
use Tiny\Libs\HttpHeader;
use Tiny\Libs\Request;

class HttpErrorHandler {

    public const BAD_REQUEST = 'BAD_REQUEST';
    public const INSUFFICIENT_PRIVILEGES = 'INSUFFICIENT_PRIVILEGES';
    public const NOT_ALLOWED = 'NOT_ALLOWED';
    public const NOT_IMPLEMENTED = 'NOT_IMPLEMENTED';
    public const RESOURCE_NOT_FOUND = 'RESOURCE_NOT_FOUND';
    public const SERVER_ERROR = 'SERVER_ERROR';
    public const UNAUTHENTICATED = 'UNAUTHENTICATED';
    public const UNAUTHORIZED = 'UNAUTHORIZED';
    
    public static function handle($exception) {
        $exception = $exception;
        $statusCode = 500;
        $type = self::SERVER_ERROR;
        $description = 'An internal error has occurred while processing your request.';
        $trace = $file = $line = '';

        if ($exception instanceof HttpException) {
            $statusCode = $exception->getCode();
            $description = $exception->getMessage();

            if ($exception instanceof ResourceNotFound) {
                $type = self::RESOURCE_NOT_FOUND;
            } elseif ($exception instanceof HttpMethodNotAllowedException) {
                $type = self::NOT_ALLOWED;
            } elseif ($exception instanceof HttpUnauthorizedException) {
                $type = self::UNAUTHORIZED;
            } elseif ($exception instanceof HttpForbiddenException) {
                $type = self::UNAUTHENTICATED;
            } elseif ($exception instanceof HttpBadRequestException) {
                $type = self::BAD_REQUEST;
            } elseif ($exception instanceof HttpNotImplementedException) {
                $type = self::NOT_IMPLEMENTED;
            }
        }

        if (
            !($exception instanceof HttpException)
            && ($exception instanceof Exception || $exception instanceof Throwable)
        ) {
            $description = $exception->getMessage();
            $trace = $exception->getTrace();
            $file = $exception->getFile();
            $line = $exception->getLIne();
        }

        $error = [
            'statusCode' => $statusCode,
            'error' => [
                'type' => $type,
                'description' => $description,
                'trace' => $trace,
                'file' => $file,
                'line' => $line,
            ],
        ];
        $mimeType = (new Request)->getHeader('Accept');
        switch ($mimeType) {
            case HttpHeader::getMimeType('json'):
                HttpHeader::setStatusCode($statusCode);
                HttpHeader::setContentType('json');
                echo json_encode($error, JSON_PRETTY_PRINT);
                break;
            default:
                HttpHeader::setStatusCode($statusCode);
                HttpHeader::setContentType('html');
                App::renderErrorView($description, $trace, $file, $line);
                break;
        }
    }
}
