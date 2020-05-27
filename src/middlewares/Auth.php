<?php

namespace tiny\middlewares;

use app\libs\Auth as LibsAuth;
use app\libs\JWT;
use tiny\exceptions\HttpUnauthorizedException;
use tiny\interfaces\IMiddleware;
use tiny\interfaces\IRequest;
use tiny\interfaces\IResponse;

class Auth implements IMiddleware {
    public function handle(IRequest &$req, IResponse &$res){
        $token = $req->getSession('token');

        if(!isset($token) || !JWT::verify($token, JWT_SECRET) || !LibsAuth::user('account_type') == "BASIC"){
            // throw new HttpUnauthorizedException("Unauthorized Request");
            $req->destroySession("token");
            $res->redirect('/login');
        }
    }
}