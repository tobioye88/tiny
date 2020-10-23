<?php

namespace tiny\libs;

use tiny\interfaces\IHttpAllowedMethods;

abstract class AbstractHttpMethods implements IHttpAllowedMethods
{
    protected array $routeMiddleWare = [];
    protected array $registeredRoute = [
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
        $this->registeredRoute['GET'][$route] = $callback;
    }

    public function post(string $route, callable $callback, array $middleware = []){
        $route = trim($route, "/");
        $this->routeMiddleWare[$route.':'.'POST'] = $middleware;
        $this->registeredRoute['POST'][$route] = $callback; 
    }

    public function put(string $route, callable $callback, array $middleware = []){
        $route = trim($route, "/");
        $this->routeMiddleWare[$route.':'.'PUT'] = $middleware;
        $this->registeredRoute['PUT'][$route] = $callback; 
    }

    public function patch(string $route, callable $callback, array $middleware = []){
        $route = trim($route, "/");
        $this->routeMiddleWare[$route.':'.'PATCH'] = $middleware;
        $this->registeredRoute['PATCH'][$route] = $callback; 
    }

    public function delete(string $route, callable $callback, array $middleware = []){
        $route = trim($route, "/");
        $this->routeMiddleWare[$route.':'.'DELETE'] = $middleware;
        $this->registeredRoute['DELETE'][$route] = $callback; 
    }

    public function options(string $route, callable $callback, array $middleware = []){
        $this->routeMiddleWare[trim($route.':'.'POST', "/")] = $middleware;
    }

    public function any(string $route, callable $callback, array $middleware = []){
        $route = trim($route, "/");
        foreach ($this->registeredRoute as $key => $value){
            $this->routeMiddleWare[$route.':'.$key] = $middleware;
            $this->registeredRoute[$key][$route] = $callback;
        }
    }
}