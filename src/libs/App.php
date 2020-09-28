<?php

namespace tiny\libs;

use Exception;
use tiny\interfaces\IRequest;
use tiny\interfaces\IMiddleware;
use tiny\exceptions\HttpErrorHandler;
use tiny\exceptions\ResourceNotFound;
use tiny\exceptions\HttpBadRequestException;
use tiny\exceptions\HttpMethodNotAllowedException;


class App extends AbstractHttpMethods {
    public const VIEW_PATH = __DIR__ . "/../../app/view/";
    public const BASE_PATH = __DIR__ . "/../../";
    
    private static string $defaultErrorView = "";
    private $callback;
    private array $globalMiddleWare = [];
    private array $currentMiddleware = [];

    public function __construct()
    {
        session_start();
        date_default_timezone_set(DEFAULT_TIME_ZONE);
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
                $routeMiddleware = $this->currentMiddleware;
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

    public function group(string $prefix, callable $callback, array $middleware = []){
        $group = new Group();
        $callback($group);
        $groupRoutes = $group->getRoutes($prefix);
        $groupMiddleware = $group->getMiddleware($prefix, $middleware);

        foreach ($this->register as $key => $value){
            $this->register[$key] = array_merge($this->register[$key], $groupRoutes[$key]);
        }
        $this->routeMiddleWare = array_merge($this->routeMiddleWare, $groupMiddleware);
    }

    public function hasRoute(string $url, $method, IRequest &$req): bool {
        $matcher = new RouteMatcher();
        foreach ($this->register[$method] as $key => $value) {
            $result = $matcher->match($key, $url);
            if($result){
                $this->callback = $value;
                $this->currentMiddleware = $this->routeMiddleWare[$key];
                $req->setPathParams($matcher->pathParams);
                return $result;
            }
        }
        return false;
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

    public static function defaultErrorView($errorMessage = "Unknown Error Occurred"): void
    {
        echo '<!DOCTYPE html>
        <html lang="en">
        
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <base href="/">
            <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
            <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
            <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
            <title>Error</title>
        </head>
        
        <body class="bg-light">
            <div class="container align-items-center">
                <div style="min-height: 100vh;">
                    <div class="jumbotron shadow-sm bg-white mt-5 text-center">
                        <div class="">
                            <h1>Something went wrong</h1>
                        </div>
                        <div class="lead">'. $errorMessage.'</div>
                        <div class="lead">Go <a href="/">home</a></div>
                    </div>
                </div>
            </div>
        </body>
        
        </html>';
    }

    public static function errorView($errorMessage)
    {
        if(App::$defaultErrorView == null || App::$defaultErrorView == ""){
            return null;
        }

        if(is_file(App::$defaultErrorView)){
            include App::$defaultErrorView;
        }else {
            throw new ResourceNotFound("Path to custom error message was not found");
        }
    }

    public function addDefaultErrorPage(string $path): void
    {
        self::$defaultErrorView = $path;
    }

}