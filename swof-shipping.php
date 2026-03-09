<?php
/**
 * Plugin Name: SwofCommerce Shipping - Tree Table Rates
 * Description: Advanced tree-based table rate shipping for WooCommerce with powerful rule-based shipping cost calculation
 * Version: 1.0.0
 * Author: Swof Media
 * Plugin URI: https://swofmedia.com/plugins/swof-shipping
 * Author URI: https://swofmedia.com
 * Requires PHP: 7.4
 * Requires at least: 5.0
 * Tested up to: 6.5
 * WC requires at least: 7.0
 * WC tested up to: 8.8
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: swof-shipping
 * Domain Path: /languages
 */

/**
 * SwofCommerce Shipping - Tree Table Rate Shipping
 * 
 * Fork of WooCommerce Tree Table Rate Shipping by tablerateshipping.com
 * Modified and enhanced by Swof Media for SwofCommerce
 * 
 * @package SwofCommerce\Shipping
 * @author Swof Media
 * @copyright 2025 Swof Media
 */

if (!defined('ABSPATH')) {
    exit;
}

/** @noinspection PhpDefineCanBeReplacedWithConstInspection */
define('SWOFSHIPPING_ENTRY_FILE', __FILE__);
define('SWOFSHIPPING_VERSION', '1.0.0');
define('SWOFSHIPPING_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SWOFSHIPPING_PLUGIN_URL', plugin_dir_url(__FILE__));

use TrsVendors\Dgm\WpPluginBootstrapGuard\Guard;

if (!class_exists(Guard::class, false)) {
    require_once(__DIR__ . '/vendor/dangoodman/wp-plugin-bootstrap-guard/Guard.php');
}

Guard::checkPrerequisitesAndBootstrap(
    'SwofCommerce Shipping',
    '7.4',
    '5.0',
    '7.0',
    __DIR__ . '/bootstrap.php'
);
