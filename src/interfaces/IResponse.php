<?php

namespace Tiny\Interfaces;

interface IResponse {

    public function status(int $code);

    public function json($body, $statusCode=200);

    public function view($path, $extra);

    public function file($path);

}