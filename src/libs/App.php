<?php

namespace Tiny\Libs;

use \Exception;
use Tiny\Exceptions\HttpBadRequestException;
use Tiny\Exceptions\HttpErrorHandler;
use Tiny\Interfaces\IMiddleware;
use Tiny\Interfaces\IRequest;
use Tiny\Interfaces\IResponse;
use Tiny\Libs\Router;


class App {
    public const VIEW_PATH = __DIR__ . "/../../src/Views/";
    public const BASE_PATH = __DIR__ . "/../../";
    public static string $url;
    
    private static string $defaultErrorView = "";
    private static string $errorViewPath = "";

    private array $globalMiddleWare = [];


    public function __construct(
        private IRequest $request, 
        private IResponse $response,
        private Router $router,
        )
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
            foreach($this->globalMiddleWare as $key => $middleware){
                $middleware->handle($this->request, $this->response);
            }
    
            App::$url = $url = $this->request->getUrl();
            $method = $this->request->getMethod();
            $this->router->resolve($this->request, $this->response, $method, $url);

        }catch (Exception $e){
            HttpErrorHandler::handle($e);
        }
    }

    // public function group(string $prefix, callable $callback, array $middleware = []){
    //     $callback($this->routeGroup);
    //     $groupRoutes = $this->routeGroup->getRoutes($prefix);
    //     $groupMiddleware = $this->routeGroup->getMiddleware($prefix, $middleware);

    //     foreach ($this->registeredRoute as $method => $value){
    //         $this->registeredRoute[$method] = array_merge($this->registeredRoute[$method], $groupRoutes[$method]);
    //     }
    //     $this->routeMiddleWare = array_merge($this->routeMiddleWare, $groupMiddleware);
    // }

    // public function getRouteAndMiddleware(string $url, $method): array {
    //     $routeMap = $this->registeredRoute[$method];
    //     if (!$routeMap) {
    //         throw new HttpMethodNotAllowedException("Method not supported", 405);
    //     }

    //     $callback = $routeMap[$url];
    //     if ($callback) {
    //         $middleware = $this->routeMiddleWare[$url.':'.$method] ?? [];
    //         return [$callback, $middleware];
    //     }

    //     foreach ($this->registeredRoute[$method] as $routes => $callback) {
    //         $result = $this->routeMatcher->match($routes, $url);
    //         if($result){
    //             $middleware = $this->routeMiddleWare[$routes.':'.$method] ?? [];
    //             return [$callback, $middleware];
    //         }
    //     }
    //     throw new ResourceNotFound("404 Not Found");
    // }


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

    private static function defaultErrorView(string $errorMessage = "Unknown Error Occurred", array $trace = [], string $file = '', string $line = ''): void {
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
                        <div class="ty-pb-3">Error message:<br><b>'. $errorMessage .'</b></div>
                        <div class="ty-pb-3">Trace:<br><b>'. implode('<br>', $trace) .'</b></div>
                        <div class="ty-pb-3">File:<br><b>'. $file .'</b></div>
                        <div class="ty-pb-3">Line:<br><b>'. $line .'</b></div>
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

    public static function renderErrorView(string $errorMessage, array $trace = [], string $file = '', string $line = ''){
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

    // public function getAllRoutes(): array {
    //     return $this->registeredRoute;
    // }

    public function getRouter(): Router {
        return $this->router;
    }

}