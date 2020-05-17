<?php

spl_autoload_register(function ($fullClassName) {
    $fullClassName = preg_replace("/\\\\/", "/", $fullClassName);
    $fullClassName = preg_replace("/Tiny/i", "src", $fullClassName);
	require $fullClassName.'.php';
});

use Tiny\exceptions\HttpNotImplementedException;
use Tiny\exceptions\HttpUnauthorizedException;
use Tiny\Interfaces\IMiddleware;
use Tiny\Libs\App;
use Tiny\Interfaces\IRequest;
use Tiny\Interfaces\IResponse;
use Tiny\middlewares\Auth;

$app = new App;


$authMiddleware = new Auth();
// $app->addMiddleWare($authMiddleware);

$app->get('/', function(IRequest $req, IResponse $res){
    $res->json(["GREETINGS" => "/"]);
});

$app->get('/api/{name}', function(IRequest $req, IResponse $res){
    $res->json(["GREETINGS" => $req->getPathParam('name')]);
});

$app->get('/api/{name}/world', function(IRequest $req, IResponse $res){
    $res->json(["user" => [ 'username'=> $req->getPathParam('name')] ]);
});

$app->put('/api/admin', function(IRequest $req, IResponse $res){
    $res->json(["res" => trim("/api/", '/')]);
}, [$authMiddleware]);

$app->get('/home', function(IRequest $req, IResponse $res){
    $res->view('/view/index.php', "Hello World");
});

$app->post('/api/post', function(IRequest $req, IResponse $res){
    // $req->body
    $res->json($req->body);
});


$app->start();
