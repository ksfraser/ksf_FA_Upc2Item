<?php
// UPC2Item - Configuration Page
$page_security = 'SA_ksf_FA_Upc2ItemMANAGE';
$path_to_root = "../../..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");

page(_("UPC2Item - Price Book Configuration"), false, false, "");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sources = ['Retail', 'Amazon', 'Ebay', 'Facebook'];
    foreach ($sources as $source) {
        $key = 'map_' . strtolower($source);
        if (isset($_POST[$key])) {
            $sql = "UPDATE " . TB_PREF . "ksf_upc2item_pricebook_map 
                    SET fa_sales_type_id=" . (int)$_POST[$key] . " 
                    WHERE source_name='" . db_escape($source) . "'";
            db_query($sql, 'cannot update pricebook map');
        }
        $enabledKey = 'enabled_' . strtolower($source);
        $enabled = isset($_POST[$enabledKey]) ? 1 : 0;
        $sql = "UPDATE " . TB_PREF . "ksf_upc2item_pricebook_map 
                SET enabled=" . (int)$enabled . " 
                WHERE source_name='" . db_escape($source) . "'";
        db_query($sql, 'cannot update pricebook map');
    }
    echo "<div class='alert alert-success'>" . _("Configuration saved.") . "</div>";
}

// Load current mappings
$mappings = [];
$sql = "SELECT source_name, fa_sales_type_id, enabled FROM " . TB_PREF . "ksf_upc2item_pricebook_map ORDER BY id";
$result = db_query($sql, 'cannot query pricebook map');
while ($row = db_fetch($result)) {
    $mappings[$row['source_name']] = $row;
}

// Load all sales types
$salesTypes = [];
$st = db_query("SELECT id, type FROM " . TB_PREF . "sales_types ORDER BY type", 'cannot query sales_types');
while ($row = db_fetch($st)) {
    $salesTypes[] = $row;
}

echo "<h2>" . _("Price Book Mapping") . "</h2>";
echo "<p class='text-muted'>" . _("Associate each marketplace source to a FrontAccounting sales type (price book).") . "</p>";

start_form(false);
start_table(TABLESTYLE, "style='width:60%;'");
table_header([_('Source'), _('FA Sales Type / Price Book'), _('Enabled')]);
foreach ($mappings as $source => $row) {
    alt_table_row($row);
    label_cell($source);
    $selected = (int)$row['fa_sales_type_id'];
    echo "<td><select name='map_" . strtolower($source) . "' style='width:100%;'>";
    foreach ($salesTypes as $st) {
        $sel = ($st['id'] == $selected) ? " selected" : "";
        echo "<option value='" . (int)$st['id'] . "'{$sel}>" . htmlspecialchars($st['type']) . "</option>";
    }
    echo "</select></td>";
    $checked = (bool)$row['enabled'] ? " checked" : "";
    echo "<td><input type='checkbox' name='enabled_" . strtolower($source) . "' value='1'{$checked}></td>";
}
end_table(1);
submit_center('save', _("Save Configuration"));
end_form();

echo "<p><a href='?upc2item=1' class='btn btn-sm'>" . _("Back to Scan") . "</a></p>";
end_page(true);
