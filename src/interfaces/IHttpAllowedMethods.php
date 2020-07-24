<?php

namespace tiny\interfaces;

interface IHttpAllowedMethods {

    public function get(String $route, callable $callback, array $middleware = []);
    public function post(String $route, callable $callback, array $middleware = []);
    public function put(String $route, callable $callback, array $middleware = []);
    public function delete(String $route, callable $callback, array $middleware = []);
    public function any(String $route, callable $callback, array $middleware = []);
    public function options(String $route, callable $callback, array $middleware = []);
    public function patch(String $route, callable $callback, array $middleware = []);
    
}