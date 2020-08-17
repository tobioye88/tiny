<?php

namespace tiny\interfaces;

interface IRequest {

    
    public function getQueryParam(String $name, $default = null);
    
    public function getQueryParams(): array;
    
    public function getPathParam(String $name, $default = null);
    
    public function getPathParams();
    
    public function getUrl();
    
    public function getMethod();
    
    public function setPathParams($pathParams);
    
    public function getHeader(String $name);
    
    public function getHeaders();
    
    public function setCookies(String $name, $value);

    public function getCookies(String $name);
    
    public function destroyCookies(String $name);

    public function getSession(String $name);

    public function setSession(String $name, $value);
    
    public function destroySession($name);

    public function uploadFile(string $destination, string $fieldName, $newName=null): bool;

    public function file($fileName);

    public function fileSize($fileName);

    public function fileName($fileName);
    
    public function fileType($fileName);

    public function acceptJson();

}