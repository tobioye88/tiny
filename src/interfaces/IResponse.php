<?php

namespace tiny\interfaces;

interface IResponse {

    public function status(int $code);

    public function json($body, $statusCode=200);

    public function view($_path, array $extra=[]);

    public function file($path);

    public function setCookies(string $name, string $value);

    public function redirect(string $path, $queryParams = []);

    public function setHeader(string $key, string $value): void;

}