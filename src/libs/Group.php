<?php
namespace Tiny\Libs;

class Group extends AbstractHttpMethods {

    public function getRoutes(string $prefix)
    {
        foreach ($this->registeredRoute as $method => $routesArray) {
            $newMethod[$method] = [];
            foreach ($routesArray as $routeSuffix => $callback) {
                $newKey = trim($prefix, "/") . "/" . trim($routeSuffix, "/");
                $newKey = trim($newKey, '/');
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
            $newKey = trim($prefix, "/") . "/" . trim($routeSuffix, "/");
            $newKey = trim($newKey, '/');
            $newKey = preg_replace('(/:)', ':', $newKey);
            $newRouteMiddleware[$newKey] = array_merge($middleware, $callbackArray);
        }
        unset($this->register);
        return $newRouteMiddleware;
    }
}

