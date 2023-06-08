<?php
//global config
require "./src/config.php";

//global functions
require "./src/functions.php";

require __DIR__ . '/vendor/autoload.php';

//get sample routes
$route = require "./src/Routes/route.php";

use Tiny\Libs\App;
use Tiny\Libs\Request;
use Tiny\Libs\Response;
use Tiny\Libs\RouteMatcher;
use Tiny\Libs\RouteGroup;

$app = new App(
  new Request(), 
  new Response(), 
  new RouteMatcher(), 
  new RouteGroup()
);

$route($app);

$app->start();
