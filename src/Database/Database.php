<?php

namespace Vista\Database;

use Exception;
use Vista\Custom\CollectionContract;
use Vista\Database\DTO\Query;

/**
 * A database connection.
 */
interface Database
{
    /**
     * Opens a database connection.
     *
     * @param  DatabaseType $dbType Database type.
     * @param  string       $dbName Database name.
     * @param  ?string      $host   Database host.
     * @param  ?int         $port   Database port.
     * @param  ?string      $user   Database user.
     * @param  ?string      $pass   Database password.
     * @return bool         True on success, false on failure.
     * @throws Exception    If the connection fails.
     */
    public function connect(DatabaseType $dbType, string $dbName, ?string $host, ?int $port, ?string $user, ?string $pass): bool;

    /**
     * Closes the database connection.
     *
     * @return bool True on success, false on failure.
     */
    public function disconnect(): bool;

    /**
     * Executes a raw query.
     *
     * @param  Query     $query Query to be executed.
     * @return bool      True on success, false on failure.
     * @throws Exception If the query fails.
     */
    public function executeRaw(Query $query): bool;

    /**
     * Returns an ICollection of objects for a given class name.
     *
     * @template T
     * @param  class-string<T>       $className Fully qualified class name.
     * @param  Query                 $query     Select query with parameters for binding.
     * @return CollectionContract<T> Objects representing records in the database.
     * @throws Exception             If the query fails.
     */
    public function select(string $className, Query $query): CollectionContract;

    /**
     * Returns an associative array of the rows.
     *
     * @param  Query     $query Select query with parameters for binding.
     * @return array     Associative arrays representing records in the database.
     * @throws Exception If the query fails.
     */
    public function selectAssoc(Query $query): array;

    /**
     * Counts the number of rows matching the query.
     *
     * @param  Query     $query Select query with parameters for binding.
     * @return int       Number of matching rows.
     * @throws Exception If the query fails.
     */
    public function count(Query $query): int;

    /**
     * Checks if a row exists.
     *
     * @param  Query     $query Select query with parameters for binding.
     * @return bool      True if a row exists, false otherwise.
     * @throws Exception If the query fails.
     */
    public function rowExists(Query $query): bool;

    /**
     * Executes an insert query.
     *
     * @param  Query     $query Insert query with parameters for binding.
     * @return bool      True on success, false on failure.
     * @throws Exception If the query fails.
     */
    public function insert(Query $query): bool;

    /**
     * Executes an update query if the row exists.
     *
     * @param  Query     $query Insert query with parameters for binding.
     * @return bool      True on success, false on failure.
     * @throws Exception If the query fails.
     */
    public function update(Query $query): bool;

    /**
     * Executes a delete query.
     *
     * @param  Query     $query Delete query with parameters for binding.
     * @return bool      True on success, false on failure.
     * @throws Exception If the query fails.
     */
    public function delete(Query $query): bool;

    /**
     * Initiates a transaction.
     *
     * @return bool      True on success, false on failure.
     * @throws Exception If the transaction fails to start.
     */
    public function beginTransaction(): bool;

    /**
     * Checks if a transaction is active.
     *
     * @return bool True if a transaction is active, false otherwise.
     */
    public function inTransaction(): bool;

    /**
     * Commits a transaction.
     *
     * @return bool      True on success, false on failure.
     * @throws Exception If the transaction fails to commit.
     */
    public function commit(): bool;

    /**
     * Rolls back a transaction.
     *
     * @return bool      True on success, false on failure.
     * @throws Exception If the transaction fails to roll back.
     */
    public function rollBack(): bool;

    /**
     * Returns the ID of the last inserted row or sequence value.
     *
     * @param  string|null  $name Name of the sequence object (optional).
     * @return false|string The last inserted ID or false on failure.
     */
    public function lastInsertId(?string $name = null): false|string;
}
