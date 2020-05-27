<?php
namespace tiny\libs;

class Cookie {
	
	public static function set($key, $value) {
		setcookie($key, $value, time() + (86400 * 30), '/');
	}
	
	public static function get($key) {
		if (isset($_COOKIE[$key]))
			return $_COOKIE[$key];
		else
			return null;
	}
	
	public static function destroy($key) {
		@setcookie($key, "", strtotime( '-5 days' ), '/');
	}
}