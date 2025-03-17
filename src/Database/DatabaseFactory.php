<?php

namespace Vista\Database;

use InvalidArgumentException;
use Vista\Database\DTO\DatabaseConfiguration;

/**
 * A factory for creating database instances.
 */
readonly class DatabaseFactory implements Factory
{
    public function __construct(
        private ?Database $defaultDatabase = null,
        private ?DatabaseConfiguration $configuration = null
    ) {
    }

    /**
     * Creates a new database instance.
     */
    public function create(string $type = 'sql'): Database
    {
        if ($this->defaultDatabase !== null) {
            return $this->defaultDatabase;
        }

        return match ($type) {
            'sql' => $this->getSqlDatabaseInstance(),
            default => throw new InvalidArgumentException("Invalid database type: $type."),
        };
    }

    private function getSqlDatabaseInstance(): Database
    {
        $configuration = $this->configuration;

        if ($configuration === null) {
            $configuration = DatabaseConfiguration::fromConfigFile(__DIR__ . '/../../../../../config/database.php');
        }

        if ($configuration === null) {
            throw new InvalidArgumentException('Database configuration not provided');
        }

        return new SqlDatabase(
            $configuration->dbType,
            $configuration->dbName,
            $configuration->dbHost,
            $configuration->dbPort,
            $configuration->dbUser,
            $configuration->dbPass
        );
    }
}
