<?php
declare(strict_types=1);

namespace ksfraser\FrontAccounting\Upc2Item\Models;

/**
 * Price book mapping configuration entity.
 * 
 * @UML Note: Entity in class diagram
 * @BABOK Related: FR-UPCS-008
 */
class PriceBookMapping
{
    private int $id;
    private string $sourceName;
    private int $faSalesTypeId;
    private bool $enabled;

    public function __construct(int $id, string $sourceName, int $faSalesTypeId, bool $enabled = true)
    {
        $this->id = $id;
        $this->sourceName = $sourceName;
        $this->faSalesTypeId = $faSalesTypeId;
        $this->enabled = $enabled;
    }

    public function getId(): int { return $this->id; }
    public function getSourceName(): string { return $this->sourceName; }
    public function getFaSalesTypeId(): int { return $this->faSalesTypeId; }
    public function isEnabled(): bool { return $this->enabled; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'source_name' => $this->sourceName,
            'fa_sales_type_id' => $this->faSalesTypeId,
            'enabled' => $this->enabled ? 1 : 0,
        ];
    }
}
