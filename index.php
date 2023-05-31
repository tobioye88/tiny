<?php
//global config
require "./src/app/config.php";

//global functions
require "./src/app/functions.php";

require __DIR__ . '/vendor/autoload.php';

//get sample routes
$route = require "./src/App/Routes/route.php";

use Tiny\Libs\App;

$app = new App;

$route($app);

$app->start();
