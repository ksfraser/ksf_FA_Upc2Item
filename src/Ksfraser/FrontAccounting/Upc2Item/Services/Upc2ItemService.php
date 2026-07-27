<?php
declare(strict_types=1);

namespace ksfraser\FrontAccounting\Upc2Item\Services;

use ksfraser\FrontAccounting\Upc2Item\Contracts\BarcodeScannerInterface;
use ksfraser\FrontAccounting\Upc2Item\Contracts\ProductSearchInterface;
use ksfraser\FrontAccounting\Upc2Item\Contracts\FaItemImporterInterface;
use ksfraser\FrontAccounting\Upc2Item\Contracts\PriceBookMapperInterface;
use ksfraser\FrontAccounting\Upc2Item\Models\ScanResult;

/**
 * Main orchestrating service for UPC2Item.
 * 
 * Coordinates scanning, searching, and importing.
 * 
 * @UML Note: Facade / Service layer
 * @BABOK Related: FR-UPCS-001
 */
class Upc2ItemService
{
    private BarcodeScannerInterface $scanner;
    private ProductSearchInterface $searchService;
    private FaItemImporterInterface $importer;
    private PriceBookMapperInterface $priceBookMapper;
    private $conn;

    public function __construct(
        $conn,
        BarcodeScannerInterface $scanner,
        ProductSearchInterface $searchService,
        FaItemImporterInterface $importer,
        PriceBookMapperInterface $priceBookMapper
    ) {
        $this->conn = $conn;
        $this->scanner = $scanner;
        $this->searchService = $searchService;
        $this->importer = $importer;
        $this->priceBookMapper = $priceBookMapper;
    }

    /**
     * Process a single UPC: scan, search, import.
     * 
     * @param string $upcInput
     * @return ScanResult
     */
    public function processUpc(string $upcInput): ScanResult
    {
        $upc = $this->scanner->scan($upcInput);
        $product = $this->searchService->searchByUpc($upc);

        if ($product === null) {
            $product = new ScanResult($upc);
        }

        if ($product->getTitle() !== null || $product->getAmazonPrice() !== null) {
            $priceBookMap = $this->priceBookMapper->getMappings();
            $this->importer->import($product, $priceBookMap);
            $product->setFaImported(true);
        }

        return $product;
    }

    /**
     * Process CSV batch.
     * 
     * @param string $csvContent
     * @return ScanResult[]
     */
    public function processBatch(string $csvContent): array
    {
        $upcs = $this->scanner->scanBatch($csvContent);
        $priceBookMap = $this->priceBookMapper->getMappings();
        $results = [];

        $toImport = [];
        foreach ($upcs as $upc) {
            $product = $this->searchService->searchByUpc($upc);
            if ($product === null) {
                $product = new ScanResult($upc);
            }
            $results[] = $product;
            if ($product->getTitle() !== null || $product->getAmazonPrice() !== null) {
                $toImport[] = $product;
            }
        }

        if (!empty($toImport)) {
            $this->importer->importBatch($toImport, $priceBookMap);
            foreach ($toImport as $product) {
                $product->setFaImported(true);
            }
        }

        return $results;
    }
}
