<?php

/**
 * Fired during plugin activation
 *
 * @link       https://github.com/EsubalewAmenu/
 * @since      1.0.0
 *
 * @package    Ds_Binance_Trader
 * @subpackage Ds_Binance_Trader/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Ds_Binance_Trader
 * @subpackage Ds_Binance_Trader/includes
 * @author     Esubalew Amenu <esubalew.a2009@gmail.com>
 */
class Ds_Binance_Trader_Activator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate()
	{

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');


		self::ds_bt_trade_table();
	}

	public static function ds_bt_trade_table()
	{

		global $table_prefix, $wpdb;

		$wp_xcc_table = $table_prefix . "ds_bt_trades";

		if ($wpdb->get_var("show tables like '$wp_xcc_table'") != $wp_xcc_table) {
			$sql = "CREATE TABLE `" . $wp_xcc_table . "` ( ";
			$sql .= "  `id` int(10) unsigned NOT NULL AUTO_INCREMENT, ";

			$sql .= "  `symbol` varchar(10) NOT NULL, ";
			
			$sql .= "  `side` varchar(10) NOT NULL, ";
			$sql .= "  `type` varchar(10) NOT NULL, ";
			$sql .= "  `quantity` DECIMAL(16,8) NOT NULL, ";
			$sql .= "  `price` varchar(50) NOT NULL, ";

			$sql .= "  `status` varchar(10) NOT NULL, "; //NEW,
			$sql .= "  `buy_id` int(10) unsigned, ";
			$sql .= "  `profit_loss` varchar(10), "; //p, l
			$sql .= "  `profit_loss_amount` varchar(10), ";
			
			$sql .= "  `orderId` varchar(255) NOT NULL, ";
			$sql .= "  `orderListId` varchar(255) NOT NULL, ";
			$sql .= "  `clientOrderId` varchar(255) NOT NULL, ";
			$sql .= "  `transactTime` varchar(255) NOT NULL, ";


			$sql .= "  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, ";
			$sql .= "  `updated_at` TIMESTAMP NULL DEFAULT NULL, ";
			$sql .= "  `deleted_at` TIMESTAMP NULL DEFAULT NULL, ";

			$sql .= "  PRIMARY KEY (`id`) ";

			$sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ; ";

			dbDelta($sql);
		}
	}
}
