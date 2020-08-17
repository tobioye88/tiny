<?php
namespace tiny\libs;

use \Exception;

/**
* File::set($destination)->upload();
* File::set($destination)->uploadAll();
* File::delete($destination);
*/
class File {
	private static $_instance; 
	protected $_path, 
	$_fullPath, 
	$_lastPath, 
	$_fileTmpLoc, 
	$_fileError, 
	$_size, 
	$_errors = [], 
	$_type, 
	$_ext, 
	$_name, 
	$_files;

	protected function __construct(){}

	public static function set($destination, $fieldname){
		if(!isset(self::$_instance)){
			self::$_instance = new self();
		}
		self::$_instance->_setFile($destination, $fieldname);
		// self::$_instance->_setDestination($destination);
		return self::$_instance;
	}
	
	protected function _setFile($dest, $fieldname){
		$this->_name = $_FILES[$fieldname]["name"];
		$this->_fileTmpLoc = $_FILES[$fieldname]["tmp_name"];
		$this->_type = $_FILES[$fieldname]["type"]; 
		$this->_size = $_FILES[$fieldname]["size"]; 
		$this->_fileError = $_FILES[$fieldname]["error"];
		$this->_setDestination($dest);
		$this->_setExtension();
		$this->_lastPath = $this->_fullPath = $this->_path . $this->_name;
	}
	
	protected function _setDestination($dest){
		$dest = rtrim($dest, "/");
		$this->_path = $dest;
	}
	
	protected function _setExtension(){
		$arr = explode(".", $this->_name);
		$this->_ext = strtolower(end($arr));
	}
	
	public function rename($name=null){
		$this->_name = ($name)? $name . "." . $this->_ext : "File_". uniqid() .".".$this->_ext;
		$this->_lastPath = $this->_fullPath = $this->_path . $this->_name;
		return $this;
	}
	
	public function upload(){
		$this->createPath();
		$moveResult = move_uploaded_file($this->_fileTmpLoc, $this->_path . '/' .$this->_name);
		// Check to make sure the move result is true before continuing
		if (!$moveResult) {
			throw new Exception("File not uploaded. Try again.");
		}
		return $this;
	}

	private function createPath(){
		// echo __. $this->_path;
		if(!is_dir($this->_path)){
			mkdir($this->_path);
		}
	}

	public function errors(): bool {
		return !count($this->_errors);
	}

	public function size(){
		return $this->_size;
	}

	public static function delete($path=null){
		if(!empty($path) && is_file($path)){
			unlink($path);
			return true;
		}
		throw new Exception("File can not be found path: " + $path);
	}

	public function getName(){
		return $this->_name;
	}
}