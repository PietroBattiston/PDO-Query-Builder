# Query Builder for simple PDO queries

## Usage

### INSERT
```php

$qb = new QueryBuilder;

$values = [
	'name' => 'John',
	'surname' => 'Doe'
];
$query = $qb
        ->table('YourTable')
	->create($values);

var_dump($query);
//INSERT INTO YourTable (name,surname) VALUES (:name,:surname)

```
### SELECT
```php

$qb = new QueryBuilder;

$query = $qb
	->table('YourTable')
	->select(['name, surname'])
	->returnQuery();

var_dump($query);
//SELECT name, surname FROM YourTable

$query = $qb
	->table('YourTable')
	->select(['*'])
	->returnQuery();

var_dump($query);
//SELECT * FROM YourTable

```
### WHERE
```php

$qb = new QueryBuilder;


$query = $qb
	->table('YourTable')
	->select(['*'])
	->where('name','=','John')
	->returnQuery();

var_dump($query);
//SELECT * FROM YourTable WHERE name=:name

```
### LIMIT
```php

$qb = new QueryBuilder;

$query = $qb
	->table('YourTable')
	->select(['*'])
	->limit(2)
	->returnQuery();

var_dump($query);
//SELECT * FROM YourTable LIMIT 2

```
### UPDATE
```php

$qb = new QueryBuilder;

$id = 32;
$newName = 'Paul';

$query = $qb
	->table('YourTable')
	->where('id','=', $id)
	->update(['title' => $newName]);

var_dump($query);
//UPDATE YourTable SET title=:title WHERE id=:id
	
```
### DELETE
```php

$qb = new QueryBuilder;

$id = 32;
$query = $qb
	->table('YourTable')
	->where('id','=', $id)
	->delete();

var_dump($query);
//DELETE FROM YourTable WHERE id=:id

```


## Working with binded values
As you may had notice, the Query Builder returns the query containing [bindend values](https://www.php.net/manual/en/pdostatement.bindvalue.php).

### Example

```php

$database = new Database;
$qb = new QueryBuilder;

$id = 32;
$query = $qb
	->table('YourTable')
	->where('id','=', $id)
	->delete();

$database->prepare($query);

foreach ($qb->bindedValues as $key => $value) {
	$database->bindValues($key, $value);
}

$database->execute();

```
And your Database class can look like this:

```php

class Database
{
	private $pdo;
	private $statement;
	
	function __construct()
	{
		$dsn = "mysql:host=localhost;dbname=YourDBName;charset=utf8mb4";
		$options = [
		  PDO::ATTR_EMULATE_PREPARES   => false, // turn off emulation mode for "real" prepared statements
		  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, //turn on errors in the form of exceptions
		  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //make the default fetch be an associative array
		];
		try {
		  $this->pdo = new PDO($dsn, "YourUsername", "YourDBPassword", $options);
		} catch (Exception $e) {
		  error_log($e->getMessage());
		  exit('ERROR!');
		}
	}

	public function prepare(string $query):void
	{
		$this->statement = $this->pdo->prepare($query);
	}

	public function bindValues($param, $value, $type = NULL):void
 	{
 		switch (is_null($type)) {
				case is_int($value):
					$type = PDO::PARAM_INT;
					break;
				case is_bool($value):
					$type = PDO::PARAM_BOOL;
					break;
				case is_null($value):
					$type = PDO::PARAM_NULL;
					break;
				default:
					$type = PDO::PARAM_STR;
		}
		$this->statement->bindValue($param, $value, $type);
 	}

 	public function execute():void
	{
		$this->statement->execute();
	}

}

```

## Bugs
Please let me know if you find bugs.

## Contribute
Feel free to contribute adding new features to the project. Please follow the latest PHP Coding Style and Best practices :)
