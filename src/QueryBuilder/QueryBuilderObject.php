<?php

namespace Vista\QueryBuilder;

/**
 * @mixin QueryBuilderObject
 *
 * @property string $select
 * @property string $from
 * @property string $leftJoin
 * @property string $rightJoin
 * @property string $innerJoin
 * @property string $where
 * @property string $groupBy
 * @property string $having
 * @property string $orderBy
 * @property string $limit
 * @property string $insert
 * @property string $update
 * @property string $delete
 * @property array $params
 */
final class QueryBuilderObject
{
    public function __construct(
        private string $select = '',
        private string $from = '',
        private string $leftJoin = '',
        private string $rightJoin = '',
        private string $innerJoin = '',
        private string $where = '',
        private string $groupBy = '',
        private string $having = '',
        private string $orderBy = '',
        private string $limit = '',
        private string $insert = '',
        private string $update = '',
        private string $delete = '',
        private array $params = []
    ) {
    }

    public function __get(string $name): mixed
    {
        if (!property_exists($this, $name)) {
            return null;
        }

        return $this->$name;
    }

    public function __set(string $name, mixed $value): void
    {
        if (!property_exists($this, $name)) {
            return;
        }

        $this->$name = $name === 'params' ? $value : trim($value) . ' ';
    }

    public function setParam(string $key, mixed $value): void
    {
        $this->params[$key] = $value;
    }

    public function get(): string
    {
        if (strlen($this->insert) > 0) {
            return $this->insertQuery();
        }

        if (strlen($this->update) > 0) {
            return $this->updateQuery();
        }

        if (strlen($this->delete) > 0) {
            return $this->deleteQuery();
        }

        return $this->selectQuery();
    }

    public function reset(): void
    {
        $this->select = '';
        $this->from = '';
        $this->leftJoin = '';
        $this->rightJoin = '';
        $this->innerJoin = '';
        $this->where = '';
        $this->groupBy = '';
        $this->having = '';
        $this->orderBy = '';
        $this->limit = '';
        $this->insert = '';
        $this->update = '';
        $this->delete = '';
        $this->params = [];
    }

    private function selectQuery(): string
    {
        return trim(
            $this->select
            . $this->from
            . $this->leftJoin
            . $this->rightJoin
            . $this->innerJoin
            . $this->where
            . $this->groupBy
            . $this->having
            . $this->orderBy
            . $this->limit
        );
    }

    private function insertQuery(): string
    {
        return trim($this->insert);
    }

    private function updateQuery(): string
    {
        return trim(
            $this->update
            . $this->where
        );
    }

    private function deleteQuery(): string
    {
        return trim(
            $this->delete
            . $this->from
            . $this->where
        );
    }

    private function select(): string
    {
        return trim($this->select) . ' ';
    }

    private function from(): string
    {
        return trim($this->from) . ' ';
    }

    private function leftJoin(): string
    {
        return trim($this->leftJoin) . ' ';
    }

    private function rightJoin(): string
    {
        return trim($this->rightJoin) . ' ';
    }

    private function innerJoin(): string
    {
        return trim($this->innerJoin) . ' ';
    }

    private function where(): string
    {
        return trim($this->where) . ' ';
    }

    private function groupBy(): string
    {
        return trim($this->groupBy) . ' ';
    }

    private function having(): string
    {
        return trim($this->having) . ' ';
    }

    private function orderBy(): string
    {
        return trim($this->orderBy) . ' ';
    }

    private function limit(): string
    {
        return trim($this->limit) . ' ';
    }

    private function update(): string
    {
        return trim($this->update) . ' ';
    }

    private function delete(): string
    {
        return trim($this->delete) . ' ';
    }
}
