<?php

namespace Tiny\Libs;

use Exception;

class RouteMatcher {
    private static $KEY_PATTERN = "/\{(\w+)\}+?/";
    private static $REPLACE_PATTERN = "([\w@\.\-]+)";
    public $pathParams = [];

    // private function __construct(){}

    public function match(String $appRoute, String $httpRoute): bool {
        // does this route match 
        if(self::routeContainsPathParams($appRoute) && self::routeWithPathParamsEquals($appRoute, $httpRoute)){
            return true;
        }else if(self::routeEquals($appRoute, $httpRoute)){
            return true;
        }
        return false;
    }

    public function routeWithPathParamsEquals(String $appRoute, String $httpRoute): bool {
        $appRoute = trim($appRoute, "/");
        $httpRoute = trim($httpRoute, "/");

        preg_match_all(self::$KEY_PATTERN, $appRoute, $keys);
        // print_r($keys);


        $appRoute = preg_replace(self::$KEY_PATTERN, self::$REPLACE_PATTERN, trim($appRoute, "/"));
        $pattern = "(".$appRoute.")";

        $result = preg_match($pattern, $httpRoute, $matches);
        array_shift($matches);
        $this->setPathParams($keys, $matches);

        return (!!$result && substr_count($httpRoute, '/') == substr_count($appRoute, '/'));
    }

    public function routeEquals(String $appRoute, String $httpRoute): bool {
        return $appRoute == $httpRoute;
    }

    public function routeContainsPathParams(String $appRoute): bool {
        return preg_match(self::$KEY_PATTERN, $appRoute);
    }

    public function setPathParams(array $keys, array $matches): void {
        if(count($matches) > 0){
            foreach($matches as $key => $match){
                $this->pathParams[$keys[1][$key]] = $match;
            }
        }
    }

    public function matchCurrentRoute(): bool {
        //TODO: implement method
        throw new Exception("Method not implemented yet!");
    }
}

// $matcher = new RouteMatcher();
// echo $matcher->match("/api/{one}/two/{two}", "/api/one-1234/two/something@Different.com")? "Match\n": "No match\n";
// print_r($matcher->pathParams);
