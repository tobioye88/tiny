<?php

namespace Tiny\Libs;

use Exception;
use Tiny\exceptions\HttpBadRequestException;
use Tiny\exceptions\HttpErrorHandler;
use Tiny\exceptions\HttpMethodNotAllowedException;
use Tiny\exceptions\HttpNotImplementedException;
use Tiny\exceptions\ResourceNotFound;
use Tiny\Interfaces\IRequest;

/**
 * $app->get('/', function(req, res){}, middleware)
 */
class App {
    public const BASE_PATH = __DIR__ . "/../..";
    private $callback;

    private $register = [
        "GET" => [],
        "POST" => [],
        "PUT" => [],
        "DELETE" => [],
    ];
    
    public function run(){
        $this->start();
    }

    public function start(){
        try{
            $req = new Request;
            $res = new Response;
    
            $url = $req->getUrl();
            $method = $req->getMethod();
            $registeredMethod = $this->register[$method];
            
            if($registeredMethod == null){
                throw new HttpMethodNotAllowedException("Method not supported");
            }

            if($this->hasRoute($url, $method, $req)){
                call_user_func($this->callback, $req, $res);
            }else{
                throw new ResourceNotFound("404 Not Found");
            }
        }catch (Exception $e){
            HttpErrorHandler::handle($e);
        }
    }

    public function group(String $route, callable $callback, $middleware = null){
        throw new HttpNotImplementedException("Method not Implemented"); //TODO
    }

    public function get(String $route, callable $callback, $middleware = null){
        $this->register['GET'][$route] = $callback;
    }

    public function post(String $route, callable $callback, $middleware = null){
        $this->register['POST'][$route] = $callback; 
    }

    public function put(String $route, callable $callback, $middleware = null){
        $this->register['PUT'][$route] = $callback; 
    }

    public function delete(String $route, callable $callback, $middleware = null){
        $this->register['DELETE'][$route] = $callback; 
    }

    public function any(String $route, callable $callback, $middleware = null){
        throw new HttpNotImplementedException("Method not Implemented"); //TODO
    }

    public function hasRoute(String $url, $method, IRequest &$req): bool {
        $matcher = new RouteMatcher();
        foreach ($this->register[$method] as $key => $value) {
            $result = $matcher->match($key, $url);
            if($result){
                $this->callback = $value;
                $req->setPathParams($matcher->pathParams);
                return $result;
            }
        }
        return false;
    }

    public function options(String $route, callable $callback, $middleware = null){}
    public function patch(String $route, callable $callback, $middleware = null){}


    public function cors(array $allowed_domains = []){
        if (isset($_SERVER['HTTP_ORIGIN']) && !in_array($_SERVER['HTTP_ORIGIN'], $allowed_domains)) {
            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');    // cache for 1 day
        }

        // Access-Control headers are received during OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            // may also be using PUT, PATCH, HEAD etc
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         
            
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
            
            exit(0);
        }

        throw new HttpBadRequestException("Origin not allowed");
    }

}