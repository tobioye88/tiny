<?php

use tiny\interfaces\IHttpAllowedMethods;
use tiny\interfaces\IRequest;
use tiny\interfaces\IResponse;
use app\middleware\Auth;

$authMiddleware = new Auth();

return function ($app) use ($authMiddleware) {


    $app->get('/', function (IRequest $req, IResponse $res) {
        $res->json(["GREETINGS" => "/"]);
    });

    $app->get('/home', function (IRequest $req, IResponse $res) {
        $message = "Hello World";
        $res->view('index.php', compact('message'));
    });

    $app->any('/api/{name}', function (IRequest $req, IResponse $res) {
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
