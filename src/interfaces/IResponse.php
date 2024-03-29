<?php

namespace Tiny\Interfaces;

interface IResponse {

    public function status(int $code);

    public function json(array $body, $statusCode=200);
    
    public function write(string $body);
    
    public function text(string $body);

    public function view($_path, array $extra=[]);

    public function file($path);

    public function setCookies(string $name, string $value);

    public function redirect(string $path, $queryParams = []);

    public function setHeader(string $key, string $value): void;

}