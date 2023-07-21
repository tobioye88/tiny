<?php

namespace Tiny\Libs;

abstract class HttpAllowedMethods {
  protected array $routeMiddleWare = [];
    protected array $registeredRoute = [
        "GET" => [],
        "POST" => [],
        "PUT" => [],
        "PATCH" => [],
        "DELETE" => [],
        "OPTION" => [],
    ];
  
  public function register(string $method, string $route, callable|array $callback, array $middleware = []){
    $route = '/' . trim($route, "/");
    $this->routeMiddleWare[$route . ':' . $method] = $middleware;
    $this->registeredRoute[$method][$route] = $callback;
}

public function get(string $route, callable|array $callback, array $middleware = []){
    $this->register('GET', $route, $callback, $middleware);
}

public function post(string $route, callable|array $callback, array $middleware = []){
    $this->register('POST', $route, $callback, $middleware);
}

public function put(string $route, callable|array $callback, array $middleware = []){
    $this->register('PUT', $route, $callback, $middleware);
}

public function patch(string $route, callable|array $callback, array $middleware = []){
    $this->register('PATCH', $route, $callback, $middleware);
}

public function delete(string $route, callable|array $callback, array $middleware = []){
    $this->register('DELETE', $route, $callback, $middleware);
}

public function options(string $route, callable|array $callback, array $middleware = []){
    $this->routeMiddleWare[trim($route . ':POST', "/")] = $middleware;
}

public function any(string $route, callable|array $callback, array $middleware = []){
    foreach ($this->registeredRoute as $method => $value){
        $this->register($method, $route, $callback, $middleware);
    }
}

}