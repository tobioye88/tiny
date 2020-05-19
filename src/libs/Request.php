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
    private $headers;


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
        $this->files = $_FILES;
        $this->body = $this->parseBody();
        $this->headers =  apache_request_headers();
    }


    public function getQueryParam(String $name, $default = null)
    {
        return $this->queryParameters[$name] ?? $default;
    }
    
    public function getQueryParams()
    {
        return $this->queryParameters;
    }

    public function getPathParam(String $name, $default = null)
    {
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
    
    public function getUrl()
    {
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

    public function parseBody()
    {
        $res = $_POST;
        $inputJSON = file_get_contents('php://input');
        $raw = json_decode($inputJSON, false);
        return (object) array_merge((array) $res, (array) $raw);
        
    }

    public function getHeader(String $name)
    {
        return $this->headers[$name];
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setCookies(String $name, $value)
    {
        return Cookie::set($name, $value);
    }

    public function getCookies(String $name)
    {
        return Cookie::get($name);
    }

    public function destroyCookies(String $name)
    {
        return Cookie::destroy($name);
    }

    public function setSession(String $name, $value)
    {
        Session::set($name, $value);
    }
    
    public function getSession(String $name)
    {
        return Session::get($name);
    }

    public function destroySession(String $name)
    {
        return Session::destroy($name);
    }
}