# UPC2Item — Functional Requirements

## FR-UPCS-001: Module Installation
The module shall install via FA's extension mechanism, creating `0_ksf_upc2item_scans`, `0_ksf_upc2item_items`, and `0_ksf_upc2item_pricebook_map` tables.

## FR-UPCS-002: Barcode Scanning
The system shall accept UPC input via:
- Live keyboard-wedge scanning (USB barcode reader or manual entry)
- CSV file upload (one UPC per line)
The system shall normalize input to GTIN-13 format.

## FR-UPCS-003: Amazon Product Search
The system shall search Amazon by UPC and return:
- Product title
- Current price
- Image URL
- Category (if available)

## FR-UPCS-004: eBay Product Search
The system shall search eBay by UPC and return:
- Product title
- Current price
- Category (if available)

## FR-UPCS-005: Facebook Marketplace Search
The system shall search Facebook Marketplace by UPC and return:
- Product title (if available)
- Current price (if available)

## FR-UPCS-006: FA Item Creation
The system shall insert or update a stock item in `stock_master` with:
- `stock_id` auto-generated from title
- `description` = product title
- `long_description` = product description
- `cost_price` = lowest available marketplace price
- `category_id` = resolved from product category or default

## FR-UPCS-007: FA Price Book Population
For each enabled marketplace source, the system shall upsert a row in `prices`:
- `stock_id` = created/updated item
- `sales_type_id` = mapped FA sales type for that source
- `price` = source price

## FR-UPCS-008: Price Book Configuration
The system shall provide a configuration screen mapping:
- Retail → FA sales_type_id
- Amazon → FA sales_type_id
- eBay → FA sales_type_id
- Facebook → FA sales_type_id

## FR-UPCS-009: Module Configuration
The system shall persist module settings (enabled sources, timeout) in `_init/config` gzip format.

## Non-Functional Requirements
- NFR-001: PHP 7.3 compatible; no PHP 8 features.
- NFR-002: Marketplace search timeout ≤ 15 seconds per source.
- NFR-003: CSV batch supports up to 500 UPCs per request.
- NFR-004: Module shall not block FA page load for searches (async via submit).
