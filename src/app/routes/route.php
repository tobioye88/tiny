<?php

use tiny\interfaces\IHttpAllowedMethods;
use tiny\interfaces\IRequest;
use tiny\interfaces\IResponse;
use src\app\middleware\Auth;
use tiny\libs\App;
use tiny\libs\Email;

$authMiddleware = new Auth();

return function (App $app) use ($authMiddleware) {


    $app->get('/', function (IRequest $req, IResponse $res) {
        $res->json(['response' => 'Hello, World!']);
    });
    
    $app->get('/homepage', function (IRequest $req, IResponse $res) {
        $message = "Hello World";
        $res->view('index.php', compact('message'));
    });

    $app->any('/login', function (IRequest $req, IResponse $res) {
        $res->json(["login" => 'Please login here']);
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

    $app->post('/api/query', function (IRequest $req, IResponse $res) {
        $res->json($req->getQueryParams());
    });

    $app->group('api/v1/', function (IHttpAllowedMethods $group) use ($authMiddleware) {

        $group->get('/', function (IRequest $req, IResponse $res) {
            $res->json(['hello' => 'Inner route']);
        });
        
        $group->get('/admin', function (IRequest $req, IResponse $res) {
            $res->json(['admin' => 'Inner route']);
        }, [$authMiddleware]);

    },); // [ $authMiddleware ]);

    $app->group('api/v2/{name}', function (IHttpAllowedMethods $group) use ($authMiddleware) {

        $group->get('/special', function (IRequest $req, IResponse $res) {
            $res->json([
                'special' => 'Inner route',
                'name' => $req->getPathParam('name'),
            ]);
        });

        $group->get('', function (IRequest $req, IResponse $res) {
            $res->json([
                'special' => 'Inner route',
            ]);
        });

    }, [ $authMiddleware ]);
};
