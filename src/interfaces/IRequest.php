<?php

namespace Tiny\Interfaces;

interface IRequest {

    
    public function getQueryParam(string $name, $default = null);
    
    public function getQueryParams(): array;
    
    public function getPathParam(string $name, $default = null);
    
    public function getPathParams();
    
    public function getUrl(): string;
    
    public function getMethod(): string;
    
    public function setPathParams(array $pathParams);
    
    public function getHeader(string $name);
    
    public function getHeaders();
    public function getBody(): ?array;
    
    public function getContentType(): ?array;

    public function setCookies(string $name, $value);

    public function getCookies(string $name);
    
    public function destroyCookies(string $name);

    public function getSession(string $name);

    public function setSession(string $name, $value);
    
    public function destroySession(string $name);

    public function uploadFile(string $destination, string $fieldName, $newName=null): bool;

    public function hasFile(string $fieldName): bool;

    public function file(string $fileName);

    public function files(string $fileName = null);

    public function fileSize(string $fileName);

    public function fileName(string $fileName);
    
    public function fileType(string $fileName);

    public function acceptJson(): bool;

    public function getBodyAsArray(array $arrayList): array;

    public function getBodyAsObject(array $arrayList): object;

}