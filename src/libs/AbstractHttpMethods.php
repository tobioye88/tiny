<?php

namespace tiny\libs;

use tiny\interfaces\IHttpAllowedMethods;

abstract class AbstractHttpMethods implements IHttpAllowedMethods
{
    protected array $routeMiddleWare = [];
    protected array $register = [
        "GET" => [],
        "POST" => [],
        "PUT" => [],
        "PATCH" => [],
        "DELETE" => [],
    ];

    public function get(String $route, callable $callback, array $middleware = []){
        $route = trim($route, "/");
        $this->routMiddleWare[$route] = $middleware;
        $this->register['GET'][$route] = $callback;
    }

    public function post(String $route, callable $callback, array $middleware = []){
        $route = trim($route, "/");
        $this->routMiddleWare[$route] = $middleware;
        $this->register['POST'][$route] = $callback; 
    }

    public function put(String $route, callable $callback, array $middleware = []){
        $route = trim($route, "/");
        $this->routMiddleWare[$route] = $middleware;
        $this->register['PUT'][$route] = $callback; 
    }

    public function patch(String $route, callable $callback, array $middleware = []){
        $route = trim($route, "/");
        $this->routMiddleWare[$route] = $middleware;
        $this->register['PATCH'][$route] = $callback; 
    }

    public function delete(String $route, callable $callback, array $middleware = []){
        $route = trim($route, "/");
        $this->routMiddleWare[$route] = $middleware;
        $this->register['DELETE'][$route] = $callback; 
    }

    public function options(String $route, callable $callback, array $middleware = []){
        $this->routMiddleWare[trim($route, "/")] = $middleware;
    }

    public function any(String $route, callable $callback, array $middleware = []){
        $route = trim($route, "/");
        $this->routMiddleWare[$route] = $middleware;
        foreach ($this->register as $key => $value){
            $this->register[$key][$route] = $callback;
        }
    }
}