<?php
namespace Tiny\Libs;

use Tiny\Interfaces\IHttpAllowedMethods;
use Tiny\Interfaces\IRouteGroup;

class RouteGroup implements IHttpAllowedMethods, IRouteGroup {
    protected array $routeMiddleWare = [];
    protected array $registeredRoute = [
        "GET" => [],
        "POST" => [],
        "PUT" => [],
        "PATCH" => [],
        "DELETE" => [],
        "OPTION" => [],
    ];

    public function register(string $method, string $route, callable $callback, array $middleware = []){
        $route = '/' . trim($route, "/");
        $this->routeMiddleWare[$route . ':' . $method] = $middleware;
        $this->registeredRoute[$method][$route] = $callback;
    }

    public function get(string $route, callable $callback, array $middleware = []){
        $this->register('GET', $route, $callback, $middleware);
    }

    public function post(string $route, callable $callback, array $middleware = []){
        $this->register('POST', $route, $callback, $middleware);
    }

    public function put(string $route, callable $callback, array $middleware = []){
        $this->register('PUT', $route, $callback, $middleware);
    }

    public function patch(string $route, callable $callback, array $middleware = []){
        $this->register('PATCH', $route, $callback, $middleware);
    }

    public function delete(string $route, callable $callback, array $middleware = []){
        $this->register('DELETE', $route, $callback, $middleware);
    }

    public function options(string $route, callable $callback, array $middleware = []){
        $this->routeMiddleWare[trim($route . ':POST', "/")] = $middleware;
    }

    public function any(string $route, callable $callback, array $middleware = []){
        foreach ($this->registeredRoute as $method => $value){
            $this->register($method, $route, $callback, $middleware);
        }
    }

    public function getRoutes(string $prefix)
    {
        foreach ($this->registeredRoute as $method => $routesArray) {
            $newMethod[$method] = [];
            foreach ($routesArray as $routeSuffix => $callback) {
                $newKey = '/' . trim($prefix, "/") . "/" . trim($routeSuffix, "/");
                $newKey = '/' . trim($newKey, '/');
                $newMethod[$method][$newKey] = $callback;
            }
            unset($this->registeredRoute[$method]);
            $this->registeredRoute = $newMethod;
        }
        return $this->registeredRoute;
    }

    public function getMiddleware(string $prefix, array $middleware =[])
    {
        $newRouteMiddleware = [];
        foreach ($this->routeMiddleWare as $routeSuffix => $callbackArray) {
            $newKey = '/' . trim($prefix, "/") . "/" . trim($routeSuffix, "/");
            $newKey = '/' . trim($newKey, '/');
            $newKey = preg_replace('(/:)', ':', $newKey);
            $newRouteMiddleware[$newKey] = array_merge($middleware, $callbackArray);
        }
        unset($this->registeredRoute);
        return $newRouteMiddleware;
    }
}

