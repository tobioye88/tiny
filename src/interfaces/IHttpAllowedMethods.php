<?php

namespace tiny\interfaces;

interface IHttpAllowedMethods {

    public function get(string $route, callable $callback, array $middleware = []);
    public function post(string $route, callable $callback, array $middleware = []);
    public function put(string $route, callable $callback, array $middleware = []);
    public function delete(string $route, callable $callback, array $middleware = []);
    public function any(string $route, callable $callback, array $middleware = []);
    public function options(string $route, callable $callback, array $middleware = []);
    public function patch(string $route, callable $callback, array $middleware = []);
    
}