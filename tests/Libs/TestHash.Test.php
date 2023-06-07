<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Tiny\Libs\Hash;

class TestHash extends TestCase {

  public function testHashEquality() {
    $plainPassword = 'password';
    $hashedPassword = Hash::create($plainPassword);
    $this->assertTrue(Hash::compare($plainPassword, $hashedPassword));
  }
}