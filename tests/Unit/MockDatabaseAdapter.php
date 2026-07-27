<?php
declare(strict_types=1);

namespace ksfraser\FrontAccounting\Upc2Item\Services;

/**
 * In-memory mock database adapter for unit testing.
 * 
 * @internal
 */
class MockDatabaseAdapter implements DatabaseAdapterInterface
{
    /** @var array<string, array<int, array<string, mixed>>> */
    private $tables = [];

    /** @var int Auto-increment counter per table */
    private $autoIncrement = [];

    public function query(string $sql, string $errMsg = '')
    {
        // Very lightweight SQL parser for testing
        if (preg_match('/^SELECT\s+.*FROM\s+(\w+)/i', $sql, $m)) {
            $table = $m[1];
            return $this->createResult($table);
        }
        if (preg_match('/^INSERT\s+INTO\s+(\w+)/i', $sql, $m)) {
            $table = $m[1];
            // Extract values from VALUES (...) or key-value pairs
            if (!isset($this->tables[$table])) {
                $this->tables[$table] = [];
                $this->autoIncrement[$table] = 0;
            }
            if (preg_match('/\(([^)]+)\)\s*VALUES\s*\(([^)]+)\)/i', $sql, $vm)) {
                $keys = array_map('trim', explode(',', $vm[1]));
                $vals = array_map(function($v) {
                    $v = trim($v, " '\"");
                    return $v;
                }, explode(',', $vm[2]));
                $row = array_combine($keys, $vals);
                $this->autoIncrement[$table]++;
                $row['id'] = $this->autoIncrement[$table];
                $this->tables[$table][] = $row;
                return true;
            }
            return true;
        }
        if (preg_match('/^UPDATE\s+(\w+)/i', $sql, $m)) {
            $table = $m[1];
            if (preg_match('/SET\s+(.+?)\s+WHERE\s+(.+)$/i', $sql, $sm)) {
                $setPart = trim($sm[1]);
                $wherePart = trim($sm[2]);
                if (isset($this->tables[$table])) {
                    foreach ($this->tables[$table] as &$row) {
                        if ($this->matchesWhere($row, $wherePart)) {
                            foreach (explode(',', $setPart) as $assign) {
                                if (preg_match('/(\w+)\s*=\s*[\'"]?([^\'"]+)[\'"]?/', $assign, $am)) {
                                    $row[$am[1]] = $am[2];
                                }
                            }
                        }
                    }
                }
            }
            return true;
        }
        return true;
    }

    public function fetch($result): array
    {
        if (is_array($result) && isset($result[0])) {
            return array_shift($result);
        }
        if (is_string($result) && isset($this->tables[$result])) {
            return array_shift($this->tables[$result]);
        }
        return [];
    }

    public function numRows($result): int
    {
        if (is_array($result)) {
            return count($result);
        }
        if (is_string($result) && isset($this->tables[$result])) {
            return count($this->tables[$result]);
        }
        return 0;
    }

    public function escape(string $str): string
    {
        return addslashes($str);
    }

    public function insert(string $table, array $data): bool
    {
        if (!isset($this->tables[$table])) {
            $this->tables[$table] = [];
            $this->autoIncrement[$table] = 0;
        }
        $this->autoIncrement[$table]++;
        $data['id'] = $this->autoIncrement[$table];
        $this->tables[$table][] = $data;
        return true;
    }

    public function update(string $table, array $data, string $where): bool
    {
        if (!isset($this->tables[$table])) {
            $this->tables[$table] = [];
        }
        foreach ($this->tables[$table] as &$row) {
            if ($this->matchesWhere($row, $where)) {
                foreach ($data as $k => $v) {
                    $row[$k] = $v;
                }
            }
        }
        return true;
    }

    /**
     * Seed table with initial rows.
     */
    public function seed(string $table, array $rows): void
    {
        $this->tables[$table] = $rows;
        $this->autoIncrement[$table] = count($rows);
    }

    /**
     * Get raw table data for assertions.
     */
    public function getTable(string $table): array
    {
        return $this->tables[$table] ?? [];
    }

    private function createResult(string $table)
    {
        return $table;
    }

    private function matchesWhere(array $row, string $where): bool
    {
        if (preg_match('/(\w+)\s*=\s*[\'"]?([^\'"]+)[\'"]?/', $where, $m)) {
            return (string)($row[$m[1]] ?? '') === $m[2];
        }
        return true;
    }
}
