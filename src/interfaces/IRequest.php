<?php

namespace tiny\interfaces;

interface IRequest {

    
    public function getQueryParam(string $name, $default = null);
    
    public function getQueryParams(): array;
    
    public function getPathParam(string $name, $default = null);
    
    public function getPathParams();
    
    public function getUrl();
    
    public function getMethod();
    
    public function setPathParams($pathParams);
    
    public function getHeader(string $name);
    
    public function getHeaders();
    
    public function setCookies(string $name, $value);

    public function getCookies(string $name);
    
    public function destroyCookies(string $name);

    public function getSession(string $name);

    public function setSession(string $name, $value);
    
    public function destroySession($name);

    public function uploadFile(string $destination, string $fieldName, $newName=null): bool;

    public function file($fileName);

    public function fileSize($fileName);

    public function fileName($fileName);
    
    public function fileType($fileName);

    public function acceptJson();

}