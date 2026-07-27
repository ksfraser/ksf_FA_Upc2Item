# UPC2Item — Architecture

## System Overview
UPC2Item is a FrontAccounting module that bridges physical product scanning with digital inventory and e-commerce pricing intelligence.

## Container Diagram
```
┌──────────────────────────────────────────────────────┐
│              FrontAccounting 2.4.3                    │
│  ┌────────────────────────────────────────────────┐  │
│  │              ksf_FA_Upc2Item module             │  │
│  │  ┌──────────────┐      ┌────────────────────┐  │  │
│  │  │ scan.php     │      │ config.php         │  │  │
│  │  │ - Live scan  │      │ - Price book map   │  │  │
│  │  │ - CSV upload │      │ - Toggle sources   │  │  │
│  │  └──────┬───────┘      └────────────────────┘  │  │
│  │         │                                       │  │
│  │  ┌──────▼──────────────────────────────────┐   │  │
│  │  │      Upc2ItemService (Facade)            │   │  │
│  │  └──────┬──────────────────────────────────┘   │  │
│  │         │                                       │  │
│  │  ┌──────▼──────┐  ┌──────────────┐  ┌────────┐│  │
│  │  │ Barcode     │  │ Product      │  │ FaItem ││  │
│  │  │ Scanner     │  │ Search       │  │ Import ││  │
│  │  │ Service     │  │ Service      │  │Service ││  │
│  │  └─────────────┘  └──────┬───────┘  └───┬────┘│  │
│  │                          │              │      │  │
│  │  ┌───────────────────────▼──┐  ┌───────▼────┐ │  │
│  │  │ PriceBookMappingService  │  │ DB Adapter │ │  │
│  │  └────────────┬────────────┘  └──────┬─────┘  │  │
│  │               │                      │         │  │
│  └───────────────┼──────────────────────┼─────────┘  │
│                  │                      │             │
│  ┌───────────────▼──────┐    ┌─────────▼──────────┐ │
│  │  0_ksf_upc2item_*    │    │ FA Core Tables     │ │
│  │  scans, items, maps   │    │ stock_master,      │ │
│  │                       │    │ prices, sales_types│ │
│  └──────────────────────┘    └────────────────────┘  │
└──────────────────────────────────────────────────────┘
         │                        │
         ▼                        ▼
┌──────────────────┐   ┌─────────────────────┐
│   Internet       │   │   MariaDB / MySQL   │
│   Amazon/eBay/FB │   │   FA Database       │
└──────────────────┘   └─────────────────────┘
```

## Design Principles
- **SRP**: One class per responsibility (scanning, searching, importing, mapping, config).
- **DI**: All services injected via constructor; no global service locator.
- **DRY**: Shared interfaces in `Contracts/`; common parsing logic in base classes.
- **Testability**: FA DB wrapped in `DatabaseAdapterInterface`; external HTTP mocked in tests.

## Key Classes
| Class | Responsibility |
|-------|---------------|
| `Upc2ItemService` | Facade orchestrating scan → search → import |
| `BarcodeScannerService` | UPC normalization and CSV batch parsing |
| `ProductSearchService` | HTTP search across Amazon/eBay/Facebook |
| `FaItemImportService` | Inserts/updates `stock_master` and `prices` |
| `PriceBookMappingService` | CRUD for source → sales_type mappings |
| `ConfigService` | Gzip-compressed module config |
| `FaDatabaseAdapter` | Wraps FA `db_query`/`db_insert`/`db_update` |
| `ScanResult` | Value object for single UPC result |
| `PriceBookMapping` | Value object for mapping configuration |

## Configuration
- `_init/config` — gzip compressed `Key: Value` pairs
- Default: all sources enabled, sales_type_id = 1, timeout = 15s

## Error Handling
- Invalid UPC: throws `InvalidArgumentException`, caught by page layer.
- HTTP failures: logged, returns null, system marks "Not found".
- DB failures: `db_query` errors propagated; page shows FA error dialog.
