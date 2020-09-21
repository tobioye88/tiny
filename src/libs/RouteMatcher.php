<?php

namespace tiny\libs;

use Exception;

class RouteMatcher {
    private static string $PATH_NAME_PATTERN = "/\{(\w+)\}+?/";
    private static string $PATH_VARIABLE_PATTERN = "([\w@\.\-\s\_]+)";
    public array $pathParams = [];

    public function match(string $appRoute, string $httpRoute): bool {
        // try direct match
        if($this->routeEquals($appRoute, $httpRoute)){
            return true;
        }
        if($this->routeContainsPathParams($appRoute) && $this->routeWithPathParamsEquals($appRoute, $httpRoute)){
            return true;
        }
        return false;
    }

    public function getMiddleware(string $appRout): array {
        return [];
    }

    public function routeWithPathParamsEquals(string $appRoute, string $httpRoute): bool {
        $appRoute = trim($appRoute, "/");
        $httpRoute = trim($httpRoute, "/");
        
        preg_match_all(self::$PATH_NAME_PATTERN, $appRoute, $keys);

        $appRoute = preg_replace(self::$PATH_NAME_PATTERN, self::$PATH_VARIABLE_PATTERN, trim($appRoute, "/"));
        $pattern = "(^".$appRoute."$)";

        $result = preg_match($pattern, urldecode($httpRoute), $matches);
        array_shift($matches);
        $matches = array_map(fn($match) => $match, $matches);
        $this->setPathParams($keys, $matches);

        return !!$result;
    }

    public function routeEquals(string $appRoute, string $httpRoute): bool {
        return trim($appRoute, "/") == trim($httpRoute, "/");
    }

    public function routeContainsPathParams(string $appRoute): bool {
        return preg_match(self::$PATH_NAME_PATTERN, $appRoute);
    }

    public function setPathParams(array $keys, array $matches): void {
        if(count($matches) > 0){
            foreach($matches as $key => $match){
                $this->pathParams[$keys[1][$key]] = $match;
            }
        }
    }
}
