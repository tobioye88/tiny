<?php

namespace Tiny\Libs;;

use Tiny\Exceptions\HttpMethodNotAllowedException;
use Tiny\Exceptions\ResourceNotFound;
use Tiny\Interfaces\IHttpAllowedMethods;
use Tiny\Interfaces\IRequest;
use Tiny\Interfaces\IResponse;
use Tiny\Interfaces\IRouteGroup;
use Tiny\Interfaces\IRouteMatcher;
use Tiny\Libs\HttpAllowedMethods;

class Router extends HttpAllowedMethods implements IHttpAllowedMethods
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
        private IRouteMatcher $routeMatcher,
        private Container $container,
        ) {}

    

    public function group(string $prefix, callable|array $callback, array $middleware = []){;
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

        [$action, $routeMiddleware] = $this->getRouteAndMiddleware($url, $method);
        $request->setPathParams($this->routeMatcher->getPathParams());
        
        foreach($routeMiddleware as $key => $middleware){
            $middleware->handle($request, $response);
        }
        
        if (is_callable($action)) {
            return call_user_func($action, $request, $response);
        }

        [$class, $method] = $action;

        if (class_exists($class)) {
            $class = $this->container->get($class);

            if (method_exists($class, $method)) {
                $result = call_user_func_array([$class, $method], []);
                if (is_array($result)) {
                    $response->json($result);
                } else if (is_string($result)) {
                    $response->text($result);
                }
                return;
            }
        }

        throw new HttpMethodNotAllowedException("Route not found", 404);
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