<?php

namespace tiny\interfaces;

interface IResponse {

    public function status(int $code);

    public function json($body, $statusCode=200);

    public function view($path, array $extra=[]);

    public function file($path);

    public function setCookies(String $name, String $value);

    public function redirect(String $path);

}