<?php
declare(strict_types=1);

namespace ksfraser\FrontAccounting\Upc2Item\Services;

use ksfraser\FrontAccounting\Upc2Item\Contracts\DatabaseAdapterInterface;

/**
 * Database adapter wrapping FrontAccounting global DB functions.
 * 
 * @UML Note: Adapter class
 * @BABOK Related: FR-UPCS-006
 */
class FaDatabaseAdapter implements DatabaseAdapterInterface
{
    /**
     * {@inheritdoc}
     */
    public function query(string $sql, string $errMsg = '')
    {
        return db_query($sql, $errMsg);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($result): array
    {
        return db_fetch($result);
    }

    /**
     * {@inheritdoc}
     */
    public function numRows($result): int
    {
        return db_num_rows($result);
    }

    /**
     * {@inheritdoc}
     */
    public function escape(string $str): string
    {
        return db_escape($str);
    }

    /**
     * {@inheritdoc}
     */
    public function insert(string $table, array $data): bool
    {
        return db_insert($table, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function update(string $table, array $data, string $where): bool
    {
        return db_update($table, $data, $where);
    }
}
