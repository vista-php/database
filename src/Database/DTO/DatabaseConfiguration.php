<?php

namespace Vista\Database\DTO;

use InvalidArgumentException;
use Vista\Database\DatabaseType;

/**
 * Data transfer object for database configuration.
 *
 * @param DatabaseType $dbType
 * @param string       $dbName
 * @param ?string      $dbHost
 * @param ?int         $dbPort
 * @param ?string      $dbUser
 * @param ?string      $dbPass
 */
final readonly class DatabaseConfiguration
{
    public function __construct(
        private DatabaseType $dbType,
        private string $dbName,
        private ?string $dbHost = null,
        private ?int $dbPort = null,
        private ?string $dbUser = null,
        private ?string $dbPass = null,
    ) {
    }

    public function __get(string $name): mixed
    {
        if (!property_exists($this, $name)) {
            throw new InvalidArgumentException("Property $name does not exist.");
        }

        return $this->$name;
    }

    /**
     * Create from application configuration
     */
    public static function fromArray(array $config): self
    {
        return new self(
            $config['DB_TYPE'] instanceof DatabaseType ? $config['DB_TYPE'] : DatabaseType::from($config['DB_TYPE']),
            $config['DB_NAME'] ?? '',
            $config['DB_HOST'] ?? '',
            $config['DB_PORT'] ?? 0,
            $config['DB_USER'] ?? '',
            $config['DB_PASS'] ?? ''
        );
    }

    /**
     * Create from environment variables
     */
    public static function fromConfigFile(string $path): self
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException("Config file not found: $path");
        }

        $config = require $path;

        return self::fromArray($config);
    }
}
