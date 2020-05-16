<?php


namespace Tiny\Libs;

use Tiny\Interfaces\IResponse;

class Response implements IResponse {
    // header('Access-Control-Allow-Origin: *');

    public function json($body, $statusCode=200){
        // header_remove();
        header('Content-type: application/json; charset=utf-8');
        HttpHeader::code($statusCode);
        echo json_encode($body); //, JSON_PRETTY_PRINT
        exit();
    }
    
    public function text($body){
        header('Content-type: text/plain; charset=utf-8');
        // echo 
    }
}