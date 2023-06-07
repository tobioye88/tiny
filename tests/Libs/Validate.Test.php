<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Tiny\Libs\Validate as Validation;

class Validate extends TestCase {

  /**
   * @dataProvider minimumRuleDataProvider
  */
  public function testMinimumRule($body, $rule, $expected) {
    $validation = new Validation($body, $rule);
    $this->assertSame($validation->isValid(), $expected);
  }

  public static function minimumRuleDataProvider() {
    return [
      'When age is grateThan min: 10 > 5' => [
        ['age' => 10],
        ['age' => ['min' => 5]],
        true
      ],
      'When age is 10 > 15' => [
        ['age' => 10],
        ['age' => ['min'=> 15]],
        false
      ],
      'When age is 10 = 10' => [
        ['age' => 10],
        ['age' => ['min'=> 10]],
        true
      ],
    ];
  }
  /**
   * @dataProvider maximumRuleDataProvider
  */
  public function testMaximumRule($body, $rule, $expected) {
    $validation = new Validation($body, $rule);
    $this->assertSame($validation->isValid(), $expected);
  }

  public static function maximumRuleDataProvider() {
    return [
      'When age is grateThan min: 5 = 5' => [
        ['age' => 5],
        ['age' => ['max' => 5]],
        true
      ],
      'When age is 50 > 5' => [
        ['age' => 50],
        ['age' => ['max'=> 5]],
        false
      ],
      'When age is 5 < 50' => [
        ['age' => 5],
        ['age' => ['max'=> 50]],
        true
      ],
    ];
  }

  /**
   * @dataProvider matchRuleProvider
  */
  public function testMatchRule($body, $rule, $expected) {
    $validation = new Validation($body, $rule);
    $this->assertSame($validation->isValid(), $expected);
  }

  public static function matchRuleProvider() {
    return [
      'Same string' => [
        ['password' => 'password', 'confirmPassword' => 'password'],
        ['confirmPassword' => ['matches' => 'password']],
        true
      ],
      'String is different' => [
        ['password' => '1password', 'confirmPassword' => 'password'],
        ['confirmPassword' => ['matches' => 'password']],
        false
      ],
    ];
  }

  /**
   * @dataProvider alphaRuleProvider
  */
  public function testAlphaRule($body, $rule, $expected) {
    $validation = new Validation($body, $rule);
    $this->assertSame($validation->isValid(), $expected);
  }

  public static function alphaRuleProvider() {
    return [
      'alpha string' => [
        ['first_name' => 'John', 'last_name' => 'doe'],
        ['first_name' => ['alpha' => true], 'last_name' =>  ['alpha' => true]],
        true
      ],
      'non alpha string' => [
        ['first_name' => 'John ', 'last_name' => 'doe@jane.com'],
        ['first_name' => ['alpha' => true], 'last_name' =>  ['alpha' => true]],
        false
      ]
    ];
  }

  /**
   * @dataProvider stringAlphanumericRuleProvider
  */
  public function testStringAlphanumericRule($body, $rule, $expected) {
    $validation = new Validation($body, $rule);
    $this->assertSame($validation->isValid(), $expected);
  }

  public static function stringAlphanumericRuleProvider() {
    return [
      'String rule string' => [
        ['first_name' => 'John', 'description' => 'John lived in the far west of the island. In Jane\'s house.'],
        ['first_name' => ['string' => true], 'description' =>  ['string' => true]],
        true
      ],
      'Alphanumeric rule string' => [
        ['first_name' => 'John', 'description' => 'John lived in the far west of the island. In Jane\'s house.'],
        ['first_name' => ['alphanumeric' => true], 'description' =>  ['alphanumeric' => true]],
        true
      ],
      'String rule string is not valid' => [
        ['first_name' => '<John></John>', 'description' => 'John lived in the far <b>west</b> of the island. In Jane\'s house.'],
        ['first_name' => ['string' => true], 'description' =>  ['string' => true]],
        false
      ],
      'Alphanumeric rule string is not valid' => [
        ['first_name' => '<John></John>', 'description' => 'John lived in the far <b>west</b> of the island. In Jane\'s house.'],
        ['first_name' => ['alphanumeric' => true], 'description' =>  ['alphanumeric' => true]],
        false
      ]
    ];
  }

  /**
   * @dataProvider regexRuleProvider
  */
  public function testRegexRule($body, $rule, $expected) {
    $validation = new Validation($body, $rule);
    $this->assertSame($validation->isValid(), $expected);
  }

  public static function regexRuleProvider() {
    return [
      'Regex rule valid string' => [
        ['first_name' => 'John'],
        ['first_name' => ['regex' => '/[a-zA-Z]/'], ],
        true
      ],
    ];
  }
 
  /**
   * @dataProvider numberRuleProvider
  */
  public function testNumberRule($body, $rule, $expected) {
    $validation = new Validation($body, $rule);
    $this->assertSame($validation->isValid(), $expected);
  }

  public static function numberRuleProvider() {
    return [
      'Number rule valid string' => [
        ['phoneNumber' => '08034006567', 'age' => 34 ],
        ['phoneNumber' => ['number' => true], 'age' => ['number' => true], ],
        true
      ],
    ];
  }

  /**
   * @dataProvider emailRuleProvider
  */
  public function testEmailRule($body, $rule, $expected) {
    $validation = new Validation($body, $rule);
    $this->assertSame($validation->isValid(), $expected);
  }

  public static function emailRuleProvider() {
    return [
      'email rule valid string' => [
        ['email' => 'john@mail.com' ],
        ['email' => ['email' => true]],
        true
      ],
      'email rule invalid string' => [
        ['email' => 'johntravolter.com' ],
        ['email' => ['email' => true]],
        false
      ],
      'email rule invalid string' => [
        ['email' => 'johntravolter.com@market' ],
        ['email' => ['email' => true]],
        false
      ],
    ];
  }

  /**
   * @dataProvider uniqueRuleProvider
  */
  public function testUniqueRule($body, $rule, $expected) {
    $validation = new Validation($body, $rule);
    $this->assertSame($validation->isValid(), $expected);
  }

  public static function uniqueRuleProvider() {
    return [
      'Unique rule valid string' => [
        ['email' => 'john@mail.com' ],
        ['email' => ['unique' => function($value) { return $value == 'john@mail.com';}]],
        true
      ],
      'Unique rule invalid string' => [
        ['email' => 'johntravolter.com' ],
        ['email' => ['unique' => function($value) { return $value == 'john@mail.com';}]],
        false
      ],
    ];
  }

  /**
   * @dataProvider enumRuleProvider
  */
  public function testEnumRule($body, $rule, $expected) {
    $validation = new Validation($body, $rule);
    $this->assertSame($validation->isValid(), $expected);
  }

  public static function enumRuleProvider() {
    return [
      'Enum rule valid string' => [
        ['workday' => 'Tuesday' ],
        ['workday' => ['enum' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']]],
        true
      ],
      'Enum rule invalid string' => [
        ['workday' => 'Saturday' ],
        ['workday' => ['enum' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']]],
        false
      ],
    ];
  }

  /**
   * @dataProvider equalsRuleProvider
  */
  public function testEqualRule($body, $rule, $expected) {
    $validation = new Validation($body, $rule);
    $this->assertSame($validation->isValid(), $expected);
  }

  public static function equalsRuleProvider() {
    return [
      'Equal rule valid string' => [
        ['workday' => 'Tuesday' ],
        ['workday' => ['equals' => 'Tuesday']],
        true
      ],
      'Equal rule valid number' => [
        ['one' => 1 ],
        ['one' => ['equals' => 1]],
        true
      ],
      'Equal rule valid boolean' => [
        ['true' => true ],
        ['true' => ['equals' => true]],
        true
      ],
      'Equal rule valid number' => [
        ['one' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] ],
        ['one' => ['equals' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']]],
        true
      ],
      'Equal rule invalid string' => [
        ['workday' => 'Saturday' ],
        ['workday' => ['equals' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']]],
        false
      ],
    ];
  }

  public function testInvalidRule() {
    $body = ['workday' => 'Saturday' ];
    $rule = ['workday' => ['anyRule' => true] ];
    $expected = false;

    $validation = new Validation($body, $rule);
    $this->assertSame($validation->isValid(), $expected);
  }
}

// php -d xdebug.mode=profile ./vendor/bin/phpunit tests     