<?php

namespace Tiny\Interfaces;

interface IRequest {

    
    public function getQueryParam(String $name, $default = null);
    
    public function getQueryParams();
    
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
    
    public function destroySession(String $name);

}