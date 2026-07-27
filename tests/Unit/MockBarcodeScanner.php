<?php
declare(strict_types=1);

namespace ksfraser\FrontAccounting\Upc2Item\Services;

/**
 * Mock barcode scanner for testing.
 */
class MockBarcodeScanner implements BarcodeScannerInterface
{
    public function scan(string $input): string
    {
        $upc = preg_replace('/\D/', '', $input);
        $upc = str_pad($upc, 13, '0', STR_PAD_LEFT);
        return $upc;
    }

    public function scanBatch(string $csvContent): array
    {
        return $this->scanBatch($csvContent);
    }
}
