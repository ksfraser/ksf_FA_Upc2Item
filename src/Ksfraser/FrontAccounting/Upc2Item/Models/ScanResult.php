<?php
declare(strict_types=1);

namespace ksfraser\FrontAccounting\Upc2Item\Models;

/**
 * Represents a single scanned item result.
 * 
 * @UML Note: Entity in class diagram
 * @BABOK Related: FR-UPCS-002, FR-UPCS-003
 */
class ScanResult
{
    private string $upc;
    private ?string $title;
    private ?string $description;
    private ?string $imageUrl;
    private ?string $category;
    private ?string $brand;
    private ?string $model;
    private ?float $amazonPrice;
    private ?float $amazonRetail;
    private ?float $ebayPrice;
    private ?float $ebayRetail;
    private ?float $facebookPrice;
    private ?float $facebookRetail;
    private bool $faImported;
    private ?string $faStockId;

    public function __construct(string $upc)
    {
        $this->upc = $upc;
        $this->faImported = false;
    }

    public function getUpc(): string { return $this->upc; }
    public function getTitle(): ?string { return $this->title; }
    public function setTitle(?string $title): self { $this->title = $title; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $desc): self { $this->description = $desc; return $this; }
    public function getImageUrl(): ?string { return $this->imageUrl; }
    public function setImageUrl(?string $url): self { $this->imageUrl = $url; return $this; }
    public function getCategory(): ?string { return $this->category; }
    public function setCategory(?string $cat): self { $this->category = $cat; return $this; }
    public function getBrand(): ?string { return $this->brand; }
    public function setBrand(?string $brand): self { $this->brand = $brand; return $this; }
    public function getModel(): ?string { return $this->model; }
    public function setModel(?string $model): self { $this->model = $model; return $this; }
    public function getAmazonPrice(): ?float { return $this->amazonPrice; }
    public function setAmazonPrice(?float $p): self { $this->amazonPrice = $p; return $this; }
    public function getAmazonRetail(): ?float { return $this->amazonRetail; }
    public function setAmazonRetail(?float $p): self { $this->amazonRetail = $p; return $this; }
    public function getEbayPrice(): ?float { return $this->ebayPrice; }
    public function setEbayPrice(?float $p): self { $this->ebayPrice = $p; return $this; }
    public function getEbayRetail(): ?float { return $this->ebayRetail; }
    public function setEbayRetail(?float $p): self { $this->ebayRetail = $p; return $this; }
    public function getFacebookPrice(): ?float { return $this->facebookPrice; }
    public function setFacebookPrice(?float $p): self { $this->facebookPrice = $p; return $this; }
    public function getFacebookRetail(): ?float { return $this->facebookRetail; }
    public function setFacebookRetail(?float $p): self { $this->facebookRetail = $p; return $this; }
    public function isFaImported(): bool { return $this->faImported; }
    public function setFaImported(bool $v): self { $this->faImported = $v; return $this; }
    public function getFaStockId(): ?string { return $this->faStockId; }
    public function setFaStockId(?string $id): self { $this->faStockId = $id; return $this; }

    public function toArray(): array
    {
        return [
            'upc' => $this->upc,
            'title' => $this->title,
            'description' => $this->description,
            'image_url' => $this->imageUrl,
            'category' => $this->category,
            'brand' => $this->brand,
            'model' => $this->model,
            'amazon_price' => $this->amazonPrice,
            'amazon_retail' => $this->amazonRetail,
            'ebay_price' => $this->ebayPrice,
            'ebay_retail' => $this->ebayRetail,
            'facebook_price' => $this->facebookPrice,
            'facebook_retail' => $this->facebookRetail,
            'fa_imported' => $this->faImported ? 1 : 0,
            'fa_stock_id' => $this->faStockId,
        ];
    }

    public static function fromArray(array $row): self
    {
        $s = new self((string)($row['upc'] ?? ''));
        $s->title = $row['title'] ?? null;
        $s->description = $row['description'] ?? null;
        $s->imageUrl = $row['image_url'] ?? null;
        $s->category = $row['category'] ?? null;
        $s->brand = $row['brand'] ?? null;
        $s->model = $row['model'] ?? null;
        $s->amazonPrice = $row['amazon_price'] !== null ? (float)$row['amazon_price'] : null;
        $s->amazonRetail = $row['amazon_retail'] !== null ? (float)$row['amazon_retail'] : null;
        $s->ebayPrice = $row['ebay_price'] !== null ? (float)$row['ebay_price'] : null;
        $s->ebayRetail = $row['ebay_retail'] !== null ? (float)$row['ebay_retail'] : null;
        $s->facebookPrice = $row['facebook_price'] !== null ? (float)$row['facebook_price'] : null;
        $s->facebookRetail = $row['facebook_retail'] !== null ? (float)$row['facebook_retail'] : null;
        $s->faImported = (bool)($row['fa_imported'] ?? 0);
        $s->faStockId = $row['fa_stock_id'] ?? null;
        return $s;
    }
}
