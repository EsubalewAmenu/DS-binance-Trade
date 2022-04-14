<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/EsubalewAmenu/
 * @since      1.0.0
 *
 * @package    Ds_Binance_Trader
 * @subpackage Ds_Binance_Trader/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Ds_Binance_Trader
 * @subpackage Ds_Binance_Trader/includes
 * @author     Esubalew Amenu <esubalew.a2009@gmail.com>
 */
class Ds_Binance_Trader
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Ds_Binance_Trader_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('DS_BINANCE_TRADER_VERSION')) {
			$this->version = DS_BINANCE_TRADER_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'ds-binance-trader';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Ds_Binance_Trader_Loader. Orchestrates the hooks of the plugin.
	 * - Ds_Binance_Trader_i18n. Defines internationalization functionality.
	 * - Ds_Binance_Trader_Admin. Defines all hooks for the admin area.
	 * - Ds_Binance_Trader_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-ds-binance-trader-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-ds-binance-trader-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-ds-binance-trader-admin.php';

		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/controller/buysell.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/controller/common.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/controller/spot.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/controller/holder.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/controller/tradingview.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/controller/margin.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/controller/api/base_api.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-ds-binance-trader-public.php';

		$this->loader = new Ds_Binance_Trader_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Ds_Binance_Trader_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{

		$plugin_i18n = new Ds_Binance_Trader_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{

		$plugin_admin = new Ds_Binance_Trader_Admin($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');


		// $Ds_bt_buysell = new Ds_bt_buysell();
		// $Ds_bt_buysell->main();

		// $Ds_bt_spot = new Ds_bt_spot();
		// $Ds_bt_spot->main();

		$DS_bt_admin_base_api = new DS_bt_admin_base_api();
		$this->loader->add_action('rest_api_init', $DS_bt_admin_base_api, 'rest_check_trade', 1, 1);
		$this->loader->add_action('rest_api_init', $DS_bt_admin_base_api, 'rest_check_margin_trade', 1, 1);
		$this->loader->add_action('rest_api_init', $DS_bt_admin_base_api, 'rest_check_hold', 1, 1);
		$this->loader->add_action('rest_api_init', $DS_bt_admin_base_api, 'rest_check_tradingview', 1, 1);


		// function ds_bt_check_schedule($schedules)
		// {
		// 	$length = 60;

		// 	if (!isset($schedules["ds_bt_check_schedule"])) {
		// 		$schedules["ds_bt_check_schedule"] = array(
		// 			'interval' =>  $length,
		// 			'display' => __('Once every 2 minutes')
		// 		);
		// 	}
		// 	return $schedules;
		// }
		// add_filter('cron_schedules', 'ds_bt_check_schedule');

		// if (!wp_next_scheduled('ds_bt_check_schedule_task_hook')) {
		// 	// echo "wp_next_scheduled('ds_bt_check_schedule_task_hook') was not seted";
		// 	wp_schedule_event(time(), 'ds_bt_check_schedule', 'ds_bt_check_schedule_task_hook');
		// }
		// // else
		// // echo "wp_next_scheduled('ds_bt_check_schedule_task_hook') was seted";

		// add_action('ds_bt_check_schedule_task_hook', 'ds_bt_check_schedule_task_hook_function');


		// //reset scheduled
		// // if (array_key_exists('submit_xcc_admin_settings', $_POST)) {
		// // 	wp_clear_scheduled_hook('ds_bt_check_schedule_task_hook');
		// // }

		// function ds_bt_check_schedule_task_hook_function()
		// {
		// 	$Ds_bt_spot = new Ds_bt_spot();
		// 	$Ds_bt_spot->main();
		// }
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{

		$plugin_public = new Ds_Binance_Trader_Public($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');


		//  node api call

		// 		const axios = require('axios');
		// const get = async () => {
		//     axios.get('https://dashencon.com/tezt/wp-json/ds_bt/v1/trade/spot').then(res => {
		//         console.log(res.data);
		//     })
		// }
		// setInterval(get, 4000)
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Ds_Binance_Trader_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}
}
