<?php
//global config
require "./src/config.php";

//global functions
require "./src/functions.php";

require __DIR__ . '/vendor/autoload.php';

//get sample routes
$route = require "./src/Routes/route.php";

use Tiny\Libs\App;

$app = new App;

$route($app);

$app->start();
