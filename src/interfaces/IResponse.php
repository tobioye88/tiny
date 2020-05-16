<?php

namespace Tiny\Interfaces;

interface IResponse {

    public function json($body, $statusCode=200);

}