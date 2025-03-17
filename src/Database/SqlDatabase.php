<?php

namespace Vista\Database;

use PDO;
use PDOException;
use PDOStatement;
use Vista\Custom\Collection;
use Vista\Custom\CollectionContract;
use Vista\Database\DTO\Query;

class SqlDatabase implements Database
{
    private ?PDO $db = null;
    private PDOStatement|false $result = false;

    /**
     * @throws PDOException
     */
    public function __construct(
        DatabaseType $dbType = DatabaseType::MYSQL,
        ?string $dbName = null,
        ?string $host = null,
        ?int $port = 3306,
        ?string $user = null,
        ?string $pass = null,
    ) {
        $this->connect($dbType, $dbName, $host, $port, $user, $pass);
    }

    /**
     * Opens a database connection.
     *
     * @param  DatabaseType $dbType Database type.
     * @param  string       $dbName Database name.
     * @param  ?string      $host   Database host.
     * @param  ?int         $port   Database port.
     * @param  ?string      $user   Database user.
     * @param  ?string      $pass   Database password.
     * @return bool
     */
    public function connect(
        DatabaseType $dbType,
        string $dbName,
        ?string $host,
        ?int $port,
        ?string $user,
        ?string $pass
    ): bool {
        switch ($dbType) {
            case DatabaseType::MYSQL:
                $this->db = new PDO(
                    dsn: 'mysql:dbname=' . $dbName . ';host=' . $host . ';port=' . $port,
                    username: $user,
                    password: $pass,
                    options: [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"],
                );
                break;
            case DatabaseType::SQLITE:
                $this->db = new PDO('sqlite:' . $dbName);
                break;
        }

        $this->db->setAttribute(
            attribute: PDO::ATTR_ERRMODE,
            value: PDO::ERRMODE_EXCEPTION,
        );

        return true;
    }

    /**
     * Closes the database connection.
     *
     * @return bool True on success, false on failure.
     */
    public function disconnect(): bool
    {
        $this->db = null;

        return true;
    }

    /**
     * Executes a raw query.
     *
     * @param  Query $query Query to be executed.
     * @return bool
     */
    public function executeRaw(Query $query): bool
    {
        return $this->bindParams($query->query, $query->params)->execute();
    }

    /**
     * Returns an ICollection of objects for a given class name.
     *
     * @template T
     * @param  class-string<T>       $className Fully qualified class name.
     * @param  Query                 $query     Select query with parameters for binding.
     * @return CollectionContract<T> Objects representing records in the database.
     * @throws PDOException          If the query fails.
     */
    public function select(string $className, Query $query): CollectionContract
    {
        $this->bindParams($query->query, $query->params)->execute();

        return $this->fetchAllObjects($className);
    }

    /**
     * Returns an associative array of the rows.
     *
     * @param  Query        $query Select query with parameters for binding.
     * @throws PDOException If the query fails.
     */
    public function selectAssoc(Query $query): array
    {
        $this->bindParams($query->query, $query->params)->execute();

        return $this->fetchAll();
    }

    /**
     * Counts the number of rows matching the query.
     *
     * @param  Query        $query Select query with parameters for binding.
     * @return int          Number of matching rows.
     * @throws PDOException If the query fails.
     */
    public function count(Query $query): int
    {
        return count($this->selectAssoc($query));
    }

    /**
     * Checks if a row exists.
     *
     * @param  Query        $query Select query with parameters for binding.
     * @return bool         True if a row exists, false otherwise.
     * @throws PDOException If the query fails.
     */
    public function rowExists(Query $query): bool
    {
        return count($this->selectAssoc($query)) > 0;
    }

    /**
     * Executes an insert query.
     *
     * @param  Query        $query Insert query with parameters for binding.
     * @return bool         True on success, false on failure.
     * @throws PDOException If the query fails.
     */
    public function insert(Query $query): bool
    {
        return $this->bindParams($query->query, $query->params)->execute();
    }

    /**
     * Executes an update query if the row exists.
     *
     * @param  Query        $query Insert query with parameters for binding.
     * @return bool         True on success, false on failure.
     * @throws PDOException If the query fails.
     */
    public function update(Query $query): bool
    {
        return $this->bindParams($query->query, $query->params)->execute();
    }

    /**
     * Executes a delete query.
     *
     * @param  Query        $query Delete query with parameters for binding.
     * @return bool         True on success, false on failure.
     * @throws PDOException If the query fails.
     */
    public function delete(Query $query): bool
    {
        return $this->bindParams($query->query, $query->params)->execute();
    }

    /**
     * Initiates a transaction.
     *
     * @return bool         True on success, false on failure.
     * @throws PDOException If the transaction fails to start.
     */
    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    /**
     * Checks if a transaction is active.
     *
     * @return bool True if a transaction is active, false otherwise.
     */
    public function inTransaction(): bool
    {
        return $this->db->inTransaction();
    }

    /**
     * Commits a transaction.
     *
     * @return bool         True on success, false on failure.
     * @throws PDOException If the transaction fails to commit.
     */
    public function commit(): bool
    {
        return $this->db->commit();
    }

    /**
     * Rolls back a transaction.
     *
     * @return bool         True on success, false on failure.
     * @throws PDOException If the transaction fails to roll back.
     */
    public function rollBack(): bool
    {
        return $this->db->rollBack();
    }

    /**
     * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.1.0)<br/>
     * Returns the ID of the last inserted row or sequence value
     * @link http://php.net/manual/en/pdo.lastinsertid.php
     * @param  ?string      $name [optional] <p>
     *                            Name of the sequence object from which the ID should be returned.
     *                            </p>
     * @return false|string
     */
    public function lastInsertId(?string $name = null): false|string
    {
        return $this->db->lastInsertId($name);
    }

    private function bindParams(string $query, array $params): self
    {
        $this->result = $this->db->prepare($query);

        foreach ($params as $key => &$val) {
            $this->result->bindParam($key, $val);
        }

        return $this;
    }

    private function execute(): bool
    {
        if ($this->result === false) {
            throw new PDOException('*** There is no query to execute.');
        }

        return $this->result->execute();
    }

    private function setFetchMode(string $args): void
    {
        $this->result->setFetchMode(PDO::FETCH_CLASS, $args);
    }

    private function fetchAll(): array
    {
        return $this->result->fetchAll();
    }

    private function fetchAllObjects(string $className): CollectionContract
    {
        $this->setFetchMode($className);

        $objects = [];
        while ($object = $this->result->fetch()) {
            $objects[] = $object;
        }

        return new Collection($objects);
    }
}
