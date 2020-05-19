<?php

spl_autoload_register(function ($fullClassName) {
    $fullClassName = preg_replace("/\\\\/", "/", $fullClassName);
    $fullClassName = preg_replace("/Tiny/i", "src", $fullClassName);
	require $fullClassName.'.php';
});

$route = require "./route.php";

use Tiny\Libs\App;

$app = new App;

$route($app);


$app->start();
