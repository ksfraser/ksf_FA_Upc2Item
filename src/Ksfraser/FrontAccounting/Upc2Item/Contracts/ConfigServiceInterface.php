<?php
declare(strict_types=1);

namespace ksfraser\FrontAccounting\Upc2Item\Contracts;

/**
 * Interface for module configuration.
 * 
 * @BABOK Related: FR-UPCS-009
 */
interface ConfigServiceInterface
{
    /**
     * Get all configuration as key-value pairs.
     * 
     * @return array<string, mixed>
     */
    public function getAll(): array;

    /**
     * Set a configuration value.
     * 
     * @param string $key
     * @param mixed $value
     * @return bool Success
     */
    public function set(string $key, $value): bool;
}
