<?php
declare(strict_types=1);

namespace ksfraser\FrontAccounting\Upc2Item\Contracts;

/**
 * Interface for price book mapping operations.
 * 
 * Maps marketplace sources (Amazon, eBay, Facebook) to FA sales types.
 * 
 * @BABOK Related: FR-UPCS-008, FR-UPCS-009
 */
interface PriceBookMapperInterface
{
    /**
     * Get all configured mappings.
     * 
     * @return array<string, PriceBookMapping> Keyed by source name
     */
    public function getMappings(): array;

    /**
     * Set a mapping for a source.
     * 
     * @param string $source Source name (amazon, ebay, facebook, retail)
     * @param string $salesTypeId FA sales type ID
     * @param bool $enabled Whether this price book is active
     * @return void
     */
    public function setMapping(string $source, string $salesTypeId, bool $enabled = true): void;

    /**
     * Check if a source is enabled for price book creation.
     * 
     * @param string $source
     * @return bool
     */
    public function isEnabled(string $source): bool;

    /**
     * Get the default sales type ID for fallback.
     * 
     * @return string
     */
    public function getDefaultSalesTypeId(): string;
}
