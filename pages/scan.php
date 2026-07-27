<?php
// UPC2Item - Scan Page
$page_security = 'SA_ksf_FA_Upc2ItemVIEW';
$path_to_root = "../../..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/modules/ksf_FA_Upc2Item/hooks.php");

page(_("UPC2Item - Scan"), false, false, "");

// Autoload composer dependencies
$autoload = dirname(__FILE__) . '/../../../modules/ksf_FA_Upc2Item/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

use ksfraser\FrontAccounting\Upc2Item\Services\{
    BarcodeScannerService,
    ProductSearchService,
    FaItemImportService,
    PriceBookMappingService,
    Upc2ItemService
};
use ksfraser\FrontAccounting\Upc2Item\Models\ScanResult;

$conn = db_connect();
$scanner = new BarcodeScannerService();
$searchService = new ProductSearchService([
    'Amazon' => ['url' => 'https://www.amazon.com/s?k=', 'enabled' => true],
    'Ebay' => ['url' => 'https://www.ebay.com/sch/i.html?_nkw=', 'enabled' => true],
    'Facebook' => ['url' => 'https://www.facebook.com/marketplace/search/?query=', 'enabled' => true],
]);
$priceBookMapper = new PriceBookMappingService($conn);
$importer = new FaItemImportService($conn, $searchService);
$upcService = new Upc2ItemService($conn, $scanner, $searchService, $importer, $priceBookMapper);

$message = '';
$results = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['scan_upc']) && !empty($_POST['upc_input'])) {
        // Live scan (keyboard wedge or manual entry)
        try {
            $result = $upcService->processUpc($_POST['upc_input']);
            $results[] = $result;
            $message = _("Scanned: ") . htmlspecialchars($result->getUpc());
        } catch (\InvalidArgumentException $e) {
            $message = _("Invalid UPC: ") . htmlspecialchars($e->getMessage());
        }
    }

    if (isset($_POST['upload_csv']) && !empty($_FILES['csv_file']['tmp_name'])) {
        $csvContent = file_get_contents($_FILES['csv_file']['tmp_name']);
        $results = $upcService->processBatch($csvContent);
        $message = _("Batch processed: ") . count($results) . " UPCs";
    }
}

// Fetch all sales types for config display on scan page (quick view)
$salesTypes = [];
$st = db_query("SELECT id, type FROM " . TB_PREF . "sales_types", 'cannot query sales_types');
while ($row = db_fetch($st)) {
    $salesTypes[] = $row;
}

echo "<h2>" . _("Scan Barcode / UPC") . "</h2>";

if ($message) {
    echo "<div class='alert alert-success'>" . $message . "</div>";
}

// Live scan form
echo "<div class='card' style='margin-bottom:16px;'>";
echo "<div class='card-header'>" . _("Live Scan") . "</div>";
echo "<p class='text-muted'>" . _("Scan with USB barcode reader or type UPC manually.") . "</p>";
start_form(false);
text_row(_("UPC"), 'upc_input', '', 30);
submit_center('scan_upc', _("Scan and Import"));
end_form();
echo "</div>";

// CSV upload form
echo "<div class='card' style='margin-bottom:16px;'>";
echo "<div class='card-header'>" . _("Batch CSV Upload") . "</div>";
echo "<p class='text-muted'>" . _("Upload a CSV file with one UPC per line.") . "</p>";
start_form(true, true);
file_row(_("CSV File"), 'csv_file');
submit_center('upload_csv', _("Upload and Process"));
end_form();
echo "</div>";

// Results table
if (!empty($results)) {
    echo "<h3>" . _("Scan Results") . "</h3>";
    start_table(TABLESTYLE);
    table_header([_('UPC'), _('Title'), _('Amazon'), _('Ebay'), _('Facebook'), _('FA Item'), _('Imported')]);
    foreach ($results as $r) {
        alt_table_row($r);
        label_cell($r->getUpc());
        label_cell($r->getTitle() ?: '-');
        label_cell($r->getAmazonPrice() !== null ? price_format($r->getAmazonPrice()) : '-');
        label_cell($r->getEbayPrice() !== null ? price_format($r->getEbayPrice()) : '-');
        label_cell($r->getFacebookPrice() !== null ? price_format($r->getFacebookPrice()) : '-');
        label_cell($r->getFaStockId() ?: '-');
        label_cell($r->isFaImported() ? _("Yes") : _("No"));
    }
    end_table(1);
}

echo "<p style='margin-top:16px;'><a href='?upc2item_config=1' class='btn btn-sm'>" . _("Configure Price Books") . "</a></p>";
end_page(true);
