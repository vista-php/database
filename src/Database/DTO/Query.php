<?php

namespace Vista\Database\DTO;

use InvalidArgumentException;

/**
 * Data transfer object for query.
 *
 * @property string $query
 * @property array $params
 */
final readonly class Query
{
    public function __construct(
        private string $query,
        private array $params = []
    ) {
    }

    public function __get(string $name): mixed
    {
        if (!property_exists($this, $name)) {
            throw new InvalidArgumentException("Property $name does not exist.");
        }

        return $this->$name;
    }
}
