<?php
namespace tiny\libs\db;

use \PDO;
use stdClass;
use \PDOException;
use tiny\libs\db\exceptions\DatabaseException;

/*
* 
*/
class DB
{
	private static $_instance = null;
	private $_pdo,
			$_type,
			$_sql,
			$_lastId = false,
			$_query,
			$_error = false,
			$_errors,
			$_results,
			$_operators = ['=', '!=', '>', '<', '<=', '>=', 'LIKE', 'IN', 'BETWEEN', "IS", "IS NOT"],
			$_count = 0;

	private function __construct(){
		try{
			$this->_pdo = new PDO(DB_TYPE.':host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS);
		}catch(PDOException $e){
			die($e->getMessage());
		}
	}

	/**
	 * @return DB
	 */
	public static function ins(){
		if(!isset(self::$_instance)){
			self::$_instance = new DB();
		}
		return self::$_instance;
	}

	/**
	 * @return DB
	 */
	public static function beginTransaction(){
		$instance = self::ins();
		$instance->_pdo->beginTransaction();
		return $instance;
	}

	/**
	 * @return DB
	 */
	public static function commit(){
		$instance = self::ins();
		$instance->_pdo->commit();
		return $instance;
	}

	/**
	 * @return DB
	 */
	public static function rollback(){
		$instance = self::ins();
		$instance->_pdo->rollback();
		return $instance;
	}

	/**
	 * @return DB
	 */
	public function query($sql, $params=[]){
		$this->_error = false;
		$this->_errors = null;
		$this->_sql = $sql;

		if($this->_query = $this->_pdo->prepare($this->_sql)){
			if(count($params)){
				$x=1;
				foreach ($params as $param) {
					if(is_string($param)){
						$dataType = PDO::PARAM_STR;
					}else if(is_numeric($param)){
						$dataType = PDO::PARAM_INT;
					}else if(is_bool($param)){
						$dataType = PDO::PARAM_BOOL;
					}else {
						$dataType = PDO::PARAM_LOB;
					}
					$this->_query->bindValue($x, $param, $dataType);
					$x++;
				}
			}
			if($this->_query->execute()){
				$this->_lastId = ($this->_type == 'insert')? $this->_pdo->lastInsertId(): 0;
				$this->_results = $this->_query->fetchAll(PDO::FETCH_OBJ);
				$this->_count = $this->_query->rowCount();
			}else{
				$this->_error = true;
				$this->_errors = $this->_query->errorInfo();
			}
		}
		return $this;
	}

	private function getConditions(array $conditions){
		$conditionList = $conditions["conditions"] ?? false;
		$limit = $this->getLImit($conditions);
		$sqlPartial = "";
		$params = [];
		if($conditionList){
			if(is_array($conditionList) && is_array($conditionList[0])){
				// add up all the conditions
				// FIELD OPERATION PREDICATE
				foreach($conditionList as $condition){
					//build condition 
					if (count($condition) >= 3){
						//[FIELD, OPERATION, VALUE,? AND | OR]
						[$field, $operator, $placeholders, $values, $conjunction] = $this->getSqlClause($condition);
						$sqlPartial .= " {$field} {$operator} {$placeholders} {$conjunction} ";
						if(is_array($values)){
							$params = array_merge($params, $values);
						}else{
							array_push($params, $values);
						}
					}
				}
			}else{
				//single condition
				//[FIELD, OPERATION, VALUE]
				[$field, $operator, $placeholders, $values] = $this->getSqlClause($conditionList);
				$params = (is_array($values))? $values : [$values];
				$sqlPartial .= " {$field} {$operator} {$placeholders} ";
			}
			$sqlPartial = rtrim($sqlPartial, "AND ");
			$sqlPartial = rtrim($sqlPartial, "OR ");
		}

		return [$sqlPartial, $params, $limit];
	}

	private function getOrder($conditions): string
	{
		$order = $conditions['order'] ?? [];
		if(empty($order)){
			return '';
		}

		if(is_string($order)){
			return " ORDER BY id {$order}";
		}

		if(is_array($order) && count($order) == 1){
			return " ORDER BY id {$order[0]}";
		}

		if(is_array($order) && count($order) == 2){
			return " ORDER BY {$order[0]} {$order[1]}";
		}

		return '';
	}

	private function getLImit (array $condition){
		$limit = $condition["limit"] ?? false;
		$limitSQL = "LIMIT ";
		if($limit && !is_numeric($limit[0]) && !is_numeric($limit[1])){ throw new DatabaseException("Limit values must be numeric"); }

		if($limit && count($limit) > 1 ){
			$limitSQL .= $limit[0];
			$limitSQL .= ", ";
			$limitSQL .= $limit[1];
		}else if($limit && count($limit)) {
			$limitSQL .= $limit[0];
		}

		return $limit? $limitSQL : "";
	}

	private function getOperator(array $condition){
		$operator = "";
		if(!is_array($condition)){
			throw new DatabaseException("Can't extract the operator.");
		}
		if(count($condition) == 2){
			return "=";
		}

		$operator = strtoupper(trim($condition[1]));
		if(in_array($operator, $this->_operators)){
			return $operator;
		}
		throw new DatabaseException(printf("Operation can't be performed. Operation: %s ", $operator));
	}
	
	private function getSqlClause(array $condition){
		$field = $condition[0];
		$operator = $this->getOperator($condition);
		$placeholder = "";
		$conjunction = "";
		$values = [];
		
		if(isset($condition[3])){
			$incomingCondition = strtoupper($condition[3]);
			if(($incomingCondition == "AND" || $incomingCondition == "OR"))
				$conjunction = $incomingCondition;
		}
		
		if(count($condition) == 2){
			$placeholder = " ? ";
			$value = $condition[1];
			return [$field, $operator, $placeholder, $value];
		}else if(in_array($operator, $this->_operators) && $operator != "IN" && $operator != "BETWEEN" ){
			$placeholder .= " ? ";
			$values = $condition[2];
			return [$field, $operator, $placeholder, $values, $conjunction];
		}else if(count($condition) >= 3 
		&& isset($condition[1]) 
		&& $operator == "IN" 
		&& is_array($condition[2])){
			//break in down to look like this ( ?, ?,...)
			$placeholder .= " ( " . $this->getPlaceholderMarkers($condition[2]) . " ) ";
			$values = array_merge($values, $condition[2]);
			return [$field, $operator, $placeholder, $values, $conjunction];
		}
		else if($operator == "BETWEEN"){
			$placeholder = " ? AND ? ";
			if(!is_array($condition[2])){ throw new DatabaseException("Values for BETWEEN clause must be an array with two values");}
			$values = $condition[2];
			return [$field, $operator, $placeholder, $values, $conjunction];
		}

		throw new DatabaseException("Can't get clauses");
	}

	public function action($action, $table, $conditions=[], $join=""){
		if (empty($table)) throw new DatabaseException("Table name can't be empty");
		
		$order = $this->getOrder($conditions);
		[$clause, $params, $limit] = $this->getConditions($conditions);
		$sql = '';
		if(empty($params)){
			$sql = "{$action} FROM {$table} {$join} {$order} {$limit}";
		}else {
			$sql = "{$action} FROM {$table} {$join} WHERE {$clause} {$order} {$limit}";
		}
		if(!$this->query($sql, $params)->hasError()){
			return $this;
		}
		throw new DatabaseException("Error occurred while executing query {$this->_sql}\n<br> " . implode($this->_errors) . "\n<br>");
	}

	public function results(){
		if($this->_results && count($this->_results))
			return $this->_results;
		else
			return [];
    }
    
	public function first(){
		if(!empty($this->results()))
			return $this->results()[0];
		else
			return new stdClass();
    }
    
	public function getLastId(){
		$id = $this->_lastId;
		$this->_lastId = null;
		return $id;
    }
    
	public function find($table, array $conditions=[]){
		if(!isset($conditions["conditions"]) || empty($conditions["conditions"])) throw new DatabaseException("Empty conditions list");
		return $this->action("SELECT * ", $table, $conditions);
	}
	
	public function findAll($table, $page = null, $size = null, $conditions = []){
		if($page == null && $size == null){
			return $this->action("SELECT * ", $table, $conditions);
		}else{
			$conditions['limit'] = [$page, $size];
			return $this->action("SELECT * ", $table, $conditions);
		}
	}

    public function findIn($table, $field, $list = [], $negate = false){
		if(!count($list)) throw new DatabaseException("Search list can't be empty");
		//SELECT * FORM tablename WHERE field IN (condition, condition,...)
        return $this->action("SELECT * ", $table, ["conditions"=>[$field, "IN", $list]]);

    }
    
	public function insert($table, $fields=[]){
		if(count($fields)){
			$keys = array_keys($fields);
			$values = "";

			$values = $this->getPlaceholderMarkers($fields);

			$sql = "INSERT INTO $table (`".implode('`,`', $keys)."`) VALUES ({$values})";
			$this->_type = 'insert';
			if(!$this->query($sql, $fields)->hasError()){
				return $this;
			}else{
				throw new DatabaseException(json_encode($this->errors()));
			}
		}
		return $this;
    }

    public function getPlaceholderMarkers(array $fields){
        $markers = "";
        foreach ($fields as $key => $field) {
            $markers .= "?, ";
        }
        return rtrim($markers, ", ");
    }
    
	public function update($table, $fields, $idOrWhere){
		$set = '';
		$x= 1;

		foreach ($fields as $name => $value) {
			$set .= "{$name} = ?";
			if($x < count($fields)){
				$set .= ", ";
			}
			$x++;
		}
		$sql = "";
		if(is_array($idOrWhere) && count($idOrWhere) == 3){
			$field = $idOrWhere[0];
			$operator = $idOrWhere[1];
			$value = $idOrWhere[2];
			$sql = "UPDATE {$table} SET {$set} WHERE {$field} {$operator} '{$value}'";
		}else
			$sql = "UPDATE {$table} SET {$set} WHERE id = {$idOrWhere}";
		if(!$this->query($sql, $fields)->hasError()){
			return true;
		}
		return false;
	}

	public function join($tableName, $conditionList=[]){
		$parent = $this;

		return new class ($tableName, $conditionList, $parent) {
			public function __construct($tableName, $conditionList, $parent) {
				$this->tableName = $tableName;
				$this->conditionList = $conditionList;
				$this->parent = $parent;
			}

			public function getJoinSql($joinType, $array){
				switch ($joinType) {
					case 'ONE_TO_ONE':
						$sql = "";
						foreach ($array as $key => $value) {
							$sql .= " LEFT JOIN {$key} on {$value[0]} = {$value[1]} ";
						}
						// $sql = rtrim($sql, ", ");
						// echo $sql;
						return $sql;
						break;
					case 'ONE_TO_MANY':
						$sql = "";
						foreach ($array as $key => $value) {
							$sql .= " LEFT JOIN {$key} on {$value[0]} = {$value[1]} ";
						}
						// $sql = rtrim($sql, ", ");
						return $sql;
						break;
					case 'MANY_TO_MANY':
						$arr = [];
						foreach ($array as $key => $value) {
							$arr[] = [$key, $value[0], $value[1]];
						}
						$sql = " LEFT JOIN {$arr[0][0]} ON {$arr[0][1]} = {$arr[0][2]} 
								LEFT JOIN {$arr[1][1]} on {$arr[1][1]} = {$arr[1][2]} ";
						return $sql;
						break;
					default:
						return "";
						break;
				}
			}

			public function oneToOne(Array $array){
				$joinString = $this->getJoinSql("ONE_TO_ONE", $array);
				return $this->parent->action("SELECT * ", $this->tableName, $this->conditionList, $joinString);
			}

			public function oneToMany(Array $array){
				$joinString = $this->getJoinSql("ONE_TO_MANY", $array);
				return $this->parent->action("SELECT * ", $this->tableName, $this->conditionList, $joinString);
			}

			public function manyToMany(Array $array){
				$joinString = $this->getJoinSql("MANY_TO_MANY", $array);
				return $this->parent->action("SELECT * ", $this->tableName, $this->conditionList, $joinString);
			}

		};
	}

	public function countRows($tableName, $conditions = [], $columnToCount = "id")
	{
		return $this->action("SELECT count($columnToCount) as count ", $tableName, $conditions);
	}
    
    public function delete($table, $conditions){
		return $this->action("DELETE ", $table, $conditions);
	}

	public function sql(){
		return $this->_sql;
	}
    
    public function hasError(){
		return $this->_error;
	}
    
    public function errors(){
		return $this->_errors;
	}
    
    public function count(){
		return $this->_count;
	}

	public static function setConditions(Array $conditions){
		return ['conditions'=> $conditions ];
	}

}