# UPC2Item — UML Delta

## Class Diagram
```
class Upc2ItemService {
  -scanner: BarcodeScannerInterface
  -searchService: ProductSearchInterface
  -importer: FaItemImporterInterface
  -priceBookMapper: PriceBookMapperInterface
  +processUpc(upcInput: string): ScanResult
  +processBatch(csvContent: string): ScanResult[]
}
class BarcodeScannerService implements BarcodeScannerInterface {
  +scan(input: string): string
  +scanBatch(csvContent: string): string[]
}
class ImageBarcodeScanner implements BarcodeScannerInterface {
  -fallback: BarcodeScannerInterface
  +scan(input: string): string
  +scanBatch(csvContent: string): string[]
}
class ProductSearchService implements ProductSearchInterface {
  -sources: array
  -timeout: int
  +searchByUpc(upc: string): ScanResult|null
  -searchAmazon(upc: string): array|null
  -parseAmazon(html: string, upc: string): array|null
  -searchEbay(upc: string): array|null
  -parseEbay(html: string, upc: string): array|null
  -searchFacebook(upc: string): array|null
  -parseFacebook(html: string, upc: string): array|null
}
class FaItemImportService implements FaItemImporterInterface {
  -db: DatabaseAdapterInterface
  -searchService: ProductSearchInterface
  +import(product: ScanResult, priceBookMap: array): string
  +importBatch(products: ScanResult[], priceBookMap: array): string[]
  -computeBaseCost(product: ScanResult): float
  -findStockByUpc(upc: string): string|false
  -generateStockId(title: string): string
  -upsertPrice(stockId: string, salesTypeId: int, price: string): void
}
class PriceBookMappingService implements PriceBookMapperInterface {
  -db: DatabaseAdapterInterface
  -cache: PriceBookMapping[]
  +getMappings(): array
  +setMapping(sourceName: string, salesTypeId: int): bool
  +isEnabled(sourceName: string): bool
  +getEnabledSalesTypeIds(): int[]
}
class ConfigService implements ConfigServiceInterface {
  -configPath: string
  -config: array
  +getAll(): array
  +set(key: string, value: mixed): bool
}
class FaDatabaseAdapter implements DatabaseAdapterInterface {
  +query(sql: string, errMsg: string): mixed
  +fetch(result: mixed): array
  +numRows(result: mixed): int
  +escape(str: string): string
  +insert(table: string, data: array): bool
  +update(table: string, data: array, where: string): bool
}
class ScanResult {
  -upc: string
  -title: string|null
  -description: string|null
  -amazonPrice: float|null
  -ebayPrice: float|null
  -facebookPrice: float|null
  -faImported: bool
  +getUpc(): string
  +toArray(): array
  +fromArray(row: array): ScanResult
}
class PriceBookMapping {
  -id: int
  -sourceName: string
  -faSalesTypeId: int
  -enabled: bool
}
```

## Sequence Diagram (Live Scan)
```
User -> scan.php: GET /upc2item=1
scan.php -> Upc2ItemService: processUpc('9780000000001')
Upc2ItemService -> BarcodeScannerService: scan(input)
Upc2ItemService -> ProductSearchService: searchByUpc('9780000000001')
ProductSearchService -> Amazon: HTTP GET
ProductSearchService -> eBay: HTTP GET
ProductSearchService -> Facebook: HTTP GET
ProductSearchService --> Upc2ItemService: ScanResult
Upc2ItemService -> PriceBookMappingService: getMappings()
Upc2ItemService -> FaItemImportService: import(result, map)
FaItemImportService -> stock_master: INSERT/UPDATE
FaItemImportService -> prices: UPSERT
FaItemImportService --> Upc2ItemService: stock_id
Upc2ItemService --> scan.php: ScanResult
scan.php --> User: Results page
```

## State Machine (Scan Session)
```
[Idle] -- scan/upload --> [Processing]
[Processing] -- success --> [Idle] (display results)
[Processing] -- network error --> [Idle] (Not found)
[Processing] -- db error --> [Idle] (error message)
```

## Activity Diagram (Batch Import)
```
(start) --> read CSV --> parse UPCs --> for each UPC: search → find? --> if yes: import into FA
    find? -- no --> skip
    import into FA --> next UPC
    next UPC --> all done? -- no --> for each UPC
    all done? -- yes --> (end)
```
