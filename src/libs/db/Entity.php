<?php
namespace tiny\libs\db;

use JsonSerializable;
use ReflectionClass;
use ReflectionProperty;


//TODO: move mapping functions to an object mapper class
abstract class Entity implements IEntity, JsonSerializable
{
    protected $tableName;
    // public $ignoreIfEmpty = [];


    public function __construct(int $id = null) {
        if($id){
            self::build(DB::ins()->find($this->tableName, ["conditions" => ["id", "=", $id]])->first(), $this);
        }
    }

    public function save()
    {
        $class = new ReflectionClass($this);
        $tableName = self::getTableName();

        //valid properties == not empty properties
        $params = [];
        foreach (self::getAllProperties($class) as $property) {
            $propertyName = $property->getName();
            if(in_array(strtolower($propertyName),["id", "tablename", "db", "ignoreIfEmpty"])){
                continue;
            }
            if($this->{$propertyName} !== null) {
                $params[$propertyName] = $this->{$propertyName} == "NULL" ? null : $this->{$propertyName};
            }
        }

        if ($this->id > 0) {
            $this->db = DB::ins()->update($tableName, $params, $this->id);
        } else {
            $this->id = DB::ins()->insert($tableName, $params)->getLastId();
        }

        return self::build(DB::ins()->find($this->tableName, ["conditions" => ["id", "=", $this->id]])->first(), $this);
    }


    private static function getAllProperties($class)
    {
        return $class->getProperties(ReflectionProperty::IS_PUBLIC); //IS_PROTECTED  // properties to consider 
    }

    private static function getTableName(){
        $class = new ReflectionClass(get_called_class()); // this is a static method that's why i use get_called_class
        $entity = $class->newInstance();
        return $entity->tableName ?? null;
    }

    /**
     *
     * @return Entity
     */
    private static function build(object $object, $instance = null)
    {
        $class = new ReflectionClass(get_called_class()); // this is a static method that's why i use get_called_class

        $entity = $instance ?? $class->newInstance();

        foreach (self::getAllProperties($class) as $prop) {
            $propertyName = $prop->getName();
            if(in_array(strtolower($propertyName),["tablename", "db"])){
                continue;
            }

            if (isset($object->{$propertyName})) {
                $property = $class->getProperty($propertyName);
                $property->setAccessible(true);
                $property->setValue($entity, $object->{$propertyName});
            }
        }

        return $entity;
    }

    /**
     * @return Entity[]
     */
    private static function buildList(array $arr): array
    {
        $list = [];
        foreach($arr as $object){
            $list[] = self::build($object);
        }
        return $list;
    }

    /**
     *
     * @return Entity[]
     */
    public static function all(array $conditions=[], $page = null, $size = null) 
    {
        $tableName = self::getTableName();
        return self::buildList(DB::ins()->findAll($tableName, $page, $size, $conditions)->results());
    }

    /**
     *
     * @return Entity[]
     * [conditions: [field, operation, $value], limit: [size, start]]
     */
    public static function find(array $conditions = []) 
    {
        $tableName = self::getTableName();
        return self::buildList(DB::ins()->find($tableName, $conditions)->results());
    }

    /**
     *
     * @return Entity[]
     */
    public static function findBy($arr, $order = "asc")
    {
        $tableName = self::getTableName();
        $conditions = ["conditions" => $arr, 'order' => $order];
        return self::buildList(DB::ins()->find($tableName, $conditions)->results());
    }

    /**
     *
     * @return Entity
     */
    public static function findOneBy($arr)
    {
        $tableName = self::getTableName();
        return self::build(DB::ins()->find($tableName, ["conditions" => $arr])->first());
    }

    /**
     *
     * @return Entity
     */
    public static function findById(int $id)
    {
        $tableName = self::getTableName();
        return self::build(DB::ins()->find($tableName, ["conditions" => ["id", "=", $id]])->first());
    }
    
    /**
     *
     * @return Entity[]
     */
    public static function findIn(string $field, array $searchKeyList= [])
    {
        $tableName = self::getTableName();
        return self::buildList(DB::ins()->findIn($tableName, $field, $searchKeyList)->results());
    }
    
    public static function findLike(string $field, string $value)
    {
        $tableName = self::getTableName();
        return self::buildList(DB::ins()->find($tableName, ["conditions" => [$field, "LIKE", $value]])->results());
    }
    
    public function delete($id = null)
    {
        $id = $this->id ?? $id;
        return !DB::ins()->delete($this->tableName, ["conditions" => ["id", $id]])->hasError();
    }

    public static function remove(int $id)
    {
        $tableName = self::getTableName();
        return !DB::ins()->delete($tableName, ["conditions" => ["id", $id]])->hasError();
    }
    
    public static function deleteIn(array $list, $field="id")
    {
        $tableName = self::getTableName();
        return !DB::ins()->delete($tableName, ["conditions" => [$field, "IN", $list]])->hasError();
    }

    public function hasOne(Array $arr, $conditions = []){
        return DB::ins()->join($this->tableName, $conditions)->oneToOne($arr)->first();
    }
    
    public function hasMany(Array $arr, $conditions = []) 
    {
        return DB::ins()->join($this->tableName, $conditions)->oneToMany($arr)->results();
    }


    public function count()
    {
        return DB::ins()->count();
    }
    
    public static function countRows(array $conditions=[]): int
    {
        $tableName = self::getTableName();
        return DB::ins()->countRows($tableName, $conditions)->first()->count;
    }

    public function isEmpty(): bool
    {
        //loop over each element 
        //if the var name is tableName skip
        //check if all the vales are empty
        foreach((array) $this as $key => $value){
            if($key == 'tableName') continue;
            if($value != null){
                return false;
            }
            return true;
        }
        // return empty((array) $this);
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);

        // return $vars;
        $objectArray = [];
        foreach ($vars as $key => $prop) {
            $propertyName = $key;
            if(in_array(strtolower($propertyName),["tablename", "db"])){
                continue;
            }

            $objectArray[$key] = $prop;
        }

        return $objectArray;
    }

}
