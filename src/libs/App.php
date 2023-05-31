<?php

namespace Tiny\Libs;

use Exception;
use Tiny\Interfaces\IRequest;
use Tiny\Interfaces\IMiddleware;
use Tiny\Exceptions\HttpErrorHandler;
use Tiny\Exceptions\ResourceNotFound;
use Tiny\Exceptions\HttpBadRequestException;
use Tiny\Exceptions\HttpMethodNotAllowedException;


class App extends AbstractHttpMethods {
    public const VIEW_PATH = __DIR__ . "/../../src/App/View/";
    public const BASE_PATH = __DIR__ . "/../../";
    public static string $url;
    
    private static string $defaultErrorView = "";
    private static string $errorViewPath = "";
    private $callback;
    private array $globalMiddleWare = [];
    private array $currentMiddleware = [];

    public function __construct()
    {
        session_start();
        date_default_timezone_set(DEFAULT_TIME_ZONE);
        set_exception_handler(function($exception) {
            error_log($exception);
            HttpErrorHandler::handle($exception);
            return true;
        });
        set_error_handler(function($exception) {
            error_log($exception);
            HttpErrorHandler::handle($exception);
            return true;
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
    
            App::$url = $url = $req->getUrl();
            $method = $req->getMethod();
            $registeredMethod = $this->registeredRoute[$method] ?? null;
            
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

        foreach ($this->registeredRoute as $method => $value){
            $this->registeredRoute[$method] = array_merge($this->registeredRoute[$method], $groupRoutes[$method]);
        }
        $this->routeMiddleWare = array_merge($this->routeMiddleWare, $groupMiddleware);
    }

    public function hasRoute(string $url, $method, IRequest &$req): bool {
        $matcher = new RouteMatcher();
        foreach ($this->registeredRoute[$method] as $innerRoute => $callback) {
            $result = $matcher->match($innerRoute, $url);
            if($result){
                $this->callback = $callback;
                $this->currentMiddleware = $this->routeMiddleWare[$innerRoute.':'.$method] ?? [];
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

    private static function defaultErrorView($errorMessage = "Unknown Error Occurred", $trace = [], $file = '', $line = ''): void {
        echo '<!DOCTYPE html>
        <html lang="en">
        
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <base href="/">
            <style>
            .ty-header {
                padding: 10px;
                background-color: #b2e9ff;
            }
            .ty-pb-3 {
                padding-bottom: 8px;
            }
            </style>
            <title>Error</title>
        </head>
        
        <body class="bg-light">
            <div class="ty-header">Tiny: Error</div>
            <div class="container align-items-center">
                <div style="min-height: 100vh;">
                    <div class="jumbotron shadow-sm bg-white mt-5 text-center">
                        <div class="">
                            <h2>Something went wrong</h2>
                        </div>
                        <div class="ty-pb-3">Error message:<br>'. $errorMessage .'</div>
                        <div class="ty-pb-3">Trace:<br>'. implode(',', $trace) .'</div>
                        <div class="ty-pb-3">File:<br>'. $file .'</div>
                        <div class="ty-pb-3">Line:<br>'. $line .'</div>
                        <div class="lead">Go <a href="/">home</a></div>
                    </div>
                </div>
            </div>
        </body>
        </html>';
    }

    public static function setErrorViewPath(string $errorViewPath ): void {
        self::$errorViewPath = $errorViewPath;
    }

    public static function renderErrorView(string $errorMessage, $trace = [], $file = '', $line = ''){
        if(empty(App::$errorViewPath)){
            return self::defaultErrorView($errorMessage, $trace, $file, $line);
        }

        if(!empty(App::$errorViewPath) && is_file(App::$defaultErrorView)){
            include App::$defaultErrorView;
        }else {
            return self::defaultErrorView("Path to custom error page was not found");
        }
    }

    public function addDefaultErrorPage(string $path): void {
        self::$defaultErrorView = $path;
    }

    public function getAllRoutes(): array {
        return $this->registeredRoute;
    }

}