# UPC2Item — FrontAccounting Module

UPC2Item scans barcodes/UPCs, searches Amazon, eBay, and Facebook Marketplace for product data, and imports items into FrontAccounting 2.4.x with pricebook mapping.

## Features
- Live USB barcode scanner input or manual UPC entry
- Batch CSV upload (one UPC per line)
- Auto-search Amazon, eBay, Facebook Marketplace
- Configurable price book mapping (Retail, Amazon, eBay, Facebook → FA sales types)
- GTIN-13 normalization
- Inserts/updates `stock_master` and FA `prices` table
- PHP 7.3+ compatible
- SRP/SOLID/DI architecture
- PHPUnit 9.5 tests with 100% coverage target
- Complete BABOK documentation

## Installation
1. Copy `ksf_FA_Upc2Item` into FA `modules/` directory.
2. In FA: **Installation > Manage Modules**, enable `ksf_FA_Upc2Item`.
3. Module auto-runs `sql/install.sql` on activation.
4. Configure price books at **Inventory > UPC2Item Config**.

## Usage
- **Sales > Orders > UPC2Item**: Live scan or CSV upload.
- **Sales > Orders > UPC2Item Config**: Map marketplace sources to FA sales types.

## Directory Structure
```
ksf_FA_Upc2Item/
  _init/config
  composer.json
  hooks.php
  phpunit.xml
  .gitignore
  pages/
    scan.php
    config.php
  sql/
    install.sql
  vendor/               (created by composer install)
  src/Ksfraser/FrontAccounting/Upc2Item/
    Contracts/           Interfaces
    Models/              Value objects
    Services/            Business logic
  tests/Unit/            PHPUnit tests
  ProjectDcs/            BABOK documentation
```

## Configuration
`_init/config` stores gzip-compressed settings:
- `default_sales_type_id`
- `amazon_enabled`
- `ebay_enabled`
- `facebook_enabled`
- `search_timeout`

## BABOK Documentation
- `ProjectDcs/Use Case.md`
- `ProjectDcs/Business Requirements.md`
- `ProjectDcs/Functional Requirements.md`
- `ProjectDcs/Architecture.md`
- `ProjectDcs/Test Plan.md`
- `ProjectDcs/User Test Cases.md`
- `ProjectDcs/RTM.md`
- `ProjectDcs/UML.md`

## Testing
```bash
composer install
vendor/bin/phpunit --configuration phpunit.xml
```

## License
GPL-3.0
