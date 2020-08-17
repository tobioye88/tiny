```php

DB::ins()->query("Select * from user", );
DB::ins()->query("Select * from user where id = ? ", ['id'=>1]);
DB::ins()->find("user", ['id', '=', 1]);
DB::ins()->findALL("user");
DB::ins()->findIn("user", 'id', [1,2,3,4]);
DB::ins()->find('user', [
	['id', '>', 1, 'AND'],
	['email', 'like', '%john%', 'AND'],
	['active', '=', 1, 'AND'],
	['created_date', 'between', ['2020-01-10', '2020-03-01'], 'AND'],
]);

$results = DB::ins()
				->join('joiningTableName')
				->oneToOne([
					'user_profile' => ['property.user_id', 'user_profile.user_id'],
					'user' => ['property.user_id', 'user.id'],
					'schools' => ['property.user_id', 'schools.id'],
				])->first();

$results = DB::ins()
				->join('manyTable', ["conditions" =>["schools.id", "=", 2]])
				->oneToMany(['manyTable' => ['manyTable.school_id', 'parentTable.id']])
				->results();

$resultes = DB::ins()
				->join('joiningTableName')
				->manyToMany([
					'pivotTable' => ['parent_column', 'anotherTable'],
					'otherTableName' => ['joinTable', 'anotherTable'],
				])
				->results();

print_r($properties);

```