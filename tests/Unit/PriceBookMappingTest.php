<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use ksfraser\FrontAccounting\Upc2Item\Services\PriceBookMappingService;
use ksfraser\FrontAccounting\Upc2Item\Models\PriceBookMapping;
use Tests\Unit\MockDatabaseAdapter;

/**
 * @BABOK Related: FR-UPCS-008
 */
class PriceBookMappingTest extends TestCase
{
    private MockDatabaseAdapter $db;
    private PriceBookMappingService $service;

    protected function setUp(): void
    {
        $this->db = new MockDatabaseAdapter();
        $this->db->seed('0_ksf_upc2item_pricebook_map', [
            ['id' => 1, 'source_name' => 'Retail', 'fa_sales_type_id' => 1, 'enabled' => '1'],
            ['id' => 2, 'source_name' => 'Amazon', 'fa_sales_type_id' => 1, 'enabled' => '1'],
            ['id' => 3, 'source_name' => 'Ebay', 'fa_sales_type_id' => 1, 'enabled' => '1'],
            ['id' => 4, 'source_name' => 'Facebook', 'fa_sales_type_id' => 1, 'enabled' => '1'],
        ]);
        $this->service = new PriceBookMappingService($this->db);
    }

    public function testGetMappingsReturnsAll(): void
    {
        $map = $this->service->getMappings();
        $this->assertArrayHasKey('Retail', $map);
        $this->assertArrayHasKey('Amazon', $map);
        $this->assertSame(1, $map['Retail']);
    }

    public function testSetMappingUpdatesMapping(): void
    {
        $this->service->setMapping('Amazon', '2');
        $map = $this->service->getMappings();
        $this->assertSame(2, $map['Amazon']);
    }

    public function testIsEnabledReturnsTrue(): void
    {
        $this->assertTrue($this->service->isEnabled('Retail'));
    }

    public function testIsEnabledReturnsFalseForDisabled(): void
    {
        $this->db = new MockDatabaseAdapter();
        $this->db->seed('0_ksf_upc2item_pricebook_map', [
            ['id' => 1, 'source_name' => 'Retail', 'fa_sales_type_id' => 1, 'enabled' => '0'],
        ]);
        $svc = new PriceBookMappingService($this->db);
        $this->assertFalse($svc->isEnabled('Retail'));
    }
}
