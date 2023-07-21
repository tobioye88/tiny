<?php

use Tiny\Interfaces\IHttpAllowedMethods;
use Tiny\Interfaces\IRequest;
use Tiny\Interfaces\IResponse;
use Tiny\Libs\Router;
use Tiny\Libs\Validate;
use Tiny\Middleware\Auth;

$authMiddleware = new Auth();

return function (Router $router) use ($authMiddleware) {


    $router->get('/', function (IRequest $req, IResponse $res) {
        $query = $req->getQueryParams();
        $validate = new Validate($query, ['name' => ['string'=> true, 'required' => true ]]);
        $isValid = $validate->isValid();
        $status = $isValid? 200 : 400;
        $res->json([
            'response' => 'Hello, World!', 
            'validate' => $isValid, 
            'message' => $validate->errors(),
            'data' => $query,
        ], $status);
    });
    
    $router->get('/homepage', function (IRequest $req, IResponse $res) {
        $message = "Hello World";
        $res->view('index.php', compact('message'));
    });

    $router->any('/login', function (IRequest $req, IResponse $res) {
        $res->json(["login" => 'Please login here']);
    });

    $router->get('/api/{name}/world', function (IRequest $req, IResponse $res) {
        $res->json(["user" => ['username' => $req->getPathParam('name')]]);
    });

    $router->put('/api/admin', function (IRequest $req, IResponse $res) {
        $res->json(["res" => trim("/api/", '/'), "body"=> $req->getBody()]);
    }, [$authMiddleware]);

    $router->delete('/api/admin', function (IRequest $req, IResponse $res) {
        $res->json(["res" => trim("/api/", '/'), "body"=> $req->getBody()]);
    });

    $router->post('/api/query', function (IRequest $req, IResponse $res) {
        $res->json($req->getQueryParams());
    });

    $router->group('api/v1/', function (IHttpAllowedMethods $group) use ($authMiddleware) {

        $group->get('/', function (IRequest $req, IResponse $res) {
            $res->json(['hello' => 'Inner route']);
        });
        
        $group->get('/admin', function (IRequest $req, IResponse $res) {
            $res->json(['admin' => 'Inner route']);
        }, [$authMiddleware]);

    },); // [ $authMiddleware ]);

    $router->group('api/v2/{name}', function (IHttpAllowedMethods $group) use ($authMiddleware) {

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
