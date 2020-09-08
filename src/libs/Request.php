<?php


namespace tiny\libs;

use tiny\libs\File;
use tiny\interfaces\IRequest;

class Request implements IRequest {
    public string $url = "";
    public string $method = "";
    public string $contentType = "";
    public array $queryParameters = [];
    public array $pathParameters = [];
    public $body;
    public $data;
    private $headers;


    public function __construct()
    {
        $this->setUp();
    }

    private function setUp()
    {
        $url = $_GET['url'] ?? $_SERVER['REQUEST_URI'];
        $this->url = str_replace(DIRECTORY_ROOT, "", $url);
        $this->url = preg_replace("/\?.*/", "", $this->url);
        $this->method = $_SERVER['REQUEST_METHOD'] ?? '';
        $this->contentType = $_SERVER["CONTENT_TYPE"] ?? '';
        $this->queryParameters = $_GET;
        $this->files = $_FILES;
        $this->body = $this->parseBody();
        $this->headers =  apache_request_headers();
    }


    public function getQueryParam(string $name, $default = null)
    {
        return $this->queryParameters[$name] ?? $default;
    }
    
    public function getQueryParams(): array
    {
        return $this->queryParameters;
    }

    public function getPathParam(string $name, $default = null)
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
        return "Request {\n
            method: $this->method,\n
            contentType: $this->contentType,\n
            url: $this->url,\n
            queryParameters: [" . implode(',', $this->queryParameters) ."]\n
            pathParameters: [" . implode(',', $this->pathParameters) ."]\n
        }";
    }

    public function parseBody()
    {
        $res = $_POST;
        $inputJSON = file_get_contents('php://input');
        $raw = json_decode($inputJSON, false);
        return (object) array_merge((array) $res, (array) $raw);
        
    }

    public function getHeader(string $name)
    {
        return $this->headers[$name] ?? null;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setCookies(string $name, $value)
    {
        return Cookie::set($name, $value);
    }

    public function getCookies(string $name)
    {
        return Cookie::get($name);
    }

    public function destroyCookies(string $name)
    {
        return Cookie::destroy($name);
    }

    public function setSession(string $name, $value)
    {
        Session::set($name, $value);
    }
    
    public function getSession(string $name)
    {
        return Session::get($name);
    }

    public function destroySession($name)
    {
        Session::destroy($name);
    }

    public function uploadFile(string $destination, $fieldName, $newName=null): bool
    {
        $file = File::set($destination, $fieldName);
        if($newName)
            $file->rename($newName);
        $file->upload();
        return $file->errors();
    }

    public function file($fileName)
    {
        return $this->files[$fileName];
    }

    public function fileSize($fileName)
    {
        return $this->files[$fileName]["size"]; 
    }

    public function fileName($fileName)
    {
        return $this->files[$fileName]["name"];
    }
    
    public function fileType($fileName)
    {
        return $this->files[$fileName]["type"]; ;
    }

    public function acceptJson()
    {
        return HttpHeader::getMimeType('json') == $this->getHeader('Accept');
    }

}