<?php
/**
 *
 *
 * @wordpress-plugin
 * Plugin Name:       Order Export to Lexware (openTrans) for WooCommerce
 * Description:       Export WooCommerce orders to an openTRANS xml file
 * Version:           1.4.0
 * Author:            Beautiful WP | made in Germany
 * Author URI:        https://beautiful-wp.com/
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       order-to-lexware-nscwto
 * Domain Path:       /languages
 *
 */

if (!defined('WPINC')) {
    die;
}

define('NSC_WTO_VERSION', '1.4.0');
define("NSC_WTO_PLUGIN_DIR", dirname(__FILE__));
define("NSC_WTO_PLUGIN_URL", plugin_dir_url(__FILE__));

require plugin_dir_path(__FILE__) . 'includes/class-woo-order-to-lexware-nscwto.php';

function add_settings_link_nscwto($links)
{
    $settings_link = '<a href="admin.php?page=woo-order-export-to-lexware-nscwto">' . __('Export Orders') . '</a>';
    array_push($links, $settings_link);
    return $links;
}
add_filter("plugin_action_links_" . plugin_basename(__FILE__), 'add_settings_link_nscwto');

function run_woo_order_to_lexware_nscwto()
{

    $plugin = new Order_To_Lexware_nscwto();
    $plugin->run();

}
run_woo_order_to_lexware_nscwto();
