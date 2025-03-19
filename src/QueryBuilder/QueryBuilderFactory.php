<?php

namespace Vista\QueryBuilder;

use InvalidArgumentException;

readonly class QueryBuilderFactory implements Factory
{
    public function __construct(
        private ?QueryBuilder $defaultQueryBuilder = null
    ) {
    }

    public function create(string $type = 'sql'): QueryBuilder
    {
        if ($this->defaultQueryBuilder !== null) {
            return $this->defaultQueryBuilder;
        }

        return match ($type) {
            'sql' => new SqlQueryBuilder(),
            default => throw new InvalidArgumentException("Invalid query builder type: $type."),
        };
    }
}
