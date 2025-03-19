<?php

namespace Vista\Model;

use Vista\Custom\CollectionContract;
use Vista\QueryBuilder\QueryBuilder;

interface ModelContract
{
    public static function select(string|array $columns = '*'): QueryBuilder;

    public static function leftJoin(string $table, string $primaryKey, string $foreignKey, string $operator = '='): QueryBuilder;

    public static function rightJoin(string $table, string $primaryKey, string $foreignKey, string $operator = '='): QueryBuilder;

    public static function innerJoin(string $table, string $primaryKey, string $foreignKey, string $operator = '='): QueryBuilder;

    public static function where(...$args): QueryBuilder;

    public static function orWhere(...$args): QueryBuilder;

    public static function orderBy(string|array $column, string $direction = 'ASC'): QueryBuilder;

    public static function limit(int $limit, int $offset = 0): QueryBuilder;

    public static function groupBy(string|array $groupBy): QueryBuilder;

    public static function having(...$args): QueryBuilder;

    public static function orHaving(...$args): QueryBuilder;

    public static function update(array $values): QueryBuilder;

    public static function delete(): QueryBuilder;

    public static function insert(array $data): ?ModelContract;

    public static function first(): ?ModelContract;

    public static function last(): ?ModelContract;

    public static function find(int $id): ?ModelContract;

    /**
     * @return CollectionContract<ModelContract>
     */
    public static function all(): CollectionContract;

    public static function count(): int;

    public static function exists(): bool;

    public function save(): ?ModelContract;

    public function destroy(): bool;
}
