<?php

spl_autoload_register(function ($fullClassName) {
    $fullClassName = preg_replace("/\\\\/", "/", $fullClassName);
    $fullClassName = preg_replace("/Tiny/i", "src", $fullClassName);
	require $fullClassName . '.php';
});

//global config
require "./app/config.php";

//global functions
require "./app/functions.php";

//get sample routes
$route = require "./app/routes/route.php";

use tiny\libs\App;

$app = new App;

$route($app);


$app->start();
