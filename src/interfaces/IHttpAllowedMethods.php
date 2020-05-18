<?php

namespace Tiny\Interfaces;

interface IHttpAllowedMethods {

    public function get(String $route, callable $callback, array $middlewares = []);
    public function post(String $route, callable $callback, array $middlewares = []);
    public function put(String $route, callable $callback, array $middlewares = []);
    public function delete(String $route, callable $callback, array $middlewares = []);
    public function any(String $route, callable $callback, array $middlewares = []);
    public function options(String $route, callable $callback, array $middlewares = []);
    public function patch(String $route, callable $callback, array $middlewares = []);
    
}