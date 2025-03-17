<?php

namespace Tests\Unit\Database\Database;

use Exception;
use PDOException;
use PHPUnit\Framework\TestCase;
use Vista\Database\Database;
use Vista\Database\DatabaseFactory;
use Vista\Database\DatabaseType;
use Vista\Database\DTO\DatabaseConfiguration;
use Vista\Database\DTO\Query;

class SqlDatabaseTest extends TestCase
{
    private Database $db;

    public function setUp(): void
    {
        parent::setUp();
        $this->db = $this->createDatabase();
        $this->initializeData();
    }

    public function testInstanceOfDatabase()
    {
        $this->assertInstanceOf(Database::class, $this->db);
    }

    /**
     * @noinspection SqlResolve
     */
    public function testExecuteRaw()
    {
        $this->assertTrue(
            $this->db->executeRaw(
                new Query(
                    query: 'CREATE TABLE test (id INTEGER PRIMARY KEY)'
                )
            )
        );
    }

    /**
     * @noinspection SqlResolve
     */
    public function testSelectAll(): void
    {
        $tests = $this->db->select(
            className: TestTable::class,
            query: new Query('SELECT * FROM tests'),
        );

        $this->assertCount(expectedCount: 3, haystack: $tests);

        foreach ($tests as $i => $test) {
            $this->assertEquals(expected: $i + 1, actual: $test->id);
            $this->assertEquals(expected: 'test' . ($i + 1), actual: $test->name);
        }
    }

    /**
     * @noinspection SqlResolve
     */
    public function testSelectWhereWithOneParam(): void
    {
        $tests = $this->db->select(
            className: TestTable::class,
            query: new Query(
                query: 'SELECT * FROM tests WHERE id = :id',
                params: [':id' => 2],
            )
        );

        $this->assertCount(expectedCount: 1, haystack:$tests);

        $test = $tests->first();
        $this->assertEquals(expected: 2, actual: $test->id);
        $this->assertEquals(expected: 'test2', actual: $test->name);
    }

    /**
     * @noinspection SqlResolve
     */
    public function testSelectWhereWithMultipleParams(): void
    {
        $tests = $this->db->select(
            className: TestTable::class,
            query: new Query(
                query: 'SELECT * FROM tests WHERE id IN (:id1, :id2)',
                params: [':id1' => 1, ':id2' => 3],
            )
        );

        $this->assertCount(expectedCount: 2, haystack:$tests);

        $this->assertEquals(expected: 1, actual: $tests->get(0)->id);
        $this->assertEquals(expected: 'test1', actual: $tests->get(0)->name);

        $this->assertEquals(expected: 3, actual: $tests->get(1)->id);
        $this->assertEquals(expected: 'test3', actual: $tests->get(1)->name);
    }

    /**
     * @noinspection SqlResolve
     */
    public function testSelectWhereWithWildCardStringParams(): void
    {
        $tests = $this->db->select(
            className: TestTable::class,
            query: new Query(
                query: 'SELECT * FROM tests WHERE name LIKE :name',
                params: [':name' => '%2%'],
            )
        );

        $this->assertCount(expectedCount: 1, haystack: $tests);

        $test = $tests->first();
        $this->assertEquals(expected: 2, actual: $test->id);
        $this->assertEquals(expected: 'test2', actual: $test->name);
    }

    /**
     * @noinspection SqlResolve
     */
    public function testSelectWhereNoRecordsMatchReturnsEmptyArray(): void
    {
        $tests = $this->db->select(
            className: TestTable::class,
            query: new Query(
                query: 'SELECT * FROM tests WHERE name = :name',
                params: [':name' => 'test4'],
            )
        );

        $this->assertCount(expectedCount: 0, haystack: $tests);
    }

    /**
     * @noinspection SqlResolve
     */
    public function testSelectWithInvalidTableTrowsPdoException(): void
    {
        $this->expectException(PDOException::class);
        $this->expectExceptionMessage(message: 'SQLSTATE[HY000]: General error: 1 no such table: invalid');
        $this->db->select(
            className: TestTable::class,
            query: new Query(
                query: 'SELECT * FROM invalid'
            ),
        );
    }

    /**
     * @noinspection SqlResolve
     */
    public function testSelectWithInvalidColumnThrowsPdoException(): void
    {
        $this->expectException(PDOException::class);
        $this->expectExceptionMessage(message: 'SQLSTATE[HY000]: General error: 1 no such column: test');
        $this->db->select(
            className: TestTable::class,
            query: new Query(
                query:'SELECT test FROM tests'
            ),
        );
    }

    /**
     * @noinspection SqlResolve
     */
    public function testSelectAssocAll()
    {
        $tests = $this->db->selectAssoc(
            query: new Query(
                query: 'SELECT * FROM tests'
            )
        );

        $this->assertCount(expectedCount: 3, haystack: $tests);

        foreach ($tests as $i => $test) {
            $this->assertEquals(expected: $i + 1, actual: $test['id']);
            $this->assertEquals(expected: 'test' . ($i + 1), actual: $test['name']);
        }
    }

    /**
     * @noinspection SqlResolve
     */
    public function testSelectAssocWhereWithOneParam(): void
    {
        $tests = $this->db->selectAssoc(
            query: new Query(
                query: 'SELECT * FROM tests WHERE id = :id',
                params: [':id' => 2]
            )
        );

        $this->assertCount(expectedCount: 1, haystack:$tests);

        $test = $tests[0];
        $this->assertEquals(expected: 2, actual: $test['id']);
        $this->assertEquals(expected: 'test2', actual: $test['name']);
    }

    /**
     * @noinspection SqlResolve
     */
    public function testSelectAssocWhereWithMultipleParams(): void
    {
        $tests = $this->db->selectAssoc(
            query: new Query(
                query: 'SELECT * FROM tests WHERE id IN (:id1, :id2)',
                params: [':id1' => 1, ':id2' => 3]
            )
        );

        $this->assertCount(expectedCount: 2, haystack:$tests);

        $this->assertEquals(expected: 1, actual: $tests[0]['id']);
        $this->assertEquals(expected: 'test1', actual: $tests[0]['name']);

        $this->assertEquals(expected: 3, actual: $tests[1]['id']);
        $this->assertEquals(expected: 'test3', actual: $tests[1]['name']);
    }

    /**
     * @noinspection SqlResolve
     */
    public function testSelectAssocWhereWithWildCardStringParams(): void
    {
        $tests = $this->db->selectAssoc(
            query: new Query(
                query: 'SELECT * FROM tests WHERE name LIKE :name',
                params: [':name' => '%2%']
            )
        );

        $this->assertCount(expectedCount: 1, haystack:$tests);

        $test = $tests[0];
        $this->assertEquals(expected: 2, actual: $test['id']);
        $this->assertEquals(expected: 'test2', actual: $test['name']);
    }

    /**
     * @noinspection SqlResolve
     */
    public function testSelectAssocWhereNoRecordsMatchReturnsEmptyArray(): void
    {
        $tests = $this->db->selectAssoc(
            query: new Query(
                query: 'SELECT * FROM tests WHERE name = :name',
                params: [':name' => 'test4']
            )
        );

        $this->assertCount(expectedCount: 0, haystack: $tests);
    }

    /**
     * @noinspection SqlResolve
     */
    public function testSelectAssocWithInvalidTableTrowsPdoException(): void
    {
        $this->expectException(exception: PDOException::class);
        $this->expectExceptionMessage(message: 'SQLSTATE[HY000]: General error: 1 no such table: invalid');
        $this->db->selectAssoc(
            query: new Query(
                query: 'SELECT * FROM invalid'
            )
        );
    }

    /**
     * @noinspection SqlResolve
     */
    public function testSelectAssocWithInvalidColumnThrowsPdoException(): void
    {
        $this->expectException(exception: PDOException::class);
        $this->expectExceptionMessage(message: 'SQLSTATE[HY000]: General error: 1 no such column: test');
        $this->db->selectAssoc(
            query: new Query(
                query: 'SELECT test FROM tests'
            )
        );
    }

    /**
     * @noinspection SqlResolve
     */
    public function testInsertNewRecord(): void
    {
        $this->db->insert(
            query: new Query(
                query: 'INSERT INTO tests (name) VALUES (:name)',
                params: [':name' => 'test4']
            )
        );

        $tests = $this->db->select(
            className: TestTable::class,
            query: new Query(
                query: 'SELECT * FROM tests'
            )
        );

        $this->assertCount(expectedCount: 4, haystack: $tests);

        $this->assertEquals(expected: 4, actual: $tests->get(3)->id);
        $this->assertEquals(expected: 'test4', actual: $tests->get(3)->name);
    }

    /**
     * @noinspection SqlResolve
     */
    public function testInsertExistingRecordThrowsPdoException(): void
    {
        $this->expectException(exception: PDOException::class);
        $this->expectExceptionMessage(message: 'SQLSTATE[23000]: Integrity constraint violation: 19 UNIQUE constraint failed: tests.name');

        $this->db->insert(
            query: new Query(
                query: 'INSERT INTO tests (name) VALUES (:name)',
                params: [':name' => 'test2'],
            )
        );
    }

    /**
     * @noinspection SqlResolve
     */
    public function testInsertWithInvalidTableThrowsPdoException(): void
    {
        $this->expectException(exception: PDOException::class);
        $this->expectExceptionMessage(message: 'SQLSTATE[HY000]: General error: 1 no such table: invalid');
        $this->db->insert(
            query: new Query(
                query: 'INSERT INTO invalid (name) VALUES (:name)',
                params: [':name' => 'test2'],
            )
        );
    }

    /**
     * @noinspection SqlResolve
     */
    public function testInsertWithInvalidColumnThrowsPdoException(): void
    {
        $this->expectException(exception: PDOException::class);
        $this->expectExceptionMessage(message: 'SQLSTATE[HY000]: General error: 1 table tests has no column named test');
        $this->db->insert(
            query: new Query(
                query: 'INSERT INTO tests (test) VALUES (:test)',
                params: [':test' => 'test2'],
            )
        );
    }

    /**
     * @noinspection SqlResolve
     */
    public function testUpdateExistingRecord(): void
    {
        $result = $this->db->update(
            query: new Query(
                query: 'UPDATE tests SET name = :name WHERE id = :id',
                params: [':name' => 'test4', ':id' => 2]
            )
        );

        $test = $this->db->select(
            className: TestTable::class,
            query: new Query(
                query: 'SELECT * FROM tests WHERE id = :id',
                params: [':id' => 2]
            )
        );

        $this->assertTrue(condition: $result);
        $this->assertEquals(expected: 2, actual: $test->first()->id);
        $this->assertEquals(expected: 'test4', actual: $test->first()->name);
    }

    /**
     * @noinspection SqlResolve
     */
    public function testUpdateNonExistingRecordDoesNotInsertNewRecord(): void
    {
        $this->db->update(
            query: new Query(
                query: 'UPDATE tests SET name = :name WHERE id = :id',
                params: [':name' => 'test4', ':id' => 5]
            )
        );

        $test = $this->db->select(
            className: TestTable::class,
            query: new Query(
                query: 'SELECT * FROM tests WHERE id = :id',
                params: [':id' => 5]
            )
        );

        $this->assertCount(expectedCount: 0, haystack: $test);
    }

    /**
     * @noinspection SqlResolve
     */
    public function testUpdateWithInvalidTableThrowsPdoException(): void
    {
        $this->expectException(PDOException::class);
        $this->expectExceptionMessage('SQLSTATE[HY000]: General error: 1 no such table: invalid');
        $this->db->update(
            query: new Query(
                query: 'UPDATE invalid SET name = :name WHERE id = :id',
                params: [':name' => 'test4', ':id' => 5]
            )
        );
    }

    /**
     * @noinspection SqlResolve
     */
    public function testUpdateWithInvalidColumnThrowsPdoException(): void
    {
        $this->expectException(PDOException::class);
        $this->expectExceptionMessage('SQLSTATE[HY000]: General error: 1 no such column: test');
        $this->db->update(
            query: new Query(
                query: 'UPDATE tests SET test = :name WHERE id = :id',
                params: [':name' => 'test4', ':id' => 5]
            )
        );
    }

    /**
     * @noinspection SqlResolve
     */
    public function testDelete(): void
    {
        $this->db->delete(
            query: new Query(
                query: 'DELETE FROM tests WHERE id = :id',
                params: [':id' => 2]
            )
        );

        $tests = $this->db->select(
            className: TestTable::class,
            query: new Query(
                query: 'SELECT * FROM tests'
            )
        );

        $this->assertCount(expectedCount: 2, haystack:$tests);

        $this->assertEquals(expected: 1, actual: $tests->get(0)->id);
        $this->assertEquals(expected: 'test1', actual: $tests->get(0)->name);

        $this->assertEquals(expected: 3, actual: $tests->get(1)->id);
        $this->assertEquals(expected: 'test3', actual: $tests->get(1)->name);
    }

    /**
     * @noinspection SqlResolve
     */
    public function testDeleteNonExistingRecordDoesNotAffectTable(): void
    {
        $this->db->delete(
            query: new Query(
                query: 'DELETE FROM tests WHERE id = :id',
                params: [':id' => 10]
            )
        );

        $tests = $this->db->select(
            className: TestTable::class,
            query: new Query(
                query: 'SELECT * FROM tests'
            )
        );

        $this->assertCount(expectedCount: 3, haystack:$tests);
    }

    /**
     * @noinspection SqlResolve
     */
    public function testDeleteWithInvalidTableThrowsPdoException(): void
    {
        $this->expectException(PDOException::class);
        $this->expectExceptionMessage('SQLSTATE[HY000]: General error: 1 no such table: invalid');
        $this->db->delete(
            query: new Query(
                query: 'DELETE FROM invalid WHERE id = :id',
                params: [':id' => 10]
            )
        );
    }

    /**
     * @noinspection SqlResolve
     */
    public function testDeleteWithInvalidColumnThrowsPdoException(): void
    {
        $this->expectException(PDOException::class);
        $this->expectExceptionMessage('SQLSTATE[HY000]: General error: 1 near "test": syntax error');
        $this->db->delete(
            query: new Query(
                query: 'DELETE test FROM tests WHERE id = :id',
                params: [':id' => 10],
            )
        );
    }

    private function createDatabase(): Database
    {
        $defaultDatabase = null;
        $config = [
            'DB_TYPE' => DatabaseType::SQLITE,
            'DB_NAME' => ':memory:',
        ];
        $factory = new DatabaseFactory($defaultDatabase, DatabaseConfiguration::fromArray($config));

        return $factory->create();
    }

    /**
     * @noinspection SqlResolve
     */
    private function initializeData(): void
    {
        try {
            $this->db->executeRaw(
                query: new Query(
                    query: 'CREATE TABLE tests (id INTEGER PRIMARY KEY, name TEXT UNIQUE NOT NULL)'
                )
            );
            $this->db->executeRaw(
                query: new Query(
                    query: 'INSERT INTO tests (name) VALUES (\'test1\'), (\'test2\'), (\'test3\')'
                )
            );
        } catch (Exception $e) {
            $this->fail(message: $e->getMessage());
        }
    }
}

class TestTable
{
    public int $id;
    public string $name;
}
