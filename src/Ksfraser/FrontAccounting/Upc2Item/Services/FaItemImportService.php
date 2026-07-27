<?php
declare(strict_types=1);

namespace ksfraser\FrontAccounting\Upc2Item\Services;

use ksfraser\FrontAccounting\Upc2Item\Contracts\FaItemImporterInterface;
use ksfraser\FrontAccounting\Upc2Item\Contracts\ProductSearchInterface;
use ksfraser\FrontAccounting\Upc2Item\Contracts\DatabaseAdapterInterface;
use ksfraser\FrontAccounting\Upc2Item\Models\ScanResult;

/**
 * Import scanned products into FrontAccounting Items and Inventory.
 * 
 * Inserts into stock_master with cost_price and maps prices to
 * FA sales_types (price books) via the prices table.
 * 
 * @UML Note: Service class
 * @BABOK Related: FR-UPCS-006, FR-UPCS-007
 */
class FaItemImportService implements FaItemImporterInterface
{
    /** @var DatabaseAdapterInterface */
    private $db;

    /** @var ProductSearchInterface */
    private $searchService;

    public function __construct(DatabaseAdapterInterface $db, ProductSearchInterface $searchService)
    {
        $this->db = $db;
        $this->searchService = $searchService;
    }

    /**
     * Import a single product match into FA stock master and prices.
     * 
     * @param ScanResult $product
     * @param array $priceBookMap Source => FA sales_type_id mapping
     * @return string FA stock_id on success
     * @throws \RuntimeException On import failure
     */
    public function import(ScanResult $product, array $priceBookMap): string
    {
        $upc = $product->getUpc();
        $title = $product->getTitle() ?: "UPC: {$upc}";

        // Determine base cost: use lowest non-null price among sources, or 0
        $baseCost = $this->computeBaseCost($product);

        // Build category or default to 0 (FA misc)
        $categoryId = $this->resolveCategory($product);

        // Check if stock_id already exists
        $existing = $this->findStockByUpc($upc);
        if ($existing !== false) {
            $stockId = $existing;
            $this->db->update(TB_PREF . 'stock_master', [
                'description' => $title,
                'long_description' => $product->getDescription(),
                'cost_price' => (string)$baseCost,
                'category_id' => (string)$categoryId,
            ], "stock_id='" . $this->db->escape($stockId) . "'");
        } else {
            $stockId = $this->generateStockId($title);
            $this->db->insert(TB_PREF . 'stock_master', [
                'stock_id' => $stockId,
                'description' => $title,
                'long_description' => $product->getDescription(),
                'category_id' => (string)$categoryId,
                'units' => '1',
                'gross_cost' => (string)$baseCost,
                'cost_price' => (string)$baseCost,
            ]);
        }

        // Upsert prices for each enabled source
        $prices = [
            'Retail' => $product->getAmazonRetail() ?? $product->getAmazonPrice() ?? null,
            'Amazon' => $product->getAmazonPrice() ?? $product->getAmazonRetail() ?? null,
            'Ebay' => $product->getEbayPrice() ?? $product->getEbayRetail() ?? null,
            'Facebook' => $product->getFacebookPrice() ?? $product->getFacebookRetail() ?? null,
        ];

        foreach ($prices as $source => $price) {
            if ($price === null) {
                continue;
            }
            $salesTypeId = $priceBookMap[$source] ?? null;
            if ($salesTypeId === null || !isset($priceBookMap[$source])) {
                continue;
            }
            $this->upsertPrice($stockId, (int)$salesTypeId, (string)$price);
        }

        return $stockId;
    }

    /**
     * Batch import multiple matches.
     * 
     * @param ScanResult[] $products
     * @param array $priceBookMap
     * @return string[]
     */
    public function importBatch(array $products, array $priceBookMap): array
    {
        $stockIds = [];
        foreach ($products as $product) {
            try {
                $stockIds[] = $this->import($product, $priceBookMap);
            } catch (\RuntimeException $e) {
                // Log and continue
                continue;
            }
        }
        return $stockIds;
    }

    /**
     * Compute base cost from product match prices.
     * 
     * @param ScanResult $product
     * @return float
     */
    private function computeBaseCost(ScanResult $product): float
    {
        $candidates = array_filter([
            $product->getAmazonPrice(),
            $product->getEbayPrice(),
            $product->getFacebookPrice(),
            $product->getAmazonRetail(),
            $product->getEbayRetail(),
            $product->getFacebookRetail(),
        ], fn($p) => $p !== null && $p > 0);

        if (empty($candidates)) {
            return 0.0;
        }

        return (float)min($candidates);
    }

    /**
     * Resolve FA category_id from product category string.
     * 
     * @param ScanResult $product
     * @return int Category ID
     */
    private function resolveCategory(ScanResult $product): int
    {
        $cat = strtolower($product->getCategory() ?? '');
        if (strpos($cat, 'book') !== false || strpos($cat, 'dvd') !== false || strpos($cat, 'film') !== false) {
            return 1; // Default: assume category 1 exists in FA
        }
        return 1;
    }

    /**
     * Find existing stock_id by UPC stored in description or custom field.
     * 
     * @param string $upc
     * @return string|false
     */
    private function findStockByUpc(string $upc)
    {
        $sql = "SELECT stock_id FROM " . TB_PREF . "stock_master WHERE description='UPC:" . $this->db->escape($upc) . "' OR long_description LIKE '%UPC:" . $this->db->escape($upc) . "%'";
        $result = $this->db->query($sql, 'cannot query stock_master');
        if ($result !== false && $this->db->numRows($result) > 0) {
            return $this->db->fetch($result)['stock_id'];
        }
        return false;
    }

    /**
     * Generate unique stock_id from title.
     * 
     * @param string $title
     * @return string
     */
    private function generateStockId(string $title): string
    {
        $base = preg_replace('/[^A-Z0-9]/i', '', substr($title, 0, 10));
        $base = strtoupper(substr($base, 0, 8));
        $suffix = '';
        $i = 1;
        do {
            $suffix = $base . '-' . $i;
            $exists = $this->db->query("SELECT stock_id FROM " . TB_PREF . "stock_master WHERE stock_id='" . $this->db->escape($suffix) . "'", 'cannot check stock_id');
            if ($exists === false || $this->db->numRows($exists) == 0) {
                break;
            }
            $i++;
        } while ($i < 1000);
        return $suffix;
    }

    /**
     * Upsert price for stock_id + sales_type_id.
     * 
     * @param string $stockId
     * @param int $salesTypeId
     * @param string $price
     * @return void
     */
    private function upsertPrice(string $stockId, int $salesTypeId, string $price): void
    {
        $sql = "SELECT price FROM " . TB_PREF . "prices WHERE stock_id='" . $this->db->escape($stockId) . "' AND sales_type_id=" . (int)$salesTypeId;
        $result = $this->db->query($sql, 'cannot query prices');
        if ($result !== false && $this->db->numRows($result) > 0) {
            $this->db->update('prices', ['price' => $price], "stock_id='" . $this->db->escape($stockId) . "' AND sales_type_id=" . (int)$salesTypeId);
        } else {
            $this->db->insert('prices', [
                'stock_id' => $stockId,
                'sales_type_id' => (string)$salesTypeId,
                'price' => $price,
            ]);
        }
    }
}
