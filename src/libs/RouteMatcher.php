<?php

namespace tiny\libs;

use Exception;

class RouteMatcher {
    private static string $PATH_NAME_PATTERN = "/\{(\w+)\}+?/"; // used to match path variables api/{name}
//    private static string $PATH_NAME_PATTERN = "/\{(\w|\d|\s)+\}+?/";
//    private static string $PATH_VARIABLE_PATTERN = "([\w\@\S\s\.]+)";
    private static string $PATH_VARIABLE_PATTERN = "([\w@\.\-\s\_]+)";
    public array $pathParams = [];

    // private function __construct(){}

    public function match(String $appRoute, String $httpRoute): bool {
        // does this route match 
        if(self::routeEquals($appRoute, $httpRoute)){
            return true;
        }
        if(self::routeContainsPathParams($appRoute) && self::routeWithPathParamsEquals($appRoute, $httpRoute)){
            return true;
        }
        return false;
    }

    public function routeWithPathParamsEquals(String $appRoute, String $httpRoute): bool {
        $appRoute = trim($appRoute, "/");
        $httpRoute = trim($httpRoute, "/");
        
        preg_match_all(self::$PATH_NAME_PATTERN, $appRoute, $keys);

        $appRoute = preg_replace(self::$PATH_NAME_PATTERN, self::$PATH_VARIABLE_PATTERN, trim($appRoute, "/"));
        $pattern = "(".$appRoute.")";

        $result = preg_match($pattern, urldecode($httpRoute), $matches);
        array_shift($matches);
        $matches = array_map(fn($match) => $match, $matches);
        $this->setPathParams($keys, $matches);

        return (!!$result && substr_count($httpRoute, '/') == substr_count($appRoute, '/'));
    }

    public function routeEquals(String $appRoute, String $httpRoute): bool {
        return trim($appRoute, "/") == trim($httpRoute, "/");
    }

    public function routeContainsPathParams(String $appRoute): bool {
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

// $matcher = new RouteMatcher();
// echo $matcher->match("/api/{one}/two/{two}", "/api/one-1234/two/something@Different.com")? "Match\n": "No match\n";
// print_r($matcher->pathParams);
