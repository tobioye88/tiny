<?php

namespace Tiny\Libs;

use Exception;
use Tiny\exceptions\HttpBadRequestException;
use Tiny\exceptions\HttpErrorHandler;
use Tiny\exceptions\HttpMethodNotAllowedException;
use Tiny\exceptions\HttpNotImplementedException;
use Tiny\exceptions\ResourceNotFound;
use Tiny\Interfaces\IHttpAllowedMethods;
use Tiny\Interfaces\IRequest;
use Tiny\Interfaces\IMiddleware;

/**
 * $app->get('/', function(req, res){}, middleware)
 * $app->group('/', function($group){
 *      $group->get('', function($req, $res){}, [$middlewares])
 * }, [middlewares]); 
 */
class App implements IHttpAllowedMethods {
    public const BASE_PATH = __DIR__ . "/../..";
    private $callback;
    private $globalMiddleWare = [];
    private $routMiddleWare = [];

    private $register = [
        "GET" => [],
        "POST" => [],
        "PUT" => [],
        "DELETE" => [],
    ];

    public function __construct()
    {
        set_exception_handler(function($exception) {
            HttpErrorHandler::handle($exception);
        });
    }

    public function addMiddleWare(IMiddleware $middleware){
        $this->globalMiddleWare[] = $middleware;
    }
    
    public function run(){
        $this->start();
    }

    public function start(){
        try{
            $req = new Request;
            $res = new Response;
            
            foreach($this->globalMiddleWare as $key => $middleware){
                $middleware->handle($req, $res);
            }
    
            $url = $req->getUrl();
            $method = $req->getMethod();
            $registeredMethod = $this->register[$method];
            
            if($registeredMethod == null){
                throw new HttpMethodNotAllowedException("Method not supported");
            }

            if($this->hasRoute($url, $method, $req)){
                $routeMiddleware = $this->routMiddleWare[trim($url, "/")] ?? [];
                foreach($routeMiddleware as $key => $middleware){
                    $middleware->handle($req, $res);
                }
                call_user_func($this->callback, $req, $res);
            }else{
                throw new ResourceNotFound("404 Not Found");
            }
        }catch (Exception $e){
            HttpErrorHandler::handle($e);
        }
    }

    public function group(String $prefix, callable $callback, array $middlewares = []){
        $group = new Group();
        $callback($group);
        $groupRoutes = $group->getRoutes($prefix);
        $groupMiddleware = $group->getMiddlewares($prefix, $middlewares);
        $this->register['GET'] = array_merge($this->register['GET'], $groupRoutes['GET']);
        $this->register['POST'] = array_merge($this->register['POST'], $groupRoutes['POST']);
        $this->register['PUT'] = array_merge($this->register['PUT'], $groupRoutes['PUT']);
        $this->register['DELETE'] = array_merge($this->register['DELETE'], $groupRoutes['DELETE']);
        $this->routMiddleWare = array_merge($this->routMiddleWare, $groupMiddleware);
    }

    public function get(String $route, callable $callback, array $middlewares = []){
        $this->routMiddleWare[trim($route, "/")] = $middlewares;
        $this->register['GET'][trim($route, "/")] = $callback;
    }

    public function post(String $route, callable $callback, array $middlewares = []){
        $this->routMiddleWare[trim($route, "/")] = $middlewares;
        $this->register['POST'][trim($route, "/")] = $callback; 
    }

    public function put(String $route, callable $callback, array $middlewares = []){
        $this->routMiddleWare[trim($route, "/")] = $middlewares;
        $this->register['PUT'][trim($route, "/")] = $callback; 
    }

    public function delete(String $route, callable $callback, array $middlewares = []){
        $this->routMiddleWare[trim($route, "/")] = $middlewares;
        $this->register['DELETE'][trim($route, "/")] = $callback; 
    }

    public function any(String $route, callable $callback, array $middlewares = []){
        $this->routMiddleWare[trim($route, "/")] = $middlewares;
        throw new HttpNotImplementedException("Method not Implemented"); //TODO
    }

    public function hasRoute(String $url, $method, IRequest &$req): bool {
        $matcher = new RouteMatcher();
        // print_r($this->register[$method]);
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

    public function options(String $route, callable $callback, array $middlewares = []){
        $this->routMiddleWare[trim($route, "/")] = $middlewares;
    }
    
    public function patch(String $route, callable $callback, array $middlewares = []){
        $this->routMiddleWare[trim($route, "/")] = $middlewares;
    }


    public function cors(array $allowed_domains = []){
        if (isset($_SERVER['HTTP_ORIGIN']) && !in_array($_SERVER['HTTP_ORIGIN'], $allowed_domains)) {
            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
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