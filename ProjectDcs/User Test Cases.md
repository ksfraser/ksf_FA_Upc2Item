# UPC2Item — User Test Cases

## TC-001: Live Scan Valid UPC
1. Navigate to `?upc2item=1`
2. Type `9780000000001` into UPC field, click Scan
3. Expected: System displays title, Amazon/eBay/Facebook prices, FA item created
4. Pass: Item appears in `stock_master` with matching title and prices

## TC-002: Live Scan Invalid UPC
1. Type `ABC123` into UPC field, click Scan
2. Expected: Error "Invalid UPC"
3. Pass: No DB writes, error displayed

## TC-003: Batch CSV Upload
1. Create `upcs.txt` with:
   ```
   9780000000001
   9780000000002
   ```
2. Upload CSV, click Process
3. Expected: Results table shows 2 rows, both imported
4. Pass: Two new items in `stock_master`; prices mapped

## TC-004: Configure Price Books
1. Navigate to `?upc2item_config=1`
2. Map Amazon → Retail sales type, eBay → Wholesale
3. Click Save
4. Scan a new UPC
5. Expected: Amazon price inserted under Retail, eBay under Wholesale
6. Pass: `prices` table contains two rows for same item with different `sales_type_id`

## TC-005: Re-scan After Failed Lookup
1. Scan an invalid/dummy UPC
2. Expected: "Not found" message shown
3. Correct to valid UPC and scan again
4. Expected: Item imported successfully

## TC-006: Empty CSV Upload
1. Upload an empty text file
2. Expected: No errors, zero results
3. Pass: No DB writes, message "Batch processed: 0 UPCs"

## TC-007: Source Disabled
1. Disable Facebook in config
2. Scan an item only found on Facebook
3. Expected: Item not found; no Facebook lookup attempted
4. Pass: 0 results
