<?php
namespace tiny\libs;

class Session
{
	
	public static function init(){
		session_start();
	}
	
	public static function set($key, $value, $main='app')
	{
		$_SESSION[$main][$key] = $value;
	}
	
	public static function get($key, $main='app')
	{
		if (isset($_SESSION[$main][$key]))
			return $_SESSION[$main][$key];
		else
			return null;
	}

	public static function flash($key, $main='app')
	{
		$val = self::get($key, $main);
		self::destroy($key);
		return $val;
	}

	public static function destroy($key=null, $main='app')
	{
		if (isset($_SESSION[$main][$key])){
			unset($_SESSION[$main][$key]);
		}elseif($key === true){
			unset($_SESSION[$main]);
		}
	}
	
}
