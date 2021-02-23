<?php declare(strict_types=1);

	namespace QueryBuilder;


	class QueryBuilder
	{
		public $table = '';
		public $query = '';
		public $statement = '';
		public $bindedValues = [];
		public $dbMethod = '';
		public $affectedRows;

		public function __construct() 
		{
		}

		/*
		|--------------------------------------------------------------------------
		| Set the Database Table's Name
		|--------------------------------------------------------------------------
		*/
		public function table(string $tableName):self 
		{
			
			$this->table = $tableName;
			//emptying values in case have been already used
			$this->query = '';
			$this->bindedValues = [];

			return $this;
		}

		/*
		|--------------------------------------------------------------------------
		| Select the Database's columns
		|--------------------------------------------------------------------------
		|
		| It sets the query like this:'SELECT columnName FROM tableName'
		| 
		*/
		public function select(array $selectedColumn):self 
		{
			$query = "SELECT @colName FROM @tableName";
			$selectedColumn = implode(',',$selectedColumn);
			$placeholdersValues = [
				"@colName" => $selectedColumn, 
				"@tableName" => $this->table, 
			];
			$this->query = $this->replacePlaceholders($query, $placeholdersValues);

			return $this;
		}

	    /*
	    |--------------------------------------------------------------------------
	    | Build the CREATE query
	    |--------------------------------------------------------------------------
	    |
	    | It sets the query like this:
	    | INSERT INTO tableName (columnName) VALUES (:columnName)
	    | or
	    | INSERT INTO tableName (col1,col2) VALUES (:col1,:col2)
	    */
		public function create(array $values):self
		{
			/// NEED REFACTOR: LOOK UPDATE METHOD
			$query = "INSERT INTO @tableName (@colName) VALUES (@bindedCol)";
			$this->extractBindedValue($values);
			$columnsName = $this->getColsName($values);
			$columnsSeparatedByCommma = $this->separateElementsByComma($columnsName);
			$bindedValues = [];
			foreach ($columnsName as $name) {
				$name = ':'. $name;
				array_push($bindedValues, $name);
			}
			$bindedColumnsSeparatedByCommma = $this->separateElementsByComma($bindedValues);

			$placeholdersValues = [
				"@tableName" => $this->table, 
				"@colName" => $columnsSeparatedByCommma,
				"@bindedCol" => $bindedColumnsSeparatedByCommma
			];
			$this->query = $this->replacePlaceholders($query, $placeholdersValues);
			$this->ReturnQuery();
			return $this;
		}
		
		/*
	    |--------------------------------------------------------------------------
	    | Build the UPDATE query
	    |--------------------------------------------------------------------------
	    |
	    | It sets the query like this:
	    | UPDATE tableName SET columnName=:columnName
	    | or
	    | UPDATE tableName SET col1=:col1,col2=:col2
	    */
		public function update(array $updateColumns):string
		{

			// NEED REFACTOR: LOOK UPDATE METHOD
			$query = "UPDATE @tableName SET @colName";
			$this->extractBindedValue($updateColumns);
			$columnsName = $this->getColsName($updateColumns);
			$bindedValues = [];
			foreach ($columnsName as $name) {
				$name = $name . '=:' . $name;
				array_push($bindedValues, $name);
			}
			$columnsSeparatedByCommma = $this->separateElementsByComma($bindedValues);
			$placeholdersValues = [
				"@tableName" => $this->table, 
				"@colName" => $columnsSeparatedByCommma
			];
			$query = $this->replacePlaceholders($query, $placeholdersValues);
			$this->query = $query . $this->query;

			return $this->ReturnQuery();
		}

		/*
		|--------------------------------------------------------------------------
		| Build the DELETE query
		|--------------------------------------------------------------------------
		|
		| It sets the query like this:
		| "DELETE FROM tableName" plus the already existing query
		| 
		|
		*/
		public function delete():string
		{	
			$query = "DELETE FROM @tableName";
			$placeholdersValues = [
				"@tableName" => $this->table
			];
			$query = $this->replacePlaceholders($query, $placeholdersValues);
			$this->query = $query . $this->query;

			return $this->ReturnQuery();
		}

		/*
	    |--------------------------------------------------------------------------
	    | Build the WHERE clause
	    |--------------------------------------------------------------------------
	    |
	    | It sets the query like this:
	    | Existing Query plus 'WHERE'
	    | 
	    | 
	    */
		public function where(string $column, string $param, $value):self
		{
			$query = " @whereOrAnd @column@param@bindedValue";
			$bindValue = ':' . $column;
			$this->bindedValues[$bindValue] = $value;
			$clause = "WHERE";
			if (count($this->bindedValues) >= 2) {
				$clause = "AND";
			}
			$placeholdersValues = [
				"@whereOrAnd" => $clause,
				"@column" => $column,
				"@param" => $param,
				"@bindedValue" => $bindValue
			];
			$query = $this->replacePlaceholders($query, $placeholdersValues);
			$this->query .= $query;

			return $this;
		}

		public function limit(int $value):self
		{	$query = ' LIMIT @value';
			$placeholdersValues = [
				'@value' => $value
			];
			$query = $this->replacePlaceholders($query, $placeholdersValues);
			$this->query.= $query;

			return $this;
		}

		public function get():array 
		{
			$this->setDbMethod('get');
			return $this->ReturnQuery();
		}

		public function first():array
		{
			$this->setDbMethod('first');
			return $this->ReturnQuery();
		}


		/*
	    |--------------------------------------------------------------------------
	    | Return the final query
	    |--------------------------------------------------------------------------
	    */
		public function ReturnQuery() 
		{
			return $this->query;
		}


		/*
		|--------------------------------------------------------------------------
		| Get column's name
		|--------------------------------------------------------------------------
		*/
		
		private function getColsName(array $cols):array 
		{
			$columnsName = array_keys($cols);
			return $columnsName;
		}

		/*
	    |--------------------------------------------------------------------------
	    | Set an array containing the column's name prefixed with ':' its own value. 
	    | The array will be used to bind the values before the query execution
	    |--------------------------------------------------------------------------
	    |
	    | It sets an array ($this->bindedValues) containing columns name as Key with 	 | the related value.
	    |
	    | 
	    */
		private function extractBindedValue(array $columns):void 
		{
			$loopIndex = 0;
			$values = '';
			foreach ($columns as $key => $value) {
					$loopIndex++;
					$columnName = $key;
					$bindValue = ':' . $columnName;
					$this->bindedValues[$bindValue] = $value;
			}
		}

		private function bindValues() 
		{
			foreach ($this->bindedValues as $key => $value) {
				$this->db->bind($key, $value);
			}
		}

		/*
		|--------------------------------------------------------------------------
		| Separate array's elements by a comma
		|--------------------------------------------------------------------------
		|
		| Given an array it returns a string with all the elements separated by a comma
		| Input: [3,4,5]
		| Output: '3,4,5'
		|
		*/
		private function separateElementsByComma(array $values):string 
		{
			// If the numbers of elements inside the $columns array is still greater than $loopIndex, a comma will separate the columns. Otherwise it will be set as empty. (title=:title,name=:name,etc.)
			$loopIndex = 0;
			$valuesSeparatedByCommma = '';
			foreach ($values as $value) {
				$loopIndex++;
				$separator = count($values) > $loopIndex ? ',' : '';
				$valuesSeparatedByCommma .= $value . $separator;
			}
			return $valuesSeparatedByCommma;
		}

		/*
		|--------------------------------------------------------------------------
		| Set $this->dbMethod to the given value. It will be passed to Database Class
		|--------------------------------------------------------------------------
		|
		| The Database Class can know which method must call.
		| Examples:
		| 'get' will call the get() method of Database Class.
		| 'first' will call the first() method of Database Class.
		| 'execute' will call the execute() method of Database Class.
		*/
		private function setDbMethod(string $method):void
		{
			$this->dbMethod = $method;
		}

		/*
		|--------------------------------------------------------------------------
		| Replace placeholders inside the given string with values of an array
		|--------------------------------------------------------------------------
		|
		| Input: "string with @placeholder"
		| Output: "string with value"
		| 
		|
		*/

		private function replacePlaceholders(string $query, array $values):string
		{
			$query = strtr($query, $values);
			
			return $query;
		}

	}