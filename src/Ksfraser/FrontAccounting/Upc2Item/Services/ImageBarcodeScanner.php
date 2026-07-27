<?php
declare(strict_types=1);

namespace ksfraser\FrontAccounting\Upc2Item\Services;

use ksfraser\FrontAccounting\Upc2Item\Contracts\BarcodeScannerInterface;

/**
 * Extended barcode scanner supporting image-based decoding via tesseract or heuristics.
 * 
 * @BABOK Related: FR-UPCS-002
 */
class ImageBarcodeScanner implements BarcodeScannerInterface
{
    /** @var BarcodeScannerInterface */
    private $fallback;

    public function __construct(BarcodeScannerInterface $fallback)
    {
        $this->fallback = $fallback;
    }

    /**
     * Try to decode UPC from base64 image, fallback to standard scanner.
     * 
     * @param string $input base64 image or raw UPC
     * @return string
     */
    public function scan(string $input): string
    {
        if (str_starts_with($input, 'data:image') || preg_match('/^[A-Za-z0-9+\/]{50,}={0,2}$/', $input)) {
            return $this->decodeImage($input);
        }
        return $this->fallback->scan($input);
    }

    /**
     * Decode barcode from base64 image.
     * 
     * @param string $base64
     * @return string
     * @throws \InvalidArgumentException
     */
    private function decodeImage(string $base64): string
    {
        // In a real deployment, integrate with tesseract or a barcode library
        throw new \InvalidArgumentException("Image barcode decoding requires tesseract or barcode library integration");
    }

    /**
     * {@inheritdoc}
     */
    public function scanBatch(string $csvContent): array
    {
        return $this->fallback->scanBatch($csvContent);
    }
}
