# UPC2Item — Business Requirements

## BR-UC-001: Automate Product Lookup from Barcodes
**Stakeholder**: Inventory Management
**Need**: Reduce manual stock entry by scanning UPCs and auto-populating item data.
**Business Value**: Faster inventory intake, fewer data entry errors, real-time market price awareness.

## BR-UC-002: Multi-Source Price Comparison
**Stakeholder**: Purchasing / Pricing
**Need**: See retail, Amazon, eBay, and Facebook Marketplace prices for the same product.
**Business Value**: Informed pricing decisions, competitive price tracking.

## BR-UC-003: Seamless Integration with FrontAccounting
**Stakeholder**: Accounting / Operations
**Need**: Imported products must appear in FA Items and Inventory with proper price books.
**Business Value**: Single source of truth; no duplicate entry.

## BR-UC-004: Configurable Price Book Mapping
**Stakeholder**: Administrator
**Need**: Map each marketplace source to an existing FA sales type (price book).
**Business Value**: Leverages existing FA pricing structure; no forced reconfiguration.

## BR-UC-005: Support Both Live and Batch Scanning
**Stakeholder**: Inventory Clerks
**Need**: Scan one item at a time with a USB scanner or bulk-import via CSV.
**Business Value**: Flexibility for small and large intake volumes.

## Assumptions
- FA 2.4.3+ is installed with `stock_master` and `prices` tables available.
- Internet connectivity is available for marketplace searches.
- USB barcode scanners act as keyboard wedge input.

## Constraints
- PHP 7.3 minimum; no PHP 8+ features.
- Marketplace HTML structure may change; scrapers need maintenance.
- FA database queries use global functions (`db_query`, `db_insert`, `db_update`).
