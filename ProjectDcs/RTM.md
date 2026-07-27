# UPC2Item — Requirements Traceability Matrix

| ID | Requirement | Artifact | Test Case |
|----|-------------|----------|-----------|
| FR-UPCS-001 | Module install | `sql/install.sql`, `hooks.php` | _N/A_ |
| FR-UPCS-002 | Barcode scanning | `BarcodeScannerService`, `scan.php` | TC-001, TC-002, TC-003, TC-006 |
| FR-UPCS-003 | Amazon search | `ProductSearchService::searchAmazon`, `parseAmazon` | TC-001, TC-007 |
| FR-UPCS-004 | eBay search | `ProductSearchService::searchEbay`, `parseEbay` | TC-001, TC-007 |
| FR-UPCS-005 | Facebook search | `ProductSearchService::searchFacebook`, `parseFacebook` | TC-001, TC-007 |
| FR-UPCS-006 | FA Item Creation | `FaItemImportService::import` | TC-001, TC-003 |
| FR-UPCS-007 | FA Price Book Population | `FaItemImportService::upsertPrice` | TC-001, TC-004 |
| FR-UPCS-008 | Price Book Configuration | `config.php`, `PriceBookMappingService` | TC-004, TC-007 |
| FR-UPCS-009 | Module Configuration | `ConfigService`, `_init/config` | TC-004 |
| BR-UC-001 | Automate lookup | All components | All TC |
| BR-UC-002 | Multi-source pricing | `ProductSearchService` | TC-001 |
| BR-UC-003 | FA integration | `FaItemImportService` | TC-001, TC-003 |
| BR-UC-004 | Configurable mappings | `config.php`, `PriceBookMappingService` | TC-004 |
| BR-UC-005 | Live + batch | `scan.php` | TC-001, TC-003 |
