<?php

namespace Tiny\Interfaces;

interface IRequest {

    public function getQueryParam(String $name, $default = null);
    public function getQueryParams();
    public function getPathParam(String $name, $default = null);
    public function getPathParams();
    public function getUrl();
    public function getMethod();

}