# UPC2Item — Use Case Model

## UC-001: Live Barcode Scan and Import
**Actor**: Inventory Clerk
**Preconditions**: User is logged into FA with `SA_ksf_FA_Upc2ItemVIEW` permission.
**Main Flow**:
1. User navigates to `?upc2item=1`.
2. User scans a barcode using USB scanner or types UPC into input field.
3. System normalizes UPC to GTIN-13.
4. System queries Amazon, eBay, Facebook Marketplace for product data.
5. System retrieves price book mappings from `0_ksf_upc2item_pricebook_map`.
6. System inserts product into `0_stock_master` and `0_prices` tables.
7. System displays confirmation with FA item code and matched prices.
**Postconditions**: New stock item created in FA; prices mapped to configured sales types.

## UC-002: Batch CSV Import
**Actor**: Inventory Clerk
**Preconditions**: User has a CSV file with one UPC per line; logged in.
**Main Flow**:
1. User selects CSV file on scan page.
2. User clicks "Upload and Process".
3. System reads file and normalizes all UPCs.
4. System searches enabled marketplaces for each UPC.
5. System batch-inserts found products into FA.
6. System displays results table with import status per UPC.
**Postconditions**: All discoverable items imported; results shown.

## UC-003: Configure Price Book Mappings
**Actor**: Administrator
**Preconditions**: User has `SA_ksf_FA_Upc2ItemMANAGE`.
**Main Flow**:
1. User navigates to Configuration page.
2. System displays current mappings: Retail, Amazon, eBay, Facebook → FA sales type.
3. User changes mappings and toggles sources on/off.
4. User clicks Save.
5. System updates `0_ksf_upc2item_pricebook_map`.
**Postconditions**: Mappings persisted; new scans use updated mappings.

## UC-004: View Scan Results
**Actor**: Inventory Clerk
**Main Flow**:
1. After scan or batch, system shows results table.
2. User reviews title, prices per source, FA item code, imported flag.
3. User clicks link to view item in FA stock maintenance if needed.

## UC-005: Re-scan Failed UPC
**Actor**: Inventory Clerk
**Preconditions**: Previous scan returned no product data.
**Main Flow**:
1. User identifies failed UPC in results.
2. User re-enters UPC into live scan input.
3. System retries marketplace searches.
4. If found, imports; if not, displays "Not found" message.
