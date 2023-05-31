<?php
namespace Tiny\Libs\DB;


interface IEntity {

    function count();
    static function countRows(array $conditions=[]): int;

    function exist(): bool;
    function isEmpty(): bool;

    function save();
    static function preSave(callable $function);
    static function postSave(callable $function);

    static function create(array $array): self;
    static function createBulk(array $listOfAssoc): array;

    static function fill(array $assoc): self;
    static function lastOne();
    static function last(int $number = 1);
    static function all(array $conditions=[]);
    static function paged(array $conditions=[], $page = 0, $size = 20);
    static function findAll(array $conditions=[], $page = null, $size = null);
    static function find(array $conditions = []);
    static function findBy(array $arr, $order = "asc");
    static function findOneBy($arr);
    static function findById(int $id);
    static function findIn(string $field, array $searchKeyList= []);
    static function findLike(string $field, string $value);

    function delete($id = null): bool;
    static function remove(int $id): bool;
    static function removeBy(array $conditions): bool;
    static function deleteIn(array $list, $field="id"): bool;

    function hasOne(Array $arr, $conditions = []);
    static function belongsToOne(Array $arr, $conditions = []);

    function hasMany(Array $arr, $conditions = []);
    
    
}