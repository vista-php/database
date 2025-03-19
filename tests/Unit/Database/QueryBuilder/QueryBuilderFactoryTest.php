<?php

namespace Tests\Unit\Database\QueryBuilder;

use InvalidArgumentException;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Vista\Database\DatabaseFactory;
use Vista\Database\DatabaseType;
use Vista\Database\DTO\DatabaseConfiguration;
use Vista\QueryBuilder\Factory;
use Vista\QueryBuilder\QueryBuilder;
use Vista\QueryBuilder\QueryBuilderFactory;
use Vista\QueryBuilder\QueryBuilderObject;
use Vista\QueryBuilder\SqlQueryBuilder;

class QueryBuilderFactoryTest extends TestCase
{
    private Factory $factory;

    public function setUp(): void
    {
        parent::setUp();

        $dbFactory = new DatabaseFactory(null, DatabaseConfiguration::fromArray([
            'DB_TYPE' => DatabaseType::SQLITE,
            'DB_NAME' => ':memory:',
        ]));
        $queryBuilder = new SqlQueryBuilder(new QueryBuilderObject(), $dbFactory);
        $this->factory = new QueryBuilderFactory($queryBuilder);
    }

    public function testCreateDefault(): void
    {
        $database = $this->factory->create();
        $this->assertInstanceOf(QueryBuilder::class, $database);
        $this->assertInstanceOf(SqlQueryBuilder::class, $database);
    }

    public function testCreateWithDefaultDatabase(): void
    {
        /** @var MockInterface|QueryBuilder $queryBuilder */
        $queryBuilder = m::mock(QueryBuilder::class);

        $factory = new QueryBuilderFactory($queryBuilder);
        $this->assertInstanceOf(QueryBuilder::class, $factory->create());
        $this->assertSame($queryBuilder, $factory->create());
        $this->assertNotSame($queryBuilder, $this->factory->create());
    }

    #[TestWith(['sql', SqlQueryBuilder::class])]
    public function testCreateWithExistingDatabaseTypeReturnsProperInstance(string $type, string $className): void
    {
        $database = $this->factory->create($type);
        $this->assertInstanceOf($className, $database);
    }

    #[TestWith(['nosql'])]
    public function testCreateWithWrongDatabaseTypeThrowsInvalidArgumentException(string $type): void
    {
        $factory = new QueryBuilderFactory();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid query builder type: ' . $type . '.');
        $factory->create($type);
    }
}
