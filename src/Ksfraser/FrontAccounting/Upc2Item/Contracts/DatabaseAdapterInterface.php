<?php
declare(strict_types=1);

namespace ksfraser\FrontAccounting\Upc2Item\Contracts;

/**
 * Interface for database operations.
 * 
 * Wraps FrontAccounting global DB functions for testability.
 * 
 * @BABOK Related: FR-UPCS-006
 */
interface DatabaseAdapterInterface
{
    /**
     * Execute a query.
     * 
     * @param string $sql
     * @param string $errMsg
     * @return mixed
     */
    public function query(string $sql, string $errMsg = '');

    /**
     * Fetch a row.
     * 
     * @param resource $result
     * @return array<string, mixed>
     */
    public function fetch($result): array;

    /**
     * Get number of rows.
     * 
     * @param resource $result
     * @return int
     */
    public function numRows($result): int;

    /**
     * Escape a string.
     * 
     * @param string $str
     * @return string
     */
    public function escape(string $str): string;

    /**
     * Insert into table.
     * 
     * @param string $table
     * @param array<string, mixed> $data
     * @return bool
     */
    public function insert(string $table, array $data): bool;

    /**
     * Update table.
     * 
     * @param string $table
     * @param array<string, mixed> $data
     * @param string $where
     * @return bool
     */
    public function update(string $table, array $data, string $where): bool;
}
