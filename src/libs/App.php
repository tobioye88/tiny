<?php

namespace Tiny\Libs;

use Exception;
use Tiny\exceptions\FileNotFoundException;
use Tiny\exceptions\MethodNotSupported;

/**
 * $app->get('/', function(req, res){}, middleware)
 */
class App {
    private const BASE_PATH = __DIR__ . "/../..";

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
                throw new MethodNotSupported("Method not supported");
            }

            if($registeredMethod[$url]){ 
                $registeredMethod[$url]($req, $res);
            }else{
                throw new FileNotFoundException("404 Not Found");
            }
            
            //when ever a request comes in
            //look to find handler
            //if handler not found return 404 status
        }catch (Exception $e){
            //TODO: handle Exception Response
            //TODO: 404 middleware
            //TODO: Method not supported
            //http_response_code(404);
        }
    }

    public function group(String $route, $callback, $middleware = null){

    }

    public function get(String $route, $callback, $middleware = null){
        $this->register['GET'][$route] = $callback;
        // echo "<pre>";
        // echo $route . PHP_EOL;
        // print_r($this->register);
    }
    public function post(String $route, $callback, $middleware = null){
        $this->register['POST'][$route] = $callback; 
        //images
        //formData
        //json
        //blob
    }
    public function put(String $route, $callback, $middleware = null){
        $this->register['PUT'][$route] = $callback; 
    }

    public function delete(String $route, $callback, $middleware = null){
        $this->register['DELETE'][$route] = $callback; 
    }

    public function any(String $route, $callback, $middleware = null){
        throw new Exception("Method not Implemented");
    }

    public function options(String $route, $callback, $middleware = null){}
    public function patch(String $route, $callback, $middleware = null){}

}