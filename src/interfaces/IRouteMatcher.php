<?php

namespace Tiny\Interfaces;

interface IRouteMatcher {
  public function match(string $appRoute, string $httpRoute): bool;
  public function getMiddleware(string $appRout): array;
  public function routeWithPathParamsEquals(string $appRoute, string $httpRoute): bool;
  public function routeEquals(string $appRoute, string $httpRoute): bool;
  public function routeContainsPathParams(string $appRoute): bool;
  public function setPathParams(array $keys, array $matches): void; 
  public function getPathParams(): array; 
  public function getPathParam(string $key): ?string;
}