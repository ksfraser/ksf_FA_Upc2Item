<?php
declare(strict_types=1);

namespace ksfraser\FrontAccounting\Upc2Item\Contracts;

use ksfraser\FrontAccounting\Upc2Item\Models\ScanResult;

/**
 * Interface for importing scanned products into FA Items/Inventory.
 * 
 * @BABOK Related: FR-UPCS-006, FR-UPCS-007
 */
interface FaItemImporterInterface
{
    /**
     * Import a single product match into FA stock master and prices.
     * 
     * @param ScanResult $product
     * @param array $priceBookMap Source => FA sales_type_id mapping
     * @return string FA stock_id on success
     * @throws \RuntimeException On import failure
     */
    public function import(ScanResult $product, array $priceBookMap): string;

    /**
     * Batch import multiple matches.
     * 
     * @param ScanResult[] $products
     * @param array $priceBookMap
     * @return string[] Array of created stock_ids
     */
    public function importBatch(array $products, array $priceBookMap): array;
}
