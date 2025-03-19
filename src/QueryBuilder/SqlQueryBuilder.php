<?php

namespace Vista\QueryBuilder;

use Exception;
use Vista\Custom\CollectionContract;
use Vista\Database\Database;
use Vista\Database\DatabaseFactory;
use Vista\Database\DTO\Query;
use Vista\Database\Factory;

class SqlQueryBuilder implements QueryBuilder
{
    private Database $db;
    private ?string $modelClass = null;
    private mixed $primaryKey = 'id';

    public function __construct(
        private readonly QueryBuilderObject $query = new QueryBuilderObject(),
        Factory $dbFactory = new DatabaseFactory()
    ) {
        $this->db = $dbFactory->create();
    }

    public function setModelClass(string $className): void
    {
        $this->modelClass = $className;
    }

    public function setPrimaryKey(mixed $primaryKey): void
    {
        $this->primaryKey = $primaryKey;
    }

    public function select(string|array $columns = '*'): QueryBuilder
    {
        $this->query->select = "SELECT {$this->arrToString($columns)}";

        return $this;
    }

    public function from(string $table): QueryBuilder
    {
        $this->query->from = "FROM $table";

        return $this;
    }

    public function leftJoin(
        string $table,
        string $primaryKey,
        string $foreignKey,
        string $operator = '='
    ): QueryBuilder {
        $this->query->leftJoin .= "LEFT JOIN $table ON $primaryKey $operator $foreignKey";

        return $this;
    }

    public function rightJoin(
        string $table,
        string $primaryKey,
        string $foreignKey,
        string $operator = '='
    ): QueryBuilder {
        $this->query->rightJoin .= "RIGHT JOIN $table ON $primaryKey $operator $foreignKey";

        return $this;
    }

    public function innerJoin(
        string $table,
        string $primaryKey,
        string $foreignKey,
        string $operator = '='
    ): QueryBuilder {
        $this->query->innerJoin .= "INNER JOIN $table ON $primaryKey $operator $foreignKey";

        return $this;
    }

    public function where(...$args): QueryBuilder
    {
        $this->prepareQueryWithParams($args);

        return $this;
    }

    public function orWhere(...$args): QueryBuilder
    {
        $this->prepareQueryWithParams($args, operator: 'OR');

        return $this;
    }

    public function orderBy(string|array $column, string $direction = 'ASC'): QueryBuilder
    {
        $this->query->orderBy = trim($this->query->orderBy)
            . (strlen($this->query->orderBy) === 0 ? 'ORDER BY ' : ', ')
            . "$column $direction";

        return $this;
    }

    public function limit(int $limit, int $offset = 0): QueryBuilder
    {
        $this->query->limit = "LIMIT $limit" . ($offset ? " OFFSET $offset" : '');

        return $this;
    }

    public function groupBy(array|string $groupBy): QueryBuilder
    {
        $this->query->groupBy = "GROUP BY {$this->arrToString($groupBy)}";

        return $this;
    }

    public function having(...$args): QueryBuilder
    {
        $this->prepareQueryWithParams($args, where: 'having');

        return $this;
    }

    public function orHaving(...$args): QueryBuilder
    {
        $this->prepareQueryWithParams($args, where: 'having', operator: 'OR');

        return $this;
    }

    public function insert(string $table, array $values): QueryBuilder
    {
        $this->query->insert = "INSERT INTO $table {$this->prepareValues($values)}";

        return $this;
    }

    public function update(string $table, array $values): QueryBuilder
    {
        $this->query->update = "UPDATE $table " . $this->updateSet($values);

        return $this;
    }

    public function delete(): QueryBuilder
    {
        $this->query->delete = 'DELETE';

        return $this;
    }

    public function query(): string
    {
        return $this->query->get();
    }

    public function params(): array
    {
        return $this->query->params;
    }

    /**
     * @throws Exception
     */
    public function get(): CollectionContract
    {
        return $this->db->select(
            $this->modelClass,
            new Query($this->query(), $this->params())
        );
    }

    /**
     * @throws Exception
     */
    public function first(): mixed
    {
        return $this->db->select(
            $this->modelClass,
            new Query($this->limit(limit: 1)->query(), $this->params())
        )->first() ?? null;
    }

    /**
     * @throws Exception
     */
    public function last(): mixed
    {
        return $this->db->select(
            $this->modelClass,
            new Query($this->orderBy($this->primaryKey, 'DESC')->limit(1)->query(), $this->params())
        )->last() ?? null;
    }

    /**
     * @throws Exception
     */
    public function find(mixed $id): mixed
    {
        return $this->db->select(
            $this->modelClass,
            new Query($this->where($this->primaryKey, $id)->query(), $this->params())
        )->first() ?? null;
    }

    /**
     * @throws Exception
     */
    public function all(): CollectionContract
    {
        return $this->db->select(
            $this->modelClass,
            new Query(
                $this->query->select
                . $this->query->from,
                $this->params()
            )
        );
    }

    /**
     * @throws Exception
     */
    public function count(): int
    {
        $result = $this->db->count(
            new Query($this->query(), $this->params())
        );

        $this->query->reset();

        return $result;
    }

    /**
     * @throws Exception
     */
    public function exists(): bool
    {
        return $this->count() > 0;
    }

    /**
     * @throws Exception
     */
    public function save(): bool
    {
        $result = false;
        if (strlen($this->query->insert) > 0) {
            $result = $this->db->insert(
                new Query($this->query(), $this->params())
            );
        } elseif (strlen($this->query->update) > 0) {
            $result = $this->db->update(
                new Query($this->query(), $this->params())
            );
        } elseif (strlen($this->query->delete) > 0) {
            $result = $this->db->delete(
                new Query($this->query(), $this->params())
            );
        }

        $this->query->reset();

        return $result;
    }

    public function lastInsertId(): string|int
    {
        return $this->db->lastInsertId();
    }

    private function arrToString(array|string $groupBy): string
    {
        if (is_array($groupBy)) {
            $groupBy = implode(', ', $groupBy);
        }

        return $groupBy;
    }

    private function prepareValues(array $values): string
    {
        $keys = array_map(function(string $key, string $value) {
            $key = $this->uniquifyKey($key);
            $this->query->setParam(":$key", $value);

            return ":$key";
        }, array_keys($values), array_values($values));

        return '(' . implode(', ', array_keys($values))
            . ') VALUES (' . implode(separator: ', ', array: $keys) . ') ';
    }

    private function prepareQueryWithParams(array $args, string $where = 'where', string $operator = 'AND'): void
    {
        $this->beginWhereClause($where, $operator)
            ->callClosure($where, $args[0])
            ->setCondition($args, $where);
    }

    private function beginWhereClause(string $where = 'where', string $operator = 'AND'): self
    {
        if (strlen($this->query->$where) === 0) {
            $this->query->$where = strtoupper($where) . ' ';
        } elseif (!str_ends_with($this->query->$where, '( ') !== false) {
            $this->query->$where .= $operator . ' ';
        }

        return $this;
    }

    private function callClosure(string $where, callable|string $closure): self
    {
        if (is_string($closure)) {
            return $this;
        }

        $this->query->$where .= '(';
        $closure($this);
        $this->query->$where .= ')';

        return $this;
    }

    private function setCondition(array $args, string $where): void
    {
        if (!is_string($args[0])) {
            return;
        }

        $key = $this->uniquifyKey($args[0]);

        if (count($args) === 2) {
            $this->query->setParam(":$key", $args[1]);
            $this->query->$where .= "$args[0] = :$key";
        } elseif (count($args) === 3) {
            $this->query->setParam(":$key", $args[2]);
            $this->query->$where .= "$args[0] $args[1] :$key";
        }
    }

    private function uniquifyKey(string $key): string
    {
        if (key_exists(":$key", $this->query->params)) {
            $i = 0;
            do {
                $num = $i++;
            } while (key_exists(":{$key}_$num", $this->query->params));
            $key .= "_$num";
        }

        return $key;
    }

    private function updateSet(array $values): string
    {
        return 'SET ' . implode(', ', array_map(function($key) use ($values) {
            $keyFormatted = $this->uniquifyKey($key);
            $this->query->setParam(":$keyFormatted", $values[$key]);

            return "$key = :$keyFormatted";
        }, array_keys($values))) . ' ';
    }
}
