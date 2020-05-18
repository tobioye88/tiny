<?php

spl_autoload_register(function ($fullClassName) {
    $fullClassName = preg_replace("/\\\\/", "/", $fullClassName);
    $fullClassName = preg_replace("/Tiny/i", "src", $fullClassName);
	require $fullClassName.'.php';
});

use Tiny\Interfaces\IHttpAllowedMethods;
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

// $app->get('/api/v1/admin', function(IRequest $req, IResponse $res){
//     $res->json(["authorized" => "User"]);
// }, [$authMiddleware]);

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

$app->group('api/v1/', function(IHttpAllowedMethods $group) use ($authMiddleware){

    $group->get('/admin', function(IRequest $req, IResponse $res){
        $res->json(['admin'=>'Inner route']);
    }, [$authMiddleware]);

    $group->get('/admin/john', function(IRequest $req, IResponse $res){
        $res->json(['admin'=>'unprotected Inner route']);
    });
    

});


$app->start();
