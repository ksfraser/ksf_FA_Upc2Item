<?php
declare(strict_types=1);

namespace ksfraser\FrontAccounting\Upc2Item\Contracts;

/**
 * Interface for barcode scanning operations.
 * 
 * Supports live scanning (keyboard wedge / image) and CSV batch.
 * 
 * @BABOK Related: FR-UPCS-002
 */
interface BarcodeScannerInterface
{
    /**
     * Scan a single UPC from input stream or image.
     * 
     * @param string $input Raw UPC string or base64 image
     * @return string Normalized UPC (GTIN-13 preferred)
     * @throws \InvalidArgumentException If UPC format invalid
     */
    public function scan(string $input): string;

    /**
     * Scan batch from CSV content.
     * 
     * @param string $csvContent Raw CSV with one UPC per line
     * @return string[] Array of normalized UPCs
     */
    public function scanBatch(string $csvContent): array;
}
