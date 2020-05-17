<?php

namespace Tiny\Interfaces;

interface IMiddleware {

    public function handle(IRequest &$req, IResponse &$res);

}