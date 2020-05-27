<?php

namespace tiny\interfaces;

interface IMiddleware {

    public function handle(IRequest &$req, IResponse &$res);

}