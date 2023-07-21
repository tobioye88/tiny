<?php
//global config
require "./src/config.php";

//global functions
require "./src/functions.php";

require __DIR__ . '/vendor/autoload.php';

//get sample routes
$route = require "./src/Routes/route.php";

use Tiny\Libs\App;
use Tiny\Libs\Container;



$container = new Container();

$app = $container->get(App::class);

$route($app->getRouter());
$app->start();
