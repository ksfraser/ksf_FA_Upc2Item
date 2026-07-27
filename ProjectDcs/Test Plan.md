# UPC2Item — Test Plan

## Overview
All code follows TDD. Unit tests target 100% coverage of business logic.
Integration tests require a running FA instance with test database.
Tests run via `composer test` (PHPUnit 9.5).

## Unit Test Scope
| Component | Tests | Coverage Target |
|-----------|-------|-----------------|
| `BarcodeScannerService` | 8 tests | 100% |
| `ScanResult` model | 6 tests | 100% |
| `PriceBookMappingService` | 4 tests | 100% |
| `FaItemImportService` | 4 tests | 100% |
| `ConfigService` | 3 tests | 100% |
| `ProductSearchService` | HTML parser tests | 90% |

## Integration Test Scope
| Component | Tests |
|-----------|-------|
| `scan.php` page | Form submit, CSV upload, results render |
| `config.php` page | POST save, mappings reflect in DB |
| DB migration | Install/uninstall scripts |

## Manual / User Test Cases
See `User Test Cases.md`.

## Environment
- PHP 7.3+
- PHPUnit 9.5
- MariaDB 10.5 (test DB)
- cURL enabled
- FA 2.4.3 installed

## CI
- Run on every push to GitHub
- Fail build on warning, incomplete, risky, or skipped tests
- Generate coverage report to `coverage.xml`
