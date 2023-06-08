<?php
namespace Tiny\Interfaces;

interface IRouteGroup {
  public function getRoutes(string $prefix);
  public function getMiddleware(string $prefix, array $middleware =[]);
}