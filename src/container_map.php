<?php

use Tiny\Interfaces\IRequest;
use Tiny\Interfaces\IResponse;
use Tiny\Interfaces\IRouteMatcher;
use Tiny\Interfaces\IRouteGroup;
use Tiny\Libs\Request;
use Tiny\Libs\Response;
use Tiny\Libs\RouteMatcher;
use Tiny\Libs\RouteGroup;

return [
  IRequest::class => Request::class,
  IResponse::class => Response::class,
  IRouteMatcher::class => RouteMatcher::class,
  IRouteGroup::class => RouteGroup::class,
];
