<?php

namespace Tests\Unit\Database\QueryBuilder;

use PHPUnit\Framework\TestCase;
use Vista\QueryBuilder\QueryBuilderObject;

class QueryBuilderObjectTest extends TestCase
{
    private QueryBuilderObject $obj;

    /**
     * @noinspection SqlResolve
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->obj = new QueryBuilderObject();
        $this->obj->select = 'SELECT *';
        $this->obj->from = 'FROM tests';
        $this->obj->leftJoin = 'LEFT JOIN tests_2 ON test_id = tests.id';
        $this->obj->rightJoin = 'RIGHT JOIN tests_3 ON tests_3.test_id = tests.id';
        $this->obj->innerJoin = 'INNER JOIN tests_4 ON tests_4.test_id = tests.id';
        $this->obj->where = 'WHERE id = :id';
        $this->obj->groupBy = 'GROUP BY id';
        $this->obj->having = 'HAVING id = :id';
        $this->obj->orderBy = 'ORDER BY id';
        $this->obj->limit = 'LIMIT 1';
        $this->obj->params = ['name' => 'test', 'id' => 1];
    }

    public function testSetParam()
    {
        $this->obj->setParam('key', 'value');

        $this->assertEquals('value', $this->obj->params['key']);
    }

    /**
     * @noinspection SqlResolve
     */
    public function testGetSelect(): void
    {
        $query = 'SELECT * FROM tests '
            . 'LEFT JOIN tests_2 ON test_id = tests.id '
            . 'RIGHT JOIN tests_3 ON tests_3.test_id = tests.id '
            . 'INNER JOIN tests_4 ON tests_4.test_id = tests.id '
            . 'WHERE id = :id '
            . 'GROUP BY id '
            . 'HAVING id = :id '
            . 'ORDER BY id LIMIT 1';

        $this->assertEquals($query, $this->obj->get());
    }

    /**
     * @noinspection SqlResolve
     */
    public function testGetInsert(): void
    {
        $query = 'INSERT INTO tests (name) VALUES (:name)';
        $this->obj->insert = $query;
        $this->obj->update = 'UPDATE tests SET name = :name';
        $this->obj->delete = 'DELETE';

        $this->assertEquals($query, $this->obj->get());
    }

    /**
     * @noinspection SqlResolve
     */
    public function testGetUpdate(): void
    {
        $query = 'UPDATE tests2 SET name = tests.name FROM tests LEFT JOIN tests_2 ON test_id = tests.id RIGHT JOIN tests_3 ON tests_3.test_id = tests.id INNER JOIN tests_4 ON tests_4.test_id = tests.id WHERE id = :id';
        $this->obj->update = 'UPDATE tests2 SET name = tests.name';
        $this->obj->delete = 'DELETE';

        $this->assertEquals($query, $this->obj->get());
    }

    /**
     * @noinspection SqlResolve
     */
    public function testGetDelete(): void
    {
        $query = 'DELETE FROM tests WHERE id = :id';
        $this->obj->delete = 'DELETE';

        $this->assertEquals($query, $this->obj->get());
    }
}
