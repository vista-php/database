<?php

namespace Vista\QueryBuilder;

use Vista\Custom\CollectionContract;

interface QueryBuilder
{
    public function setModelClass(string $className): void;

    public function setPrimaryKey(string $primaryKey): void;

    public function select(array|string $columns = '*'): QueryBuilder;

    public function from(string $table): QueryBuilder;

    public function leftJoin(string $table, string $primaryKey, string $foreignKey, string $operator = '='): QueryBuilder;

    public function rightJoin(string $table, string $primaryKey, string $foreignKey, string $operator = '='): QueryBuilder;

    public function innerJoin(string $table, string $primaryKey, string $foreignKey, string $operator = '='): QueryBuilder;

    public function where(...$args): QueryBuilder;

    public function orWhere(...$args): QueryBuilder;

    public function orderBy(string $column, string $direction = 'ASC'): QueryBuilder;

    public function limit(int $limit, int $offset = 0): QueryBuilder;

    public function groupBy(array|string $groupBy): QueryBuilder;

    public function having(...$args): QueryBuilder;

    public function orHaving(...$args): QueryBuilder;

    public function insert(string $table, array $values): QueryBuilder;

    public function update(string $table, array $values): QueryBuilder;

    public function delete(): QueryBuilder;

    public function query(): string;

    public function params(): array;

    public function get(): CollectionContract;

    public function first(): mixed;

    public function last(): mixed;

    public function find(mixed $id): mixed;

    public function all(): CollectionContract;

    public function count(): int;

    public function exists(): bool;

    public function save(): bool;

    public function lastInsertId(): string|int;
}
