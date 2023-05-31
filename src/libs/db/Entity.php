<?php
namespace Tiny\Libs\DB;

use Error;
use JsonSerializable;
use ReflectionClass;
use ReflectionProperty;
use Tiny\Libs\DB\Exceptions\DatabaseException;

//TODO: move mapping functions to an object mapper class
abstract class Entity implements IEntity, JsonSerializable
{
    protected string $tableName;
    private static $preSaveFunction;
    private static $postSaveFunction;

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
            if(self::isPropertyNameReservedName($propertyName)){
                continue;
            }
            if(isset($this->{$propertyName}) && $this->{$propertyName} !== null) {
                $params[$propertyName] = $this->{$propertyName} === "NULL" || $this->{$propertyName} === "null" ? null : $this->{$propertyName};
            }
        }

        if(isset(self::$preSaveFunction) && is_callable(self::$preSaveFunction)) {
            call_user_func(self::$postSaveFunction, $params);
        }

        if (isset($this->id) && $this->id > 0) {
            $this->db = DB::ins()->update($tableName, $params, $this->id);
        } else {
            $this->id = DB::ins()->insert($tableName, $params)->getLastId();
        }
        if(self::$postSaveFunction && is_callable(self::$postSaveFunction)) {
            call_user_func(self::$postSaveFunction, $params);
        }

        return self::build(DB::ins()->find($this->tableName, ["conditions" => ["id", "=", $this->id]])->first(), $this);
    }

    private static function isPropertyNameReservedName(string $propertyName): bool
    {
        return in_array(strtolower($propertyName), ["id", "tablename", "db", "ignoreIfEmpty"]);
    }

    private static function isPropertyNameReservedNameExcludeId(string $propertyName): bool
    {
        return in_array(strtolower($propertyName), ["tablename", "db", "ignoreIfEmpty"]);
    }

    public static function create(array $args): self
    {
        $class = new ReflectionClass(get_called_class());
        $entity = $class->newInstance();

        foreach (self::getAllProperties($class) as $property) {
            $propertyName = $property->getName();
            if(self::isPropertyNameReservedNameExcludeId($propertyName)){
                continue;
            }
            
            if(isset($args[$propertyName])) {
                $entity->{$propertyName} = $args[$propertyName];
            }
        }

        return $entity->save();
    }

    /**
     * @throws DatabaseException
     */
    public static function createBulk(array $listOfAssoc): array 
    {
        // start transaction
        // DB::beginTransaction();
        $savedList = [];
        try {
            foreach($listOfAssoc as $key => $assoc){
                $object = self::fill($assoc);
                $object->save();
                array_push($savedList, $object);
            }
            // DB::commit();
        } catch (DatabaseException $e) {
            // DB::rollback();
            throw new DatabaseException("Bulk creation failed. " . $e->getMessage());
        }
        return $savedList;
    }

    public static function fill(array $assoc): self
    {
        $class = new ReflectionClass(get_called_class());
        $entity = $class->newInstance();

        foreach (self::getAllProperties($class) as $property) {
            $propertyName = $property->getName();
            if(self::isPropertyNameReservedName($propertyName)){
                continue;
            }
            if(isset($assoc[$propertyName])){
                $entity->{$propertyName} = $assoc[$propertyName];
            }

        }
        return $entity;
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
            if(self::isPropertyNameReservedNameExcludeId($propertyName)){
                continue;
            }

            $propertyType = $prop->getType() == null? null : $prop->getType()->getName();

            $property = $class->getProperty($propertyName);
            $property->setAccessible(true);
            if (isset($object->{$propertyName})) {
                switch($propertyType){
                    case 'int':
                        $value = $object->{$propertyName} === null ? null : (int) $object->{$propertyName};
                        $property->setValue($entity, $value);
                        break;
                    case 'string':
                        $value = $object->{$propertyName} === null ? null : (string) $object->{$propertyName};
                        $property->setValue($entity, $value);
                        break;
                    case 'float':
                        $value = $object->{$propertyName} === null ? null : (float) $object->{$propertyName};
                        $property->setValue($entity, $value);
                        break;
                    case 'bool':
                        $value = $object->{$propertyName} === null ? null : boolval($object->{$propertyName});
                        $property->setValue($entity, $value);
                        break;
                    default:
                        $property->setValue($entity, $object->{$propertyName});
                }
            }else{
                try {
                    // If property is not nullish (public ?string) an exception will be thrown; 
                    $property->setValue($entity, null);
                } catch (Error $e) {
                    // Fail Silently
                }
            }
        }

        return $entity;
    }

    /**
     *
     * @return Entity
     */
    public static function lastOne()
    {
        $tableName = self::getTableName();
        $conditions = ['limit' => [1], 'order' => 'desc'];
        return self::buildList(DB::ins()->find($tableName, $conditions)->first());
    }

    /**
     *
     * @return Entity[]
     */
    public static function last(int $number = 1) 
    {
        $tableName = self::getTableName();
        $conditions = ['limit' => [$number], 'order' => 'desc'];
        return self::buildList(DB::ins()->find($tableName, $conditions)->results());
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

    public static function getPage(int $page, int $size): int
    {
        if($page <= 1){
            return 0;
        }
        return ($page - 1) * $size;
    }

    /**
     *
     * @return Entity[]
     */
    public static function all(array $conditions=[]) 
    {
        $tableName = self::getTableName();
        return self::buildList(DB::ins()->findAll($tableName, null, null, $conditions)->results());
    }

    /**
     *
     * @return Entity[]
     */
    public static function paged(array $conditions=[], $page = 0, $size = 20) 
    {
        $tableName = self::getTableName();
        $page = self::getPage($page, $size);
        return self::buildList(DB::ins()->findAll($tableName, $page, $size, $conditions)->results());
    }

    /**
     *
     * @return Entity[]
     */
    public static function findAll(array $conditions=[], $page = null, $size = null) 
    {

        $page = self::getPage($page, $size);
        return self::paged($conditions, $page, $size);
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
    public static function findBy(array $arr, $order = "asc")
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
    
    public function delete($id = null): bool
    {
        $id = $this->id ?? $id;
        return !DB::ins()->delete($this->tableName, ["conditions" => ["id", $id]])->hasError();
    }

    public static function remove(int $id): bool
    {
        $tableName = self::getTableName();
        return !DB::ins()->delete($tableName, ["conditions" => ["id", $id]])->hasError();
    }

    public static function removeBy(array $conditions): bool
    {
        $tableName = self::getTableName();
        return !DB::ins()->delete($tableName, ["conditions" => $conditions])->hasError();
    }
    
    public static function deleteIn(array $list, $field="id"): bool
    {
        $tableName = self::getTableName();
        return !DB::ins()->delete($tableName, ["conditions" => [$field, "IN", $list]])->hasError();
    }

    public function hasOne(Array $arr, $conditions = [])
    {
        return DB::ins()->join($this->tableName, $conditions)->oneToOne($arr)->first();
    }

    public static function belongsToOne(Array $arr, $conditions = [])
    {
        $tableName = self::getTableName();
        return DB::ins()->join($tableName, $conditions)->oneToOne($arr)->results();
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

    public function exist(): bool
    {
        return !$this->isEmpty();
    }

    public static function preSave(callable $callable){
        self::$preSaveFunction = $callable;
    }

    public static function postSave(callable $callable){
        self::$postSaveFunction = $callable;
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
        return true;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);

        // return $vars;
        $objectArray = [];
        foreach ($vars as $key => $prop) {
            $propertyName = $key;
            if(self::isPropertyNameReservedNameExcludeId($propertyName)){
                continue;
            }

            $objectArray[$key] = $prop;
        }

        return $objectArray;
    }

}
