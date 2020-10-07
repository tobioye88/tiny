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
        "OPTION" => [],
    ];

    public function get(string $route, callable $callback, array $middleware = []){
        $route = trim($route, "/");
        $this->routeMiddleWare[$route.':'.'GET'] = $middleware;
        $this->register['GET'][$route] = $callback;
    }

    public function post(string $route, callable $callback, array $middleware = []){
        $route = trim($route, "/");
        $this->routeMiddleWare[$route.':'.'POST'] = $middleware;
        $this->register['POST'][$route] = $callback; 
    }

    public function put(string $route, callable $callback, array $middleware = []){
        $route = trim($route, "/");
        $this->routeMiddleWare[$route.':'.'PUT'] = $middleware;
        $this->register['PUT'][$route] = $callback; 
    }

    public function patch(string $route, callable $callback, array $middleware = []){
        $route = trim($route, "/");
        $this->routeMiddleWare[$route.':'.'PATCH'] = $middleware;
        $this->register['PATCH'][$route] = $callback; 
    }

    public function delete(string $route, callable $callback, array $middleware = []){
        $route = trim($route, "/");
        $this->routeMiddleWare[$route.':'.'DELETE'] = $middleware;
        $this->register['DELETE'][$route] = $callback; 
    }

    public function options(string $route, callable $callback, array $middleware = []){
        $this->routeMiddleWare[trim($route.':'.'POST', "/")] = $middleware;
    }

    public function any(string $route, callable $callback, array $middleware = []){
        $route = trim($route, "/");
        foreach ($this->register as $key => $value){
            $this->routeMiddleWare[$route.':'.$key] = $middleware;
            $this->register[$key][$route] = $callback;
        }
    }
}