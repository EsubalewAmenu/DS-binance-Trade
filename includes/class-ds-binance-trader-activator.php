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
		self::ds_bt_symbol_table();
		self::ds_bt_settings_create_table();
	}

	public static function ds_bt_settings_create_table()
	{

		global $table_prefix, $wpdb;

		$wp_ds_bt_table = $table_prefix . "ds_bt_settings";

		if ($wpdb->get_var("show tables like '$wp_ds_bt_table'") != $wp_ds_bt_table) {
			$sql = "CREATE TABLE `" . $wp_ds_bt_table . "` ( ";
			$sql .= "  `id` int(10) unsigned NOT NULL AUTO_INCREMENT, ";
			$sql .= "  `_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, ";
			$sql .= "  `value1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, ";
			$sql .= "  `value2` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, ";

			$sql .= "  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP, ";
			$sql .= "  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, ";
			$sql .= "  `deleted_at` TIMESTAMP NULL DEFAULT NULL, ";

			$sql .= "  PRIMARY KEY (`id`) ";
			$sql .= ") ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ; ";

			dbDelta($sql);

			$sql = "INSERT INTO " . $wp_ds_bt_table . " (`_key`, `value1`, `value2`) VALUES ";
			$sql .= "('symbols_last_updated', '02-02-22', ''),";
			$sql .= "('api_owner', 'maedotesuba', ''),";
			$sql .= "('api_secret', 'tSicM8dB17cncJzmKt4PnGxMh1OXE8aIBnbMnyEnayVNlXpgJhLKjqTZlXZp7yDO', ''),";
			$sql .= "('api_key', '0red2ruc3xogwntDl658JYQaNJAjx8wRQSbSGILRvjRMeHiGEt9Y3dcqp6X5wHf0', ''),";
			$sql .= "('recvWindow', '50000', '')";
			// $sql .= "('', '', ''),";

			dbDelta($sql);
		}
	}

	public static function ds_bt_trade_table()
	{

		global $table_prefix, $wpdb;

		$wp_ds_bt_table = $table_prefix . "ds_bt_trades";

		if ($wpdb->get_var("show tables like '$wp_ds_bt_table'") != $wp_ds_bt_table) {
			$sql = "CREATE TABLE `" . $wp_ds_bt_table . "` ( ";
			$sql .= "  `id` int(10) unsigned NOT NULL AUTO_INCREMENT, ";

			$sql .= "  `symbol` varchar(10) NOT NULL, ";

			$sql .= "  `side` varchar(10) NOT NULL, ";
			$sql .= "  `type` varchar(10) NOT NULL, ";
			$sql .= "  `quantity` DECIMAL(16,8) NOT NULL, ";
			$sql .= "  `price`  DECIMAL(16,8) NOT NULL, ";

			$sql .= "  `status` varchar(10) NOT NULL, "; //NEW,
			$sql .= "  `buy_id` int(10) unsigned, ";
			$sql .= "  `profit_loss` varchar(10), "; //p, l
			$sql .= "  `profit_loss_amount`  DECIMAL(16,8), ";
			$sql .= "  `market` varchar(10) NOT NULL, "; //SPOT, MARGIN

			$sql .= "  `orderId` varchar(255) NOT NULL, ";
			$sql .= "  `orderListId` varchar(255) NOT NULL, ";
			$sql .= "  `clientOrderId` varchar(255) NOT NULL, ";
			$sql .= "  `transactTime` varchar(255) NOT NULL, ";


			$sql .= "  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, ";
			$sql .= "  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, ";
			$sql .= "  `deleted_at` TIMESTAMP NULL DEFAULT NULL, ";

			$sql .= "  PRIMARY KEY (`id`) ";

			$sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ; ";

			dbDelta($sql);
		}
	}
	public static function ds_bt_symbol_table()
	{

		global $table_prefix, $wpdb;

		$wp_ds_bt_table = $table_prefix . "ds_bt_symbols";

		if ($wpdb->get_var("show tables like '$wp_ds_bt_table'") != $wp_ds_bt_table) {
			$sql = "CREATE TABLE `" . $wp_ds_bt_table . "` ( ";
			$sql .= "  `id` int(10) unsigned NOT NULL AUTO_INCREMENT, ";

			$sql .= "  `symbol` varchar(10) NOT NULL, ";

			$sql .= "  `precisionPrice`  DECIMAL(16,8) NOT NULL, ";
			$sql .= "  `min_lot_size`  DECIMAL(16,8) NOT NULL, ";
			$sql .= "  `isSpotTradingAllowed` BOOLEAN, ";
			$sql .= "  `isMarginTradingAllowed` BOOLEAN, ";
			$sql .= "  `permissions` varchar(50) NULL, ";
			$sql .= "  `lastPrice`  DECIMAL(16,8) NOT NULL, ";
			$sql .= "  `asset_volume`  DECIMAL(16,8) NOT NULL, ";
			$sql .= "  `busd_volume`  DECIMAL(16,8) NOT NULL, ";
			$sql .= "  `priceChange`  DECIMAL(16,8) NOT NULL, ";
			$sql .= "  `priceChangePercent`  DECIMAL(16,8) NOT NULL, ";

			$sql .= "  `currentAsset`  DECIMAL(16,8) NOT NULL, ";
			$sql .= "  `busdValue`  DECIMAL(16,8) NOT NULL, ";
			

			$sql .= "  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, ";
			$sql .= "  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, ";
			$sql .= "  `deleted_at` TIMESTAMP NULL DEFAULT NULL, ";

			$sql .= "  PRIMARY KEY (`id`) ";

			$sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ; ";

			dbDelta($sql);
		}
	}
}
