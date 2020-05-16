<?php


namespace Tiny\Libs;

use Tiny\exceptions\FileNotFoundException;
use Tiny\Interfaces\IResponse;

class Response implements IResponse {
    // header('Access-Control-Allow-Origin: *');

    public function status(int $code){
        HttpHeader::setStatusCode($code);
        return $this;
    }

    public function view($path, $extra = null){
        HttpHeader::setContentType("html");
        $path = rtrim($path, ".php");
        require_once App::BASE_PATH . $path . ".php";
    }

    public function file($path){
        $stringArray = explode(".", $path);
        $ext = end($stringArray);
        array_pop($stringArray);
        $fileName = implode($stringArray);
        $attachment_location = $_SERVER["DOCUMENT_ROOT"] . $path;
        if (file_exists($attachment_location)) {
            
            header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
            header("Cache-Control: public"); // needed for internet explorer
            HttpHeader::setContentType($ext);
            // header("Content-Type: application/zip");
            header("Content-Transfer-Encoding: Binary");
            header("Content-Length:". filesize($attachment_location));
            header("Content-Disposition: attachment; filename=$fileName");
            readfile($attachment_location);
            die();        
        } else {
            throw new FileNotFoundException("File Not Found");
        }
    }

    public function json($body, $statusCode=200){
        // header_remove();
        // header('Content-type: application/json; charset=utf-8');
        HttpHeader::setContentType('json');
        HttpHeader::setStatusCode($statusCode);
        echo json_encode($body); //, JSON_PRETTY_PRINT
        exit();
    }
    
    public function text($body){
        header('Content-type: text/plain; charset=utf-8');
        // echo 
    }
}