<?php
namespace app\libs;

class Hash {

	private function __construct(){}

	public static function create($plainPassword, $passwordSalt = PASSWORD_BCRYPT){
		return password_hash($plainPassword, $passwordSalt);
	}

	public static function compare($plainPassword, $hashedPassword){
		return password_verify($plainPassword, $hashedPassword);
	}

}