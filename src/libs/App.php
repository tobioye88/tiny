<?php

namespace Tiny\Libs;

/**
 * $app->get('/', function(req, res){}, middleware)
 */
class App {

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
        $req = new Request;
        $res = new Response;

        $url = $req->getUrl();
        $method = $req->getMethod();
        $registeredMethod = $this->register[$method];
        // echo count($registeredMethod);
        if($registeredMethod == null){
            //Method not supported
            return;
        }
        if($registeredMethod[$url]){ 
            $registeredMethod[$url]($req, $res);
        }else{
            //TODO: 404 middleware
            http_response_code(404);
            echo "404 Not Found";
        }

        //when ever a request comes in
        //look to find handler
        //if handler not found return 404 status
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

    public function options(String $route, $callback, $middleware = null){}
    public function patch(String $route, $callback, $middleware = null){}

}