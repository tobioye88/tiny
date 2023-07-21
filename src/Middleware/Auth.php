<?php

namespace Tiny\Middleware;


use Tiny\Libs\JWT;
use Tiny\Exceptions\HttpUnauthorizedException;
use Tiny\Interfaces\IMiddleware;
use Tiny\Interfaces\IRequest;
use Tiny\Interfaces\IResponse;

class Auth implements IMiddleware {
    public function handle(IRequest &$req, IResponse &$res){
        $token = $req->getHeader('Authorization');

        if(!isset($token)){ // || !JWT::verify($token, JWT_SECRET)){
            if($req->acceptJson()){
                throw new HttpUnauthorizedException("Unauthorized Request");
            }

            $req->destroySession("token");
            $res->redirect('/login');
        }
    }
}