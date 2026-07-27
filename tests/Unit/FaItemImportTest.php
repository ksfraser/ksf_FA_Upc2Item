<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use ksfraser\FrontAccounting\Upc2Item\Services\FaItemImportService;
use ksfraser\FrontAccounting\Upc2Item\Services\ProductSearchService;
use ksfraser\FrontAccounting\Upc2Item\Models\ScanResult;
use Tests\Unit\MockDatabaseAdapter;

/**
 * @BABOK Related: FR-UPCS-006, FR-UPCS-007
 */
class FaItemImportTest extends TestCase
{
    private MockDatabaseAdapter $db;
    private FaItemImportService $service;

    protected function setUp(): void
    {
        $this->db = new MockDatabaseAdapter();
        $this->db->seed('0_ksf_upc2item_pricebook_map', [
            ['id' => 1, 'source_name' => 'Retail', 'fa_sales_type_id' => 1, 'enabled' => '1'],
        ]);
        $search = new ProductSearchService([], 5);
        $this->service = new FaItemImportService($this->db, $search);
    }

    public function testComputeBaseCostReturnsLowest(): void
    {
        $ref = new \ReflectionClass($this->service);
        $method = $ref->getMethod('computeBaseCost');
        $method->setAccessible(true);

        $product = new ScanResult('1234567890123');
        $product->setAmazonPrice(10.0)->setEbayPrice(8.0)->setFacebookPrice(12.0);
        $this->assertSame(8.0, $method->invoke($this->service, $product));
    }

    public function testComputeBaseCostReturnsZeroWhenNoPrices(): void
    {
        $ref = new \ReflectionClass($this->service);
        $method = $ref->getMethod('computeBaseCost');
        $method->setAccessible(true);

        $product = new ScanResult('1234567890123');
        $this->assertSame(0.0, $method->invoke($this->service, $product));
    }

    public function testGenerateStockIdIsUnique(): void
    {
        $ref = new \ReflectionClass($this->service);
        $method = $ref->getMethod('generateStockId');
        $method->setAccessible(true);

        $id1 = $method->invoke($this->service, 'Test Book');
        $id2 = $method->invoke($this->service, 'Another Book');
        $this->assertNotEmpty($id1);
        $this->assertNotEmpty($id2);
        $this->assertNotEquals($id1, $id2);
    }

    public function testImportCreatesStockMaster(): void
    {
        $product = new ScanResult('1234567890123');
        $product->setTitle('Test Book')->setAmazonPrice(19.99);

        // Seed an empty stock_master table
        $this->db->seed('0_stock_master', []);

        $stockId = $this->service->import($product, ['Retail' => 1]);
        $this->assertNotEmpty($stockId);
        $rows = $this->db->getTable('0_stock_master');
        $this->assertCount(1, $rows);
        $this->assertSame('Test Book', $rows[0]['description']);
    }

    public function testImportUpsertsExistingStock(): void
    {
        $this->db->seed('0_stock_master', [
            ['stock_id' => 'BOOK-1', 'description' => 'Old Title', 'long_description' => 'UPC:1234567890123', 'category_id' => '1', 'units' => '1', 'gross_cost' => '5.00', 'cost_price' => '5.00'],
        ]);

        $product = new ScanResult('1234567890123');
        $product->setTitle('New Title')->setAmazonPrice(19.99);

        $stockId = $this->service->import($product, ['Retail' => 1]);
        $this->assertSame('BOOK-1', $stockId);
        $rows = $this->db->getTable('0_stock_master');
        $this->assertSame('New Title', $rows[0]['description']);
    }
}
