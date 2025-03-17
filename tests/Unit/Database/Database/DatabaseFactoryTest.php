<?php

namespace Tests\Unit\Database\Database;

use InvalidArgumentException;
use Mockery as m;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Vista\Database\Database;
use Vista\Database\DatabaseFactory;
use Vista\Database\DatabaseType;
use Vista\Database\DTO\DatabaseConfiguration;
use Vista\Database\Factory;
use Vista\Database\SqlDatabase;

class DatabaseFactoryTest extends TestCase
{
    private Factory $factory;

    public function setUp(): void
    {
        parent::setUp();

        $this->factory = new DatabaseFactory(null, DatabaseConfiguration::fromArray([
            'DB_TYPE' => DatabaseType::SQLITE,
            'DB_NAME' => ':memory:',
        ]));
    }

    public function testCreateDefault(): void
    {
        $database = $this->factory->create();
        $this->assertInstanceOf(Database::class, $database);
        $this->assertInstanceOf(SqlDatabase::class, $database);
    }

    public function testCreateWithDefaultDatabase(): void
    {
        $database = m::mock(Database::class);

        $factory = new DatabaseFactory($database);
        $this->assertInstanceOf(Database::class, $factory->create());
        $this->assertSame($database, $factory->create());
        $this->assertNotSame($database, $this->factory->create());
    }

    #[TestWith(['sql', SqlDatabase::class])]
    public function testCreateWithExistingDatabaseTypeReturnsProperInstance(string $type, string $className): void
    {
        $database = $this->factory->create($type);
        $this->assertInstanceOf($className, $database);
    }

    #[TestWith(['nosql'])]
    public function testCreateWithWrongDatabaseTypeThrowsInvalidArgumentException(string $type): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid database type: ' . $type . '.');
        $this->factory->create($type);
    }
}
