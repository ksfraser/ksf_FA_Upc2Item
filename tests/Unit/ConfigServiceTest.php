<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use ksfraser\FrontAccounting\Upc2Item\Services\ConfigService;

/**
 * @BABOK Related: FR-UPCS-009
 */
class ConfigServiceTest extends TestCase
{
    private string $tempDir;
    private ConfigService $service;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/upc2item_test_' . uniqid();
        mkdir($this->tempDir, 0777, true);
        $this->service = new ConfigService($this->tempDir . '/');
    }

    protected function tearDown(): void
    {
        @unlink($this->tempDir . '/config');
        @rmdir($this->tempDir);
    }

    public function testGetAllReturnsDefaultsWhenNoConfig(): void
    {
        $config = $this->service->getAll();
        $this->assertArrayHasKey('default_sales_type_id', $config);
        $this->assertArrayHasKey('amazon_enabled', $config);
    }

    public function testSetUpdatesConfig(): void
    {
        $this->assertTrue($this->service->set('test_key', 'test_value'));
        $config = $this->service->getAll();
        $this->assertSame('test_value', $config['test_key']);
    }

    public function testPersistsAcrossInstances(): void
    {
        $this->service->set('persist_key', 'persist_val');
        $svc2 = new ConfigService($this->tempDir . '/');
        $this->assertSame('persist_val', $svc2->getAll()['persist_key']);
    }
}
