<?php
namespace app\libs;

use Exception;
use tiny\libs\Cookie;
use tiny\libs\Session;

class AuthenticationManager {

	public static function user($field=null){
		try{
			$user = (array) self::getDataFromToken();
			if($user && $field && array_key_exists($field, $user)){
				return $user[$field];
			}else{
				return null;
			}
		}catch(\Exception $e){
			return null;
		}
	}

	public static function isActivated(){
		return self::user('activated')? true: false;
	}

	public static function isLoggedIn(){
		try {
			$token = self::getToken();
			return JWT::verify($token, JWT_SECRET);
		}catch (\Exception $e){
			return false;
		}
	}

	public static function hasAuthority($authority): bool {
		return false;
	}

	public static function hasRole(string $role): bool {
		try {
			$data = self::getDataFromToken();
			if(is_array($data->roles)){
				return array_search($role, $data->roles) > -1;
			}
			if($data->roles == $role){
				return true;
			}else {
				return false;
			}
		}catch (\Exception $e){
			return false;
		}

	}

	public static function canView($user): bool {
		return false;
	}

	public static function logout(): bool {
		//LOG USER OUT
		try {
			$user = self::getDataFromToken();
			Session::destroy("token");
			Cookie::destroy("token");
			return true;
		}catch(\Exception $e){
			return false;
		}
	}

	public static function getToken(): string
	{
		return Session::get("token") ?? Cookie::get("token") ?? "";
	}

	public static function getDataFromToken(): object {
		$token = self::getToken();
		if(!$token){
			throw new Exception("Error fetching token");
		}
		$data = JWT::decode($token);
		return $data;
	}

}