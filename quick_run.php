<?php 
include 'src/App/Libs/Validate.php';


// ['email' => 'johntravolter.com' ],
// ['email' => ['unique' => function($value) { return $value != 'john@mail.com';}]],

$data = ['email' => 'johntravolter.com' ];
$rule = ['email' => ['unique' => function($value) { return $value != 'john@mail.com';}]];

$validate = new Tiny\App\Libs\Validate($data, $rule);
echo $validate->isValid() ? 'true' : 'false';
