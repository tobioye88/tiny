<?php


namespace Tiny\Libs;

use stdClass;
use Tiny\Libs\File;
use Tiny\Interfaces\IRequest;

class Request implements IRequest {
    public string $url = "";
    public string $method = "";
    public string $contentType = "";
    public array $queryParameters = [];
    public array $pathParameters = [];
    public $files;
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
        $raw = $this->parseRawInput();
        return (object) array_merge((array) $res, (array) $raw);
    }

    private function parseRawInput(){
        $inputJSON = file_get_contents('php://input');
        $raw = new stdClass;
        if(substr($inputJSON, 0, 2) == '"{'){
            $inputJSON = rtrim($inputJSON, '"');
            $inputJSON = ltrim($inputJSON, '"');
            $inputJSON = stripslashes($inputJSON);
        }
        if(!startsWith($inputJSON, "------WebKitFormBoundary") && strlen($inputJSON)) $raw = json_decode($inputJSON, false);

        if(startsWith($inputJSON, "------WebKitFormBoundary")){
            preg_match_all('/\"(.+)\"\s+(.*)/', $inputJSON, $matches);

            foreach($matches[1] as $key => $value){
                if(stringContains($value, '[')){
                    $count = countOccurrence($value, '[');

                    $newValue = preg_replace('/\[|\]/', '', $value);
                    if(!isset($raw->{$newValue})){
                        $raw->{$newValue} = [];
                    }
                    $raw->{$newValue} = $this->buildBodyArray($raw->{$newValue}, trim($matches[2][$key]), $count);
                }else{
                    $raw->{$value} = trim($matches[2][$key]);
                }
            }
        }
        return $raw;
    }


    private function buildBodyArray(&$array, $value, $dept){
        if($dept < 1){
            return [];
        }
        
        if ($dept == 1) {
            $array[] = $value;
            return $array; /*Terminating condition*/
        }
        
        if(($dept - 1 == 1) && isset($array[0])){
            $array[0][] = $value;
            return $array;
        }

        return [$this->buildBodyArray($array[0], $value, $dept-1)];
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

    public function hasFile(string $fileName): bool 
    {
        return isset($this->files[$fileName]);
    }

    public function file($fileName)
    {
        return $this->files[$fileName];
    }
    
    public function files($fileName = null)
    {
        if(!$fileName) return $this->files;
        return $this->files[$fileName]; // TODO COMPLETE IMPLEMENTATION
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

    public function acceptJson(): bool
    {
        $subject = $this->getHeader('Accept');
        if(stripos($subject, ",")){
            return stripos($subject, HttpHeader::getMimeType('json')) !== false;
        }
        return HttpHeader::getMimeType('json') == $this->getHeader('Accept');
    }

    public function getBodyAsArray(array $nameList): array
    {
        $responseList = [];
        foreach($nameList as $item){
            if(!isset($this->body->{$item})){
                continue;
            }
            $responseList[$item] = $this->body->{$item};
        }
        return $responseList;
    }

    public function getBodyAsObject(array $nameList): object
    {
        return (object) $this->getBodyAsArray($nameList);
    }

}