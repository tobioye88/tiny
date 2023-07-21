<?php
namespace Tiny\Libs;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionUnionType;
use Tiny\Exceptions\ContainerException;

class Container implements ContainerInterface {
  private array $entries = [];
  private array $cachedEntries = [];

  function __construct() {
    $this->set(Container::class, fn() => $this);
    $containerMap = require __DIR__ . '/../container_map.php';
    if (!is_array($containerMap)) {
      return;
    }
    foreach ($containerMap as $key => $value) {
      $this->set($key, $value);
    }
  }

  public function get(string $id) {
    if ($this->inCache($id)) {
      return $this->cachedEntries[$id];
    }
    if ($this->has($id)) {
      $entry = $this->entries[$id];
      if (is_callable($entry)) {
        return $this->cache($id, $entry($this));
      }
      $id = $entry;
    }

    return $this->cache($id, $this->resolve($id));
  }

  public function has(string $id): bool {
    return isset($this->entries[$id]);
  }

  public function set(string $id, callable|string $callable) {
    $this->entries[$id] = $callable;
  }

  public function inCache(string $id): bool {
    return isset($this->cachedEntries[$id]);
  }

  public function cache(string $id, $conc) {
    $this->entries[$id] = $conc;
    return $conc;
  }

  public function resolve($id) {
    $reflectionClass = new ReflectionClass($id);
    if(!$reflectionClass->isInstantiable()) {
      throw new ContainerException('Class ' . $id .' is not instantiable');
    }

    $constructor = $reflectionClass->getConstructor();
    if (!$constructor) {
      return new $id;
    }

    $parameters = $constructor->getParameters();
    if (empty($parameters)) {
      return new $id;
    }

    $dependencies = array_map(function(\ReflectionParameter $param) use ($id) {
      $name = $param->getName();
      $type = $param->getType();

      if (!$type) {
        throw new ContainerException("Failed to resolve class '$id' because param '$name' is missing a type");
      }
      
      if ($type instanceof ReflectionUnionType) {
        throw new ContainerException("Failed to resolve class '$id' because type '$type' is a union type");
      } 
      
      if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
        return $this->get($type->getName());
      }

      throw new ContainerException("Failed to resolve class '$id' invalid param '$param'");
    }, $parameters);

    return $reflectionClass->newInstanceArgs($dependencies);
  }

}