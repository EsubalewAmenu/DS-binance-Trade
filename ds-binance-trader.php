<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/EsubalewAmenu/
 * @since             1.0.0
 * @package           Ds_Binance_Trader
 *
 * @wordpress-plugin
 * Plugin Name:       Binance Trader
 * Plugin URI:        https://datascienceplc.com
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Esubalew Amenu
 * Author URI:        https://github.com/EsubalewAmenu/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ds-binance-trader
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if (!defined("ds_bt"))
    define("ds_bt", "ds_bt");
if (!defined("ds_bt_PLAGIN_DIR"))
    define("ds_bt_PLAGIN_DIR", plugin_dir_path(__FILE__));
if (!defined("ds_bt_PLAGIN_URL"))
    define("ds_bt_PLAGIN_URL", plugin_dir_url(__FILE__));

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'DS_BINANCE_TRADER_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ds-binance-trader-activator.php
 */
function activate_ds_binance_trader() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ds-binance-trader-activator.php';
	Ds_Binance_Trader_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ds-binance-trader-deactivator.php
 */
function deactivate_ds_binance_trader() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ds-binance-trader-deactivator.php';
	Ds_Binance_Trader_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_ds_binance_trader' );
register_deactivation_hook( __FILE__, 'deactivate_ds_binance_trader' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ds-binance-trader.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_ds_binance_trader() {

	$plugin = new Ds_Binance_Trader();
	$plugin->run();

}
run_ds_binance_trader();
