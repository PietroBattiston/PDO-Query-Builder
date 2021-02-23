<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use QueryBuilder\QueryBuilder;




final class QueryBuilderTest extends TestCase
{
    public function setUp():void {
        $this->QB = new QueryBuilder;
        $this->tableName = 'myTable';
        $this->QB->table = $this->tableName;
    }

    public function test_a_table_can_be_selected(): void
    {
        $this->assertEquals($this->QB->table, $this->tableName);
    }

    public function test_CREATE_query_can_be_built(): void
    {
        $this->QB->table = $this->tableName;
        $values = [
            'title' => 'new post'
        ];
        $bindedTitle = ':title';
        $this->QB->create($values);
        $expectedQuery = "INSERT INTO " .$this->tableName . " (title) VALUES (:title)";
        $this->assertEquals($this->QB->query, $expectedQuery);
        $this->assertTrue(is_array($this->QB->bindedValues));
        $this->assertArrayHasKey($bindedTitle, $this->QB->bindedValues);
        $this->assertTrue(empty($this->QB->dbMethod));

    }

    public function test_CREATE_query_can_be_built_with_multiple_params(): void
    {
        $this->QB->table = $this->tableName;
        $values = [
            'title' => 'new post',
            'id' => 32
        ];
        $bindedTitle = ':title';
        $bindedId = ':id';
        $this->QB->create($values);
        $expectedQuery = "INSERT INTO " .$this->tableName . " (title,id) VALUES (:title,:id)";
        $this->assertEquals($this->QB->query, $expectedQuery);
        $this->assertTrue(is_array($this->QB->bindedValues));
        $this->assertArrayHasKey($bindedTitle, $this->QB->bindedValues);
        $this->assertArrayHasKey($bindedId, $this->QB->bindedValues);
        $this->assertTrue(empty($this->QB->dbMethod));


    }

    public function test_UPDATE_query_can_be_built(): void
    {
         $this->QB->table = $this->tableName;
         
         $newTitle  = 'myTitle';
         $this->QB->update([
            'title' => $newTitle
         ]);
         $bindedTitle = ':title';
         $expectedQuery =  'UPDATE ' .$this->tableName . ' SET title=:title';
         $this->assertEquals($this->QB->query, $expectedQuery);
         $this->assertTrue(is_array($this->QB->bindedValues));
         $this->assertArrayHasKey($bindedTitle, $this->QB->bindedValues);
         $this->assertTrue(empty($this->QB->dbMethod));

    }

    public function test_UPDATE_query_must_contains_multiple_binded_elements_divided_by_a_comma(): void
    {

         $this->QB->table = $this->tableName;
         

         $newTitle  = 'myTitle';
         $this->QB->update([
            'title' => $newTitle,
            'id' => 32,
            'name' => 'myname',
            'age' => 29
         ]);
         $bindedTitle = ':title';
         $bindedId = ':id';
         $expectedQuery = 'UPDATE ' . $this->tableName . ' SET title=:title,id=:id,name=:name,age=:age';
         $this->assertEquals($this->QB->query, $expectedQuery);
         $this->assertArrayHasKey($bindedTitle, $this->QB->bindedValues);
         $this->assertArrayHasKey($bindedId, $this->QB->bindedValues);
         $this->assertTrue(empty($this->QB->dbMethod));

    }

    public function test_DELETE_query_can_be_built(): void
    {
         $this->QB->table = $this->tableName;
         
         $this->QB->delete();
         $expectedQuery = 'DELETE FROM ' . $this->tableName;
         $this->assertEquals($this->QB->query, $expectedQuery);
         $this->assertTrue(empty($this->QB->dbMethod));

    }

    public function test_WHERE_clause_can_be_built(): void
    {
         $this->QB->table = $this->tableName;
         $this->QB->where('id', '=', 32);
         $expectedQuery = ' WHERE id=:id';
         $this->assertEquals($this->QB->query, $expectedQuery);
    }

    public function test_multiple_WHERE_clauses_must_be_separated_by_AND(): void
    {
         $this->QB->table = $this->tableName;
         $this->QB->where('id', '=', 32);
         $this->QB->where('title', '=', 'title');
         $expectedQuery = ' WHERE id=:id AND title=:title';
         $this->assertEquals($this->QB->query, $expectedQuery);
    }

    public function test_column_can_be_selected(): void {

         $this->QB->table = $this->tableName;
         $this->QB->select(['title']);
         $expectedQuery = 'SELECT title FROM ' . $this->tableName;
         $this->assertEquals($this->QB->query, $expectedQuery);
    }

    public function test_selecting_multiple_column_must_return_col_separated_by_comma(): void
    {
         $this->QB->table = $this->tableName;
         $this->QB->select(['title, id']);
         $expectedQuery = 'SELECT title, id FROM ' . $this->tableName;
         $this->assertEquals($this->QB->query, $expectedQuery);
    }

    public function test_LIMIT_clause_can_be_built(): void
    {
         $this->QB->limit(2);
         $expectedQuery = ' LIMIT 2';
         $this->assertEquals($this->QB->query, $expectedQuery);
    }

    // public function test_calling_Get_Method_must_set_the_var_dbMethod(): void {
    //      $this->QB = new DB($this->dbMock);
    //      $this->QB->get();
    //      $this->assertEquals($this->QB->dbMethod, 'get');
    // }

    // public function test_calling_First_Method_must_set_the_var_dbMethod(): void {
    //      $this->QB = new DB($this->dbMock);
    //      $this->QB->first();
    //      $this->assertEquals($this->QB->dbMethod, 'first');
    // }
    // public function test_replacePlaceholders(): void {
    //      $this->QB = new DB($this->dbMock);
    //      $query = "Hello @name today is @day";
    //      $array = [
    //         "@name" => 'pietro',
    //         "@day" => 'saturday'
    //      ];
    //      $expectedQuery = "Hello pietro today is saturday";
    //      $replace = $this->QB->replacePlaceholders($query, $array);
    //      $this->assertEquals($replace, $expectedQuery);
    // }

}
