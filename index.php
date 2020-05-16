<?php

spl_autoload_register(function ($fullClassName) {
    $fullClassName = preg_replace("/\\\\/", "/", $fullClassName);
    $fullClassName = preg_replace("/Tiny/i", "src", $fullClassName);
	require $fullClassName.'.php';
});

use Tiny\Libs\App;
use Tiny\Interfaces\IRequest;
use Tiny\Interfaces\IResponse;

$app = new App;

$app->get('/', function(IRequest $req, IResponse $res){
    $res->json(["GREETINGS" => "/"]);
});

$app->get('/api', function(IRequest $req, IResponse $res){
    $res->json(["GREETINGS" => "/api"]);
});

$app->put('/api', function(IRequest $req, IResponse $res){
    $res->json(["Hello" => 1]);
});


$app->start();
