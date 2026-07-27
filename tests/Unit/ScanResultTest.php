<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use ksfraser\FrontAccounting\Upc2Item\Models\ScanResult;

/**
 * @BABOK Related: FR-UPCS-002
 */
class ScanResultTest extends TestCase
{
    public function testCreateWithUpc(): void
    {
        $result = new ScanResult('1234567890123');
        $this->assertSame('1234567890123', $result->getUpc());
        $this->assertFalse($result->isFaImported());
    }

    public function testSetterMethodsReturnSelf(): void
    {
        $result = new ScanResult('1234567890123');
        $self = $result->setTitle('Test Book');
        $this->assertSame($result, $self);
    }

    public function testToArrayContainsUpc(): void
    {
        $result = new ScanResult('1234567890123');
        $result->setTitle('Test')->setAmazonPrice(19.99)->setEbayPrice(15.00);
        $arr = $result->toArray();
        $this->assertSame('1234567890123', $arr['upc']);
        $this->assertSame('Test', $arr['title']);
        $this->assertSame(19.99, $arr['amazon_price']);
        $this->assertSame(15.00, $arr['ebay_price']);
    }

    public function testFromArrayReconstructs(): void
    {
        $row = [
            'upc' => '1234567890123',
            'title' => 'Book',
            'description' => 'A book',
            'amazon_price' => '19.99',
            'fa_imported' => '1',
            'fa_stock_id' => 'BOOK-1',
        ];
        $result = ScanResult::fromArray($row);
        $this->assertSame('1234567890123', $result->getUpc());
        $this->assertSame('Book', $result->getTitle());
        $this->assertSame(19.99, $result->getAmazonPrice());
        $this->assertTrue($result->isFaImported());
        $this->assertSame('BOOK-1', $result->getFaStockId());
    }

    public function testComputeBaseCostPrefersLowestPrice(): void
    {
        $result = new ScanResult('1234567890123');
        $result->setAmazonPrice(10.00)->setEbayPrice(8.50)->setFacebookPrice(12.00);
        // Base cost is min of all prices
        $base = $this->computeBaseCost($result);
        $this->assertSame(8.50, $base);
    }

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
}
