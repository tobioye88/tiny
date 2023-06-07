<?php
namespace Tiny\Libs;

class Hash {

	private function __construct(){}

	public static function create(string $plainPassword){
		return password_hash($plainPassword, PASSWORD_BCRYPT);
	}

	public static function compare(string $plainPassword, string $hashedPassword): bool {
		return password_verify($plainPassword, $hashedPassword);
	}

}