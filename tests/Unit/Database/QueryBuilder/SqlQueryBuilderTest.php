<?php

namespace Tests\Unit\Database\QueryBuilder;

use Exception;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Vista\Custom\CollectionContract;
use Vista\Database\DatabaseFactory;
use Vista\Database\DatabaseType;
use Vista\Database\DTO\Query;
use Vista\Database\Factory;
use Vista\Database\SqlDatabase;
use Vista\QueryBuilder\QueryBuilder;
use Vista\QueryBuilder\QueryBuilderObject;
use Vista\QueryBuilder\SqlQueryBuilder;

class SqlQueryBuilderTest extends TestCase
{
    private QueryBuilder $builder;
    private Factory $dbFactory;

    public function setUp(): void
    {
        parent::setUp();

        $this->builder = $this->createQueryBuilder();
    }

    public function testSelect(): void
    {
        $this->assertEquals(
            'SELECT *',
            $this->builder->select('*')->query(),
        );
        $this->assertEquals(
            'SELECT id, name',
            $this->builder->select(columns: ['id', 'name'])->query(),
        );
    }

    public function testSelectTwiceKeepsOnlyLastSelect(): void
    {
        $this->assertEquals(
            'SELECT *',
            $this->builder->select()->select()->query(),
        );
        $this->assertEquals(
            'SELECT id',
            $this->builder->select(columns: ['id', 'name'])->select(columns: ['id'])->query(),
        );
    }

    public function testFrom(): void
    {
        $this->assertEquals(
            'FROM users',
            $this->builder->from(table: 'users')->query(),
        );
    }

    public function testFromMultipleKeepsOnlyLastFrom(): void
    {
        $this->assertEquals(
            'FROM roles',
            $this->builder->from(table: 'users')->from(table: 'roles')->query(),
        );
    }

    public function testLeftJoin(): void
    {
        $this->assertEquals(
            'LEFT JOIN roles ON roles.id = users.role_id',
            $this->builder->leftJoin(
                table: 'roles',
                primaryKey: 'roles.id',
                foreignKey: 'users.role_id',
            )->query(),
        );
    }

    public function testLeftJoinMultiple(): void
    {
        $this->assertEquals(
            'LEFT JOIN roles ON roles.id = users.role_id '
            . 'LEFT JOIN permissions ON permissions.id = users.permission_id',
            $this->builder->leftJoin(
                table: 'roles',
                primaryKey: 'roles.id',
                foreignKey: 'users.role_id',
            )->leftJoin(
                table: 'permissions',
                primaryKey: 'permissions.id',
                foreignKey: 'users.permission_id',
            )->query(),
        );
    }

    public function testRightJoin(): void
    {
        $this->assertEquals(
            'RIGHT JOIN roles ON roles.id = users.role_id',
            $this->builder->rightJoin(
                table: 'roles',
                primaryKey: 'roles.id',
                foreignKey: 'users.role_id',
            )->query(),
        );
    }

    public function testRightJoinMultiple(): void
    {
        $this->assertEquals(
            'RIGHT JOIN roles ON roles.id = users.role_id '
            . 'RIGHT JOIN permissions ON permissions.id = users.permission_id',
            $this->builder->rightJoin(
                table: 'roles',
                primaryKey: 'roles.id',
                foreignKey: 'users.role_id',
            )->rightJoin(
                table: 'permissions',
                primaryKey: 'permissions.id',
                foreignKey: 'users.permission_id',
            )->query(),
        );
    }

    public function testInnerJoin(): void
    {
        $this->assertEquals(
            'INNER JOIN roles ON roles.id = users.role_id',
            $this->builder->innerJoin(
                table: 'roles',
                primaryKey: 'roles.id',
                foreignKey: 'users.role_id',
            )->query(),
        );
    }

    public function testInnerJoinMultiple(): void
    {
        $this->assertEquals(
            'INNER JOIN roles ON roles.id = users.role_id '
            . 'INNER JOIN permissions ON permissions.id = users.permission_id',
            $this->builder->innerJoin(
                table: 'roles',
                primaryKey: 'roles.id',
                foreignKey: 'users.role_id',
            )->innerJoin(
                table: 'permissions',
                primaryKey: 'permissions.id',
                foreignKey: 'users.permission_id',
            )->query(),
        );
    }

    #[TestWith(['where', 'WHERE'])]
    #[TestWith(['orWhere', 'WHERE'])]
    #[TestWith(['having', 'HAVING'])]
    #[TestWith(['orHaving', 'HAVING'])]
    public function testWhere(string $where, string $keyword): void
    {
        /** @var QueryBuilder $builder */
        $builder = $this->builder->$where('id', 1);
        $this->assertEquals(
            $keyword . ' id = :id',
            $builder->query()
        );
        $this->assertEquals(
            [':id' => 1],
            $builder->params()
        );
    }

    #[TestWith(['where', 'WHERE'])]
    #[TestWith(['orWhere', 'WHERE'])]
    #[TestWith(['having', 'HAVING'])]
    #[TestWith(['orHaving', 'HAVING'])]
    public function testWhereWithOperator(string $where, string $keyword): void
    {
        /** @var QueryBuilder $builder */
        $builder = $this->builder->$where('id', '>', 5);
        $this->assertEquals(
            $keyword . ' id > :id',
            $builder->query()
        );
        $this->assertEquals(
            [':id' => 5],
            $builder->params()
        );
    }

    #[TestWith(['where', 'WHERE', 'AND'])]
    #[TestWith(['orWhere', 'WHERE', 'OR'])]
    #[TestWith(['having', 'HAVING', 'AND'])]
    #[TestWith(['orHaving', 'HAVING', 'OR'])]
    public function testWhereMultiple(string $where, string $keyword, string $operator): void
    {
        /* @var QueryBuilder $builder */
        $this->builder->$where('id', 1)->$where('name', 'LIKE', '%John%');
        $this->assertEquals(
            $keyword . ' id = :id ' . $operator . ' name LIKE :name',
            $this->builder->query()
        );
        $this->assertEquals(
            [':id' => 1, ':name' => '%John%'],
            $this->builder->params()
        );
    }

    #[TestWith(['where', 'WHERE', 'AND'])]
    #[TestWith(['orWhere', 'WHERE', 'OR'])]
    #[TestWith(['having', 'HAVING', 'AND'])]
    #[TestWith(['orHaving', 'HAVING', 'OR'])]
    public function testWhereWithClosure(string $where, string $keyword, string $operator): void
    {
        /** @var QueryBuilder $builder */
        $builder = $this->builder->$where(function($query) use ($where) {
            $query->$where('name', 'LIKE', '%John%')
                ->$where(
                    'name',
                    'LIKE',
                    '%Doe%'
                );
        });
        $this->assertEquals(
            $keyword . ' ( name LIKE :name ' . $operator . ' name LIKE :name_0 )',
            $builder->query()
        );
        $this->assertEquals(
            [':name' => '%John%', ':name_0' => '%Doe%'],
            $builder->params()
        );
    }

    #[TestWith(['where', 'WHERE', 'AND'])]
    #[TestWith(['orWhere', 'WHERE', 'OR'])]
    #[TestWith(['having', 'HAVING', 'AND'])]
    #[TestWith(['orHaving', 'HAVING', 'OR'])]
    public function testWhereWithNestedClosure(string $where, string $keyword, string $operator): void
    {
        /** @var QueryBuilder $builder */
        $builder = $this->builder->$where(function(QueryBuilder $query) use ($where) {
            $query->$where(function(QueryBuilder $query) use ($where) {
                $query->$where('name', 'LIKE', '%John%');
            })->$where(
                'name',
                'LIKE',
                '%Doe%'
            );
        })->$where(
            'id',
            1
        );
        $this->assertEquals(
            $keyword . ' ( ( name LIKE :name ) '
            . $operator . ' name LIKE :name_0 ) '
            . $operator . ' id = :id',
            $builder->query()
        );
        $this->assertEquals(
            [':name' => '%John%', ':name_0' => '%Doe%', ':id' => 1],
            $builder->params()
        );
    }

    public function testWhereOrWhereHaving(): void
    {
        $this->builder->where('name', 'LIKE', '%John%')
            ->orWhere('name', 'LIKE', '%Doe%')
            ->where('id', '>', 5)
            ->having('name', 'LIKE', '%John%')
            ->orHaving('name', 'LIKE', '%Doe%');

        $this->assertEquals(
            'WHERE name LIKE :name OR name LIKE :name_0 AND id > :id '
            . 'HAVING name LIKE :name_1 OR name LIKE :name_2',
            $this->builder->query()
        );
        $this->assertEquals(
            [
                ':name' => '%John%',
                ':name_0' => '%Doe%',
                ':id' => 5,
                ':name_1' => '%John%',
                ':name_2' => '%Doe%',
            ],
            $this->builder->params()
        );
    }

    public function testOrderBy(): void
    {
        $this->assertEquals(
            'ORDER BY id DESC',
            $this->builder->orderBy(
                column: 'id',
                direction: 'DESC'
            )->query(),
        );
    }

    public function testOrderByMultiple(): void
    {
        $this->assertEquals(
            'ORDER BY id DESC, name ASC',
            $this->builder->orderBy(
                column: 'id',
                direction: 'DESC'
            )->orderBy(
                column: 'name'
            )->query(),
        );
    }

    public function testLimitNoOffset(): void
    {
        $this->assertEquals(
            'LIMIT 5',
            $this->builder->limit(
                limit: 5
            )->query(),
        );
    }

    public function testLimitWithOffset(): void
    {
        $this->assertEquals(
            'LIMIT 5 OFFSET 5',
            $this->builder->limit(
                limit: 5,
                offset: 5
            )->query(),
        );
    }

    public function testLimitMultipleKeepsOnlyLastLimit(): void
    {
        $this->assertEquals(
            'LIMIT 10',
            $this->builder->limit(
                limit: 5
            )->limit(
                limit: 10,
            )->query(),
        );
    }

    public function testGroupBy(): void
    {
        $this->assertEquals(
            'GROUP BY id',
            $this->builder->groupBy(
                groupBy: 'id'
            )->query(),
        );
    }

    public function testGroupByMultipleColumns(): void
    {
        $this->assertEquals(
            'GROUP BY id, name',
            $this->builder->groupBy(
                ['id', 'name']
            )->query(),
        );
    }

    public function testGroupByMultipleKeepsOnlyLastGroupBy(): void
    {
        $this->assertEquals(
            'GROUP BY name',
            $this->builder->groupBy(
                groupBy: 'id'
            )->groupBy(
                groupBy: 'name'
            )->query(),
        );
    }

    /**
     * @noinspection SqlResolve
     */
    public function testInsert(): void
    {
        $i = $this->builder->insert(
            table: 'users',
            values: [
                'name' => 'John',
                'email' => 'john@gmail.com',
            ]
        );
        $this->assertEquals(
            'INSERT INTO users (name, email) '
            . 'VALUES (:name, :email)',
            $i->query(),
        );
        $this->assertEquals(
            [
                ':name' => 'John',
                ':email' => 'john@gmail.com',
            ],
            $i->params()
        );
    }

    /**
     * @noinspection SqlResolve
     */
    public function testUpdate(): void
    {
        $u = $this->builder->update(
            table: 'users',
            values: [
                'name' => 'John',
                'email' => 'john@gmail.com',
            ]
        );
        $this->assertEquals(
            'UPDATE users SET name = :name, email = :email',
            $u->query(),
        );
        $this->assertEquals(
            [
                ':name' => 'John',
                ':email' => 'john@gmail.com',
            ],
            $u->params()
        );
    }

    /**
     * @noinspection SqlResolve
     */
    public function testDelete(): void
    {
        $d = $this->builder->delete()->from('users');
        $this->assertEquals(
            'DELETE FROM users',
            $d->query(),
        );
    }

    /**
     * @noinspection SqlResolve
     */
    public function testFullQuerySelect(): void
    {
        $s = $this->builder->select('*')
            ->from(table: 'users')
            ->leftJoin(
                table: 'roles',
                primaryKey: 'role_id',
                foreignKey: 'roles.id'
            )->rightJoin(
                table: 'tests',
                primaryKey: 'user_id',
                foreignKey: 'users.id'
            )->innerJoin(
                table: 'permissions',
                primaryKey: 'permission_id',
                foreignKey: 'permissions.id'
            )->where('created_at', '2021-01-01')
            ->orderBy('id', 'DESC')
            ->limit(5, 5)
            ->groupBy(['id', 'name'])
            ->having('name', 'LIKE', '%John%');

        $this->assertEquals(
            'SELECT * FROM users '
            . 'LEFT JOIN roles ON role_id = roles.id '
            . 'RIGHT JOIN tests ON user_id = users.id '
            . 'INNER JOIN permissions ON permission_id = permissions.id '
            . 'WHERE created_at = :created_at '
            . 'GROUP BY id, name '
            . 'HAVING name LIKE :name '
            . 'ORDER BY id DESC '
            . 'LIMIT 5 OFFSET 5',
            $s->query()
        );
        $this->assertEquals(
            [
                ':created_at' => '2021-01-01',
                ':name' => '%John%',
            ],
            $s->params()
        );
    }

    /**
     * @noinspection SqlResolve
     */
    public function testFullQueryUpdate(): void
    {
        $u = $this->builder->update(
            table: 'users',
            values: [
                'name' => 'John',
                'email' => 'john@gmail.com',
            ]
        )->where('id', 1);

        $this->assertEquals(
            'UPDATE users '
            . 'SET name = :name, email = :email '
            . 'WHERE id = :id',
            $u->query()
        );
        $this->assertEquals(
            [
                ':name' => 'John',
                ':email' => 'john@gmail.com',
                ':id' => 1,
            ],
            $u->params()
        );
    }

    /**
     * @noinspection SqlResolve
     */
    public function testFullQueryDelete(): void
    {
        $u = $this->builder->delete()->from('users')->where('id', 1);

        $this->assertEquals(
            'DELETE FROM users WHERE id = :id',
            $u->query()
        );
        $this->assertEquals(
            [
                ':id' => 1,
            ],
            $u->params()
        );
    }

    public function testGet(): void
    {
        $this->initializeData();
        $this->builder->select()->from('tests');
        $this->builder->setModelClass(className: Test::class);
        $result = $this->builder->get();
        $this->assertInstanceOf(CollectionContract::class, $result);
        $this->assertCount(3, $this->builder->get());
    }

    public function testAll(): void
    {
        $this->initializeData();
        $this->builder->select()->from('tests');
        $this->builder->setModelClass(className: Test::class);

        $result = $this->builder->all();
        $this->assertInstanceOf(CollectionContract::class, $result);
        $this->assertCount(3, $this->builder->get());
    }

    public function testFirst(): void
    {
        $this->initializeData();
        $this->builder->setModelClass(className: Test::class);

        $result = $this->builder->select()->from('tests')->first();

        $this->assertInstanceOf(Test::class, $result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals('test1', $result->name);
    }

    public function testLast(): void
    {
        $this->initializeData();
        $this->builder->setModelClass(className: Test::class);

        $result = $this->builder->select()->from('tests')->last();

        $this->assertInstanceOf(Test::class, $result);
        $this->assertEquals(3, $result->id);
        $this->assertEquals('test3', $result->name);
    }

    public function testFind(): void
    {
        $this->initializeData();
        $this->builder->setModelClass(className: Test::class);

        $result = $this->builder->select()->from('tests')->find(2);

        $this->assertInstanceOf(Test::class, $result);
        $this->assertEquals(2, $result->id);
        $this->assertEquals('test2', $result->name);
    }

    public function testCount(): void
    {
        $this->initializeData();
        $this->builder->setModelClass(className: Test::class);

        $result = $this->builder->select()->from('tests')->count();

        $this->assertEquals(3, $result);
    }

    public function testExists(): void
    {
        $this->initializeData();
        $this->builder->setModelClass(className: Test::class);

        $result = $this->builder->select()->from('tests')->where('id', 3)->exists();
        $this->assertTrue($result);

        $result = $this->builder->select()->from('tests')->where('id', 5)->exists();
        $this->assertFalse($result);
    }

    public function testSave(): void
    {
        $this->initializeData();
        $this->builder->delete()->from('tests')->save();
        $this->builder->insert('tests', ['name' => 'test']);
        $this->assertTrue($this->builder->save());
        $this->assertEmpty($this->builder->query());
    }

    public function testLastInsertId(): void
    {
        $this->initializeData();

        $this->builder->insert('tests', ['name' => 'test'])->save();

        $result = $this->builder->lastInsertId();
        $this->assertEquals(4, $result);
    }

    private function createQueryBuilder(): QueryBuilder
    {
        $this->dbFactory = new DatabaseFactory(
            new SqlDatabase(
                DatabaseType::SQLITE,
                ':memory:',
            )
        );

        return new SqlQueryBuilder(new QueryBuilderObject(), $this->dbFactory);
    }

    /**
     * @noinspection SqlResolve
     */
    private function initializeData(): void
    {
        try {
            $db = $this->dbFactory->create();
            $db->executeRaw(
                query: new Query(
                    query: 'CREATE TABLE tests (id INTEGER PRIMARY KEY, name TEXT UNIQUE NOT NULL)'
                )
            );
            $db->executeRaw(
                query: new Query(
                    query: 'INSERT INTO tests (name) VALUES (\'test1\'), (\'test2\'), (\'test3\')'
                )
            );
        } catch (Exception $e) {
            $this->fail(message: $e->getMessage());
        }
    }
}

class Test
{
}
