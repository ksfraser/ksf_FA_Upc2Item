<?php
/**
 * UPC2Item - FrontAccounting Module
 *
 * Scan barcodes/UPCs, search Amazon/eBay/Facebook Marketplace,
 * and import products into FA Items/Inventory with pricebook mapping.
 *
 * @package   ksfraser\FrontAccounting\Upc2Item
 * @author    Kevin Fraser
 * @version   1.0.0
 * @license   GPL-3.0
 *
 * @BABOK Related: FR-UPCS-001, FR-UPCS-002
 *
 * @hook      install           Called when module is installed.
 * @hook      uninstall         Called when module is deleted.
 * @hook      install_options   Register pages under Inventory app.
 * @hook      install_access    Define security areas.
 * @hook      activate_extension Install SQL schema.
 */

define('SS_ksf_FA_Upc2Item', 156 << 8);

class hooks_ksf_FA_Upc2Item extends hooks
{
    var $module_name = 'ksf_FA_Upc2Item';
    var $version = '2.4.3-0';

    /**
     * Register module menus under Inventory application
     *
     * add_module_app() is provided by the KSF app-framework FA build only. On
     * stock FA 2.4.x the method does not exist and calling it during menu
     * construction throws an uncaught exception that blanks every page
     * (footer-only render), so the menu registration is guarded. The module
     * pages remain reachable directly by URL either way.
     */
    function install_options($app) {
        if ($app->id == 'orders' && method_exists($this, 'add_module_app')) {
            $this->add_module_app('upc2item', _("UPC2Item"), 'modules/ksf_FA_Upc2Item/pages/scan.php', SA_ksf_FA_Upc2ItemVIEW);
            $this->add_module_app('upc2item_config', _("UPC2Item Config"), 'modules/ksf_FA_Upc2Item/pages/config.php', SA_ksf_FA_Upc2ItemMANAGE);
        }
    }

    /**
     * Define security permission areas
     */
    function install_access() {
        $security_sections[SS_ksf_FA_Upc2Item] = _("UPC2Item");
        $security_areas['SA_ksf_FA_Upc2ItemVIEW'] = array(
            SS_ksf_FA_Upc2Item | 1,
            _("View UPC2Item")
        );
        $security_areas['SA_ksf_FA_Upc2ItemMANAGE'] = array(
            SS_ksf_FA_Upc2Item | 2,
            _("Manage UPC2Item Config")
        );
        return array($security_areas, $security_sections);
    }

    /**
     * Install database schema on activation
     */
    function activate_extension($company, $check_only=true) {
        $this->ensure_composer_dependencies();

        if (file_exists(dirname(__FILE__) . '/sql/install.sql')) {
            $updates = array('install.sql' => array($this->module_name));
            return $this->update_databases($company, $updates, $check_only);
        }

        return true;
    }

    /**
     * Install composer dependencies if vendor/ missing
     */
    private function ensure_composer_dependencies() {
        $module_dir = dirname(__FILE__);
        $autoload_path = $module_dir . '/vendor/autoload.php';

        if (file_exists($autoload_path)) {
            return;
        }

        $composer_path = $module_dir . '/composer.json';
        if (!file_exists($composer_path)) {
            return;
        }

        chdir($module_dir);
        $output = array();
        $return_code = 0;
        exec('composer install --no-interaction --prefer-dist 2>&1', $output, $return_code);
        if ($return_code !== 0) {
            error_log('KSF Module: composer install failed: ' . implode("\n", $output));
        }
    }
}
