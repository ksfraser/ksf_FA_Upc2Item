<?php
declare(strict_types=1);

namespace ksfraser\FrontAccounting\Upc2Item\Services;

use ksfraser\FrontAccounting\Upc2Item\Contracts\BarcodeScannerInterface;

/**
 * Default barcode scanner service.
 * 
 * Handles UPC normalization and CSV batch scanning.
 * Uses keyboard-wedge input for live scanning or file upload for batch.
 * 
 * @UML Note: Service class
 * @BABOK Related: FR-UPCS-002
 */
class BarcodeScannerService implements BarcodeScannerInterface
{
    /**
     * Normalize a raw UPC to GTIN-13 where possible.
     * 
     * @param string $input Raw UPC (8, 12, 13 digits)
     * @return string Normalized 13-digit UPC
     * @throws \InvalidArgumentException If invalid format
     */
    public function scan(string $input): string
    {
        $upc = preg_replace('/\D/', '', $input);
        if (!$upc || !preg_match('/^\d{8}$|^\d{12}$|^\d{13}$/', $upc)) {
            throw new \InvalidArgumentException("Invalid UPC format: {$input}");
        }
        if (strlen($upc) === 8 || strlen($upc) === 12) {
            $upc = str_pad($upc, 13, '0', STR_PAD_LEFT);
        }
        return $upc;
    }

    /**
     * Scan batch from CSV content (one UPC per line).
     * 
     * @param string $csvContent
     * @return string[]
     */
    public function scanBatch(string $csvContent): array
    {
        $lines = explode("\n", trim($csvContent));
        $results = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            try {
                $results[] = $this->scan($line);
            } catch (\InvalidArgumentException $e) {
                // Skip invalid lines but continue
                continue;
            }
        }
        return array_values(array_unique($results));
    }
}
