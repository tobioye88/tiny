<?php
namespace Tiny\Libs;

use Tiny\Interfaces\IHttpAllowedMethods;

class Group implements IHttpAllowedMethods {
    private $routeMiddleWare = [];
    private $register = [
        "GET" => [],
        "POST" => [],
        "PUT" => [],
        "DELETE" => [],
    ];

    public function get(String $route, callable $callback, array $middlewares = []){
        $this->routeMiddleWare[trim($route, "/")] = $middlewares;
        $this->register['GET'][trim($route, "/")] = $callback;
    }
    public function post(String $route, callable $callback, array $middlewares = []){
        $this->routeMiddleWare[trim($route, "/")] = $middlewares;
        $this->register['POST'][trim($route, "/")] = $callback;
    }
    public function put(String $route, callable $callback, array $middlewares = []){
        $this->routeMiddleWare[trim($route, "/")] = $middlewares;
        $this->register['PUT'][trim($route, "/")] = $callback;
    }
    public function delete(String $route, callable $callback, array $middlewares = []){
        $this->routeMiddleWare[trim($route, "/")] = $middlewares;
        $this->register['DELETE'][trim($route, "/")] = $callback;
    }
    public function any(String $route, callable $callback, array $middlewares = []){
        $this->routeMiddleWare[trim($route, "/")] = $middlewares;
        $this->register['ANY'][trim($route, "/")] = $callback;
    }
    public function options(String $route, callable $callback, array $middlewares = []){
        $this->routeMiddleWare[trim($route, "/")] = $middlewares;
        $this->register['OPTIONS'][trim($route, "/")] = $callback;
    }
    public function patch(String $route, callable $callback, array $middlewares = []){
        $this->routeMiddleWare[trim($route, "/")] = $middlewares;
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

    public function getMiddlewares(String $prefix, array $middlewares =[])
    {
        $newRouteMiddlewares = [];
        foreach ($this->routeMiddleWare as $previousRoute => $callbackArray) {
            $newKey = trim($prefix, "/") . "/" . trim($previousRoute, "/");
            $newKey = trim($newKey, '/');
            $newRouteMiddlewares[$newKey] = array_merge($middlewares, $callbackArray);
        }
        unset($this->register);
        return $newRouteMiddlewares;
    }
}

