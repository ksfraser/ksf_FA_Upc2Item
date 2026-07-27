<?php
declare(strict_types=1);

namespace ksfraser\FrontAccounting\Upc2Item\Contracts;

/**
 * Interface for product search across marketplaces.
 * 
 * @BABOK Related: FR-UPCS-003, FR-UPCS-004, FR-UPCS-005
 */
interface ProductSearchInterface
{
    /**
     * Search for a product by UPC.
     * 
     * @param string $upc Normalized UPC
     * @return ProductMatch|null Product data or null if not found
     */
    public function searchByUpc(string $upc);
}
