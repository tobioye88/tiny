<?php

namespace tiny\interfaces;

interface IResponse {

    public function status(int $code);

    public function json($body, $statusCode=200);

    public function view($_path, array $extra=[]);

    public function file($path);

    public function setCookies(String $name, String $value);

    public function redirect(String $path, $queryParams = []);

    public function setHeader(string $key, string $value): void;

}