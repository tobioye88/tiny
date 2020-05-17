<?php

namespace Tiny\middlewares;

use Tiny\exceptions\HttpUnauthorizedException;
use Tiny\Interfaces\IMiddleware;
use Tiny\Interfaces\IRequest;
use Tiny\Interfaces\IResponse;

class Auth implements IMiddleware {
    public function handle(IRequest &$req, IResponse &$res){
        $token = $req->getHeader('token');
        if(!isset($token)){
            throw new HttpUnauthorizedException("Unauthorized Request");
        }
    }
}