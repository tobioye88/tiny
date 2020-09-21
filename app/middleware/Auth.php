<?php

namespace app\middleware;


use tiny\libs\JWT;
use tiny\exceptions\HttpUnauthorizedException;
use tiny\interfaces\IMiddleware;
use tiny\interfaces\IRequest;
use tiny\interfaces\IResponse;

class Auth implements IMiddleware {
    public function handle(IRequest &$req, IResponse &$res){
        $token = $req->getSession('token');

        if(!isset($token) || !JWT::verify($token, JWT_SECRET)){
            if($req->acceptJson()){
                throw new HttpUnauthorizedException("Unauthorized Request");
                exit;
            }

            $req->destroySession("token");
            $res->redirect('/login');
        }
    }
}