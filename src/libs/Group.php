<?php
namespace tiny\libs;

use tiny\interfaces\IHttpAllowedMethods;

class Group implements IHttpAllowedMethods {
    private array $routeMiddleWare = [];
    private array $register = [
        "GET" => [],
        "POST" => [],
        "PUT" => [],
        "DELETE" => [],
    ];

    public function get(String $route, callable $callback, array $middleware = []){
        $this->routeMiddleWare[trim($route, "/")] = $middleware;
        $this->register['GET'][trim($route, "/")] = $callback;
    }
    public function post(String $route, callable $callback, array $middleware = []){
        $this->routeMiddleWare[trim($route, "/")] = $middleware;
        $this->register['POST'][trim($route, "/")] = $callback;
    }
    public function put(String $route, callable $callback, array $middleware = []){
        $this->routeMiddleWare[trim($route, "/")] = $middleware;
        $this->register['PUT'][trim($route, "/")] = $callback;
    }
    public function delete(String $route, callable $callback, array $middleware = []){
        $this->routeMiddleWare[trim($route, "/")] = $middleware;
        $this->register['DELETE'][trim($route, "/")] = $callback;
    }
    public function any(String $route, callable $callback, array $middleware = []){
        $this->routeMiddleWare[trim($route, "/")] = $middleware;
        $this->register['ANY'][trim($route, "/")] = $callback;
    }
    public function options(String $route, callable $callback, array $middleware = []){
        $this->routeMiddleWare[trim($route, "/")] = $middleware;
        $this->register['OPTIONS'][trim($route, "/")] = $callback;
    }
    public function patch(String $route, callable $callback, array $middleware = []){
        $this->routeMiddleWare[trim($route, "/")] = $middleware;
        $this->register['PATCH'][trim($route, "/")] = $callback;
    }

    public function getRoutes(String $prefix)
    {
        foreach ($this->register as $method => $routesArray) {
            $newMethod[$method] = [];
            foreach ($routesArray as $previousKey => $callback) {
                $newKey = trim($prefix, "/") . "/" . trim($previousKey, "/");
                $newKey = trim($newKey, '/');
                $newMethod[$method][$newKey] = $callback;
            }
            unset($this->register[$method]);
            $this->register = $newMethod;
        }
        // print_r($this->register);
        return $this->register;
    }

    public function getMiddlewares(String $prefix, array $middleware =[])
    {
        $newRouteMiddlewares = [];
        foreach ($this->routeMiddleWare as $previousRoute => $callbackArray) {
            $newKey = trim($prefix, "/") . "/" . trim($previousRoute, "/");
            $newKey = trim($newKey, '/');
            $newRouteMiddlewares[$newKey] = array_merge($middleware, $callbackArray);
        }
        unset($this->register);
        return $newRouteMiddlewares;
    }
}

