<?php

namespace Tiny\Interfaces;

interface IMiddleware {

    public function before(IRequest $req, IResponse $res, IMiddleWare $iMiddleWare);

    public function after(IRequest $req, IResponse $res, IMiddleWare $iMiddleWare);

}