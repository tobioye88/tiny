<?php
namespace Tiny\Libs;

use Tiny\Interfaces\IHttpAllowedMethods;
use Tiny\Interfaces\IRouteGroup;
use Tiny\Libs\HttpAllowedMethods;

class RouteGroup extends HttpAllowedMethods implements IHttpAllowedMethods, IRouteGroup {
    protected array $routeMiddleWare = [];
    protected array $registeredRoute = [
        "GET" => [],
        "POST" => [],
        "PUT" => [],
        "PATCH" => [],
        "DELETE" => [],
        "OPTION" => [],
    ];

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

