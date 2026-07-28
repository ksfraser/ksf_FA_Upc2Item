<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use ksfraser\FrontAccounting\Upc2Item\Services\BarcodeScannerService;

/**
 * @BABOK Related: FR-UPCS-002
 */
class BarcodeScannerTest extends TestCase
{
    private BarcodeScannerService $scanner;

    protected function setUp(): void
    {
        $this->scanner = new BarcodeScannerService();
    }

    public function testScanNormalizes8DigitUpcToGtin13(): void
    {
        $result = $this->scanner->scan('12345678');
        $this->assertSame('0000012345678', $result);
    }

    public function testScanNormalizes12DigitUpcToGtin13(): void
    {
        $result = $this->scanner->scan('123456789012');
        $this->assertSame('0123456789012', $result);
    }

    public function testScanPasses13DigitUpcThrough(): void
    {
        $result = $this->scanner->scan('1234567890123');
        $this->assertSame('1234567890123', $result);
    }

    public function testScanStripsNonDigits(): void
    {
        $result = $this->scanner->scan('ABC-123-456-789-012');
        $this->assertSame('0123456789012', $result);
    }

    public function testScanRejectsInvalidLength(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->scanner->scan('123');
    }

    public function testScanRejectsNonNumeric(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->scanner->scan('ABCDEFGHIJ');
    }

    public function testScanBatchReturnsUniqueUpcs(): void
    {
        $csv = "12345678\n123456789012\n12345678\n\ninvalid\n";
        $result = $this->scanner->scanBatch($csv);
        $this->assertCount(2, $result);
        $this->assertSame('0000012345678', $result[0]);
        $this->assertSame('0123456789012', $result[1]);
    }

    public function testScanBatchEmptyContent(): void
    {
        $result = $this->scanner->scanBatch("");
        $this->assertCount(0, $result);
    }
}
