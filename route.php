<?php

use Tiny\Interfaces\IHttpAllowedMethods;
use Tiny\Interfaces\IRequest;
use Tiny\Interfaces\IResponse;
use Tiny\middlewares\Auth;

$authMiddleware = new Auth();

return function ($app) use ($authMiddleware) {


    $app->get('/', function (IRequest $req, IResponse $res) {
        $res->json(["GREETINGS" => "/"]);
    });

    $app->get('/api/{name}', function (IRequest $req, IResponse $res) {
        $res->json(["GREETINGS" => $req->getPathParam('name')]);
    });

    $app->get('/api/{name}/world', function (IRequest $req, IResponse $res) {
        $res->json(["user" => ['username' => $req->getPathParam('name')]]);
    });

    $app->put('/api/admin', function (IRequest $req, IResponse $res) {
        $res->json(["res" => trim("/api/", '/'), "body"=> $req->body]);
    }, [$authMiddleware]);

    $app->delete('/api/admin', function (IRequest $req, IResponse $res) {
        $res->json(["res" => trim("/api/", '/'), "body"=> $req->body]);
    });

    $app->get('/home', function (IRequest $req, IResponse $res) {
        $res->view('index.php', "Hello World");
    });

    $app->post('/api/post', function (IRequest $req, IResponse $res) {
        $res->json($req->body);
    });

    $app->group('api/v1/', function (IHttpAllowedMethods $group) use ($authMiddleware) {

        $group->get('/admin', function (IRequest $req, IResponse $res) {
            $res->json(['admin' => 'Inner route']);
        }, [$authMiddleware]);

        $group->get('/admin/{john}', function (IRequest $req, IResponse $res) {
            $res->json(['admin' => 'unprotected Inner route for ' . $req->getPathParam('john')]);
        });

        $group->post('/admin', function (IRequest $req, IResponse $res) {
            $res->json(['admin' => 'unprotected Post Inner route']);
        });
    });
};
