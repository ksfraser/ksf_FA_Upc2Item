<?php
declare(strict_types=1);

namespace ksfraser\FrontAccounting\Upc2Item\Contracts;

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
     * @param ProductMatch $product
     * @param array $priceBookMap Source => FA sales_type_id mapping
     * @return string FA stock_id on success
     * @throws \RuntimeException On import failure
     */
    public function import(ProductMatch $product, array $priceBookMap): string;

    /**
     * Batch import multiple matches.
     * 
     * @param ProductMatch[] $products
     * @param array $priceBookMap
     * @return string[] Array of created stock_ids
     */
    public function importBatch(array $products, array $priceBookMap): array;
}
