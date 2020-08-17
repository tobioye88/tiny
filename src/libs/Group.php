<?php
namespace tiny\libs;

class Group extends AbstractHttpMethods {

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

    public function getMiddleware(String $prefix, array $middleware =[])
    {
        $newRouteMiddleware = [];
        foreach ($this->routeMiddleWare as $previousRoute => $callbackArray) {
            $newKey = trim($prefix, "/") . "/" . trim($previousRoute, "/");
            $newKey = trim($newKey, '/');
            $newRouteMiddleware[$newKey] = array_merge($middleware, $callbackArray);
        }
        unset($this->register);
        return $newRouteMiddleware;
    }
}

