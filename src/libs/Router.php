<?php

namespace Tiny\Libs;;

use Tiny\Exceptions\HttpMethodNotAllowedException;
use Tiny\Exceptions\ResourceNotFound;
use Tiny\Interfaces\IHttpAllowedMethods;
use Tiny\Interfaces\IRequest;
use Tiny\Interfaces\IResponse;
use Tiny\Interfaces\IRouteGroup;
use Tiny\Interfaces\IRouteMatcher;

class Router implements IHttpAllowedMethods
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

    public function __construct(
        private IRouteGroup $routeGroup,
        private IRouteMatcher $routeMatcher) {}

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

    public function group(string $prefix, callable $callback, array $middleware = []){;
        $callback($this->routeGroup);
        $groupRoutes = $this->routeGroup->getRoutes($prefix);
        $groupMiddleware = $this->routeGroup->getMiddleware($prefix, $middleware);

        foreach ($this->registeredRoute as $method => $value){
            $this->registeredRoute[$method] = array_merge($this->registeredRoute[$method], $groupRoutes[$method] ?? []);
        }
        $this->routeMiddleWare = array_merge($this->routeMiddleWare, $groupMiddleware);
    }


    public function resolve(IRequest &$request, IResponse $response, string $method, string $url) {

        $methodRoutes = $this->registeredRoute[$method] ?? null;
            
        if($methodRoutes == null){
            throw new HttpMethodNotAllowedException("Method not supported", 405);
        }

        [$routeCallable, $routeMiddleware] = $this->getRouteAndMiddleware($url, $method);
        $request->setPathParams($this->routeMatcher->getPathParams());
        
        foreach($routeMiddleware as $key => $middleware){
            $middleware->handle($request, $response);
        }
        call_user_func($routeCallable, $request, $response);
    }

    public function getRouteAndMiddleware(string $url, $method): array {
        $routeMap = $this->registeredRoute[$method];
        if (!$routeMap) {
            throw new HttpMethodNotAllowedException("Method not supported", 405);
        }

        $callback = $routeMap[$url];
        if ($callback) {
            $middleware = $this->routeMiddleWare[$url.':'.$method] ?? [];
            return [$callback, $middleware];
        }

        foreach ($this->registeredRoute[$method] as $routes => $callback) {
            $result = $this->routeMatcher->match($routes, $url);
            if($result){
                $middleware = $this->routeMiddleWare[$routes.':'.$method] ?? [];
                return [$callback, $middleware];
            }
        }
        throw new ResourceNotFound("404 Not Found");
    }

    public function getAllRoutes(): array {
        return $this->registeredRoute;
    }
}