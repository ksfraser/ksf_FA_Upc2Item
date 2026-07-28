<?php
declare(strict_types=1);

namespace ksfraser\FrontAccounting\Upc2Item\Services;

use ksfraser\FrontAccounting\Upc2Item\Contracts\PriceBookMapperInterface;
use ksfraser\FrontAccounting\Upc2Item\Contracts\DatabaseAdapterInterface;
use ksfraser\FrontAccounting\Upc2Item\Models\PriceBookMapping;

/**
 * Price book mapping service.
 * 
 * Reads/writes price book mappings from module database table.
 * 
 * @UML Note: Service class
 * @BABOK Related: FR-UPCS-008
 */
class PriceBookMappingService implements PriceBookMapperInterface
{
    /** @var DatabaseAdapterInterface */
    private $db;

    /** @var PriceBookMapping[] */
    private $cache;

    public function __construct(DatabaseAdapterInterface $db)
    {
        $this->db = $db;
        $this->cache = [];
    }

    /**
     * Get mapping: source name => FA sales_type_id
     * 
     * @return array<string, int>
     */
    public function getMappings(): array
    {
        if (empty($this->cache)) {
            $sql = "SELECT source_name, fa_sales_type_id, enabled FROM " . TB_PREF . "ksf_upc2item_pricebook_map";
            $result = $this->db->query($sql, 'cannot query pricebook map');
            if ($result !== false) {
                while ($row = $this->db->fetch($result)) {
                    $this->cache[] = new PriceBookMapping(
                        (int)$row['id'],
                        $row['source_name'],
                        (int)$row['fa_sales_type_id'],
                        (bool)$row['enabled']
                    );
                }
            }
        }

        $map = [];
        foreach ($this->cache as $m) {
            $map[$m->getSourceName()] = (int)$m->getFaSalesTypeId();
        }
        return $map;
    }

    /**
     * Set mapping for a source.
     * 
     * @param string $source
     * @param string $salesTypeId
     * @param bool $enabled
     * @return void
     */
    public function setMapping(string $source, string $salesTypeId, bool $enabled = true): void
    {
        $sql = "UPDATE " . TB_PREF . "ksf_upc2item_pricebook_map 
                SET fa_sales_type_id=" . (int)$salesTypeId . ", enabled=" . ($enabled ? 1 : 0) . " 
                WHERE source_name='" . $this->db->escape($source) . "'";
        $result = $this->db->query($sql, 'cannot update pricebook map');
        $this->cache = [];
    }

    /**
     * Check if a source is enabled for price lookup.
     * 
     * @param string $sourceName
     * @return bool
     */
    public function isEnabled(string $sourceName): bool
    {
        $sql = "SELECT enabled FROM " . TB_PREF . "ksf_upc2item_pricebook_map WHERE source_name='" . $this->db->escape($sourceName) . "'";
        $result = $this->db->query($sql, 'cannot query pricebook map');
        if ($result !== false && $this->db->numRows($result) > 0) {
            return (bool)$this->db->fetch($result)['enabled'];
        }
        return false;
    }

    /**
     * Get all mapped sales_type IDs for enabled sources.
     * 
     * @return int[]
     */
    public function getEnabledSalesTypeIds(): array
    {
        $sql = "SELECT fa_sales_type_id FROM " . TB_PREF . "ksf_upc2item_pricebook_map WHERE enabled=1";
        $result = $this->db->query($sql, 'cannot query pricebook map');
        $ids = [];
        if ($result !== false) {
            while ($row = $this->db->fetch($result)) {
                $ids[] = (int)$row['fa_sales_type_id'];
            }
        }
        return array_unique($ids);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultSalesTypeId(): string
    {
        return '1';
    }
}
