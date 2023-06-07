<?php declare(strict_types=1);
include "setup.php";

use PHPUnit\Framework\TestCase;
use Tiny\App\Libs\Hash;

class Sample extends TestCase {

  public function testSampleTest() {
    $this->assertSame(1, 1);
  }
}
//to run test ./vendor/bin/phpunit tests