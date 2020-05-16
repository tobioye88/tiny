<?php


namespace Tiny\Libs;

use Tiny\Interfaces\IRequest;

class Request implements IRequest {
    public $url = "";
    public $method = "";
    public $contentType = "";
    public $queryParameters = [];
    public $pathParameters = [];
    public $body;


    public function __construct()
    {
        $this->setUp();
    }

    public function setUp()
    {
        $url = $_GET['url'] ?? $_SERVER['REQUEST_URI'];
        $this->url = preg_replace("/\?.*/", "", $url);
        $this->method = $_SERVER['REQUEST_METHOD'] ?? '';
        $this->contentType = $_SERVER["CONTENT_TYPE"] ?? '';
        $this->queryParameters = $_GET;
        // $this->body = $_POST;
        $this->files = $_FILES;
        $this->body = $this->parseBody();
        // print_r(getallheaders());

        if ('PUT' === $this->method) {
            parse_str(file_get_contents('php://input'), $_PUT);
            // var_dump($_PUT); //$_PUT contains put fields 
        }else if ('DELETE' === $this->method) {
            parse_str(file_get_contents('php://input'), $_DELETE);
            // var_dump($_DELETE);
        }
    }


    public function getQueryParam(String $name, $default = null){
        return $this->queryParameters[$name] ?? $default;
    }
    
    public function getQueryParams()
    {
        return $this->queryParameters;
    }

    public function getPathParam(String $name, $default = null){
        return $this->pathParameters[$name] ?? $default;
    }

    public function getPathParams()
    {
        return $this->pathParameters;
    }

    public function setPathParams($pathParams)
    {
        $this->pathParameters = $pathParams;
    }
    
    public function getUrl(){
        return $this->url;
    }
    
    public function getMethod()
    {
        return $this->method;
    }

    public function __toString()
    {
        return "Request {
            method: $this->method,
            contentType: $this->contentType,
            url: $this->url,
            queryParameters: " . count($this->queryParameters) ."
            pathParameters: " . count($this->pathParameters) ."
        }";
    }

    public function parseBody($inArray = false){
        $inputJSON = file_get_contents('php://input');
        return json_decode($inputJSON, $inArray);
        
    }
}