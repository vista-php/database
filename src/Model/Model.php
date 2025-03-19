<?php

namespace Vista\Model;

use Vista\Custom\CollectionContract;
use Vista\Exceptions\ColumnNotFoundException;
use Vista\QueryBuilder\Factory;
use Vista\QueryBuilder\QueryBuilder;
use Vista\QueryBuilder\QueryBuilderFactory;

class Model implements ModelContract
{
    private static ?Factory $queryBuilderFactory = null;
    protected QueryBuilder $queryBuilder;
    protected string $table;
    protected string $primaryKey = 'id';

    protected array $relationships = [];
    /**
     * @var array<string> List of columns in the table.
     */
    protected array $columns;
    /**
     * @var array<array<string, mixed>>
     */
    private array $values = [];

    private static function getInstance(): static
    {
        return new static(static::$queryBuilderFactory);
    }

    public static function setQueryBuilderFactory(Factory $factory): void
    {
        static::$queryBuilderFactory = $factory;
    }

    public static function select(string|array $columns = '*'): QueryBuilder
    {
        if ($columns === '*') {
            $model = static::getInstance();

            return $model->select($model->columns);
        }

        return static::getInstance()->queryBuilder->select($columns);
    }

    public static function leftJoin(string $table, string $primaryKey, string $foreignKey, string $operator = '='): QueryBuilder
    {
        return static::getInstance()->queryBuilder->leftJoin($table, $primaryKey, $foreignKey, $operator);
    }

    public static function rightJoin(string $table, string $primaryKey, string $foreignKey, string $operator = '='): QueryBuilder
    {
        return static::getInstance()->queryBuilder->rightJoin($table, $primaryKey, $foreignKey, $operator);
    }

    public static function innerJoin(string $table, string $primaryKey, string $foreignKey, string $operator = '='): QueryBuilder
    {
        return static::getInstance()->queryBuilder->innerJoin($table, $primaryKey, $foreignKey, $operator);
    }

    public static function where(...$args): QueryBuilder
    {
        return static::getInstance()->queryBuilder->where(...$args);
    }

    public static function orWhere(...$args): QueryBuilder
    {
        return static::getInstance()->queryBuilder->orWhere(...$args);
    }

    public static function orderBy(string|array $column, string $direction = 'ASC'): QueryBuilder
    {
        return static::getInstance()->queryBuilder->orderBy($column, $direction);
    }

    public static function limit(int $limit, int $offset = 0): QueryBuilder
    {
        return static::getInstance()->queryBuilder->limit($limit, $offset);
    }

    public static function groupBy(string|array $groupBy): QueryBuilder
    {
        return static::getInstance()->queryBuilder->groupBy($groupBy);
    }

    public static function having(...$args): QueryBuilder
    {
        return static::getInstance()->queryBuilder->having(...$args);
    }

    public static function orHaving(...$args): QueryBuilder
    {
        return static::getInstance()->queryBuilder->orHaving(...$args);
    }

    public static function update(array $values): QueryBuilder
    {
        $model = static::getInstance();

        return $model->queryBuilder->update($model->table, $values);
    }

    public static function delete(): QueryBuilder
    {
        return static::getInstance()->queryBuilder->delete();
    }

    public static function insert(array $data): ?MOdelContract
    {
        $model = static::getInstance();

        $model->queryBuilder->insert($model->table, $data)->save();

        return $model->queryBuilder->select($model->columns)->from($model->table)->find($model->queryBuilder->lastInsertId());
    }

    public static function first(): ?ModelContract
    {
        return  static::getInstance()->queryBuilder->first();
    }

    public static function last(): ?ModelContract
    {
        return static::getInstance()->queryBuilder->last();
    }

    public static function find(int $id): ?ModelContract
    {
        return static::getInstance()->queryBuilder->find($id);
    }

    public static function all(): CollectionContract
    {
        return static::getInstance()->queryBuilder->all();
    }

    public static function count(): int
    {
        return static::getInstance()->queryBuilder->count();
    }

    public static function exists(): bool
    {
        return static::getInstance()->queryBuilder->exists();
    }

    public function __construct(
        private ?Factory $factory = null
    ) {
        $this->factory = $factory ?? new QueryBuilderFactory();
        $this->resetQueryBuilder();
    }

    /**
     * @throws ColumnNotFoundException
     */
    public function __get(string $name): mixed
    {
        if (in_array($name, $this->relationships)) {
            return $this->{$name}();
        }

        if (!in_array($name, $this->columns)) {
            throw new ColumnNotFoundException("Column '$name' not found in the table.");
        }

        return $this->values[$name];
    }

    /**
     * @throws ColumnNotFoundException
     */
    public function __set(string $name, mixed $value): void
    {
        if (!in_array($name, $this->columns)) {
            throw new ColumnNotFoundException("Column '$name' not found in the table.");
        }

        $this->values[$name] = $value;
    }

    public function save(): ?ModelContract
    {
        $this->resetQueryBuilder();

        if (empty($this->{$this->primaryKey})) {
            $this->queryBuilder->insert($this->table, $this->values)->save();
            $this->{$this->primaryKey} = $this->queryBuilder->lastInsertId();
        } else {
            $this->queryBuilder->update($this->table, $this->values)
                ->where($this->primaryKey, $this->{$this->primaryKey})
                ->save();
        }

        return $this;
    }

    public function destroy(): bool
    {
        $this->resetQueryBuilder();

        return $this->queryBuilder->delete()->save();
    }

    private function resetQueryBuilder(): void
    {
        $this->queryBuilder = $this->factory->create();
        $this->queryBuilder->setModelClass(className: static::class);
        $this->queryBuilder->setPrimaryKey($this->primaryKey);
        $this->queryBuilder->select($this->columns)->from($this->table);
    }
}
