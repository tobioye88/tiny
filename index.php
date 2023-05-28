<?php

spl_autoload_register(function ($fullClassName) {
    // echo $fullClassName . "<br>";
    $fullClassName = preg_replace("/\\\\/", "/", $fullClassName);
    $fullClassName = preg_replace("/Tiny/i", "src", $fullClassName);
    // echo $fullClassName; die();
	require $fullClassName . '.php';
});

//global config
require "./src/app/config.php";

//global functions
require "./src/app/functions.php";

//get sample routes
$route = require "./src/app/routes/route.php";

use tiny\libs\App;

$app = new App;

$route($app);


$app->start();
