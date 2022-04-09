<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/EsubalewAmenu
 * @since      1.0.0
 *
 * @package    Ds_bt
 * @subpackage Ds_bt/friendship
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Ds_bt
 * @subpackage Ds_bt/friendship
 * @author     Esubalew Amenu <esubalew.a2009@gmail.com>
 */
class Ds_bt_common
{

	public function __construct()
	{
	}
	public function api_secret()
	{
		return 'tSicM8dB17cncJzmKt4PnGxMh1OXE8aIBnbMnyEnayVNlXpgJhLKjqTZlXZp7yDO';
	}
	public function api_key()
	{
		return '0red2ruc3xogwntDl658JYQaNJAjx8wRQSbSGILRvjRMeHiGEt9Y3dcqp6X5wHf0';
	}

	public function recvWindow()
	{
		return 60000;
	}

	public function order($symbol, $side, $type, $quantity, $price, $recvWindow, $key, $secret)
	{
		// place order, make sure API key and secret are set, recommend to test on testnet.
		$response = self::signedRequest('POST', 'api/v3/order', [
			'symbol' => $symbol,
			'side' => $side,
			'type' => $type,
			'timeInForce' => 'GTC',
			'quantity' => $quantity,
			'price' => $price,
			'recvWindow' => $recvWindow,
			// 'newClientOrderId' => 'my_order', // optional
			'newOrderRespType' => 'FULL' //optional
		], $key, $secret);

		return $response;
	}
	function isSymbolsUpdated($key)
	{
		global $table_prefix, $wpdb;
		$wp_ds_bt_settings_table = $table_prefix . "ds_bt_settings";
		$wp_ds_bt_symbols_table = $table_prefix . "ds_bt_symbols";
		$setting = $wpdb->get_row("SELECT * FROM " . $wp_ds_bt_settings_table . ' WHERE _key="symbols_last_updated"');
		if ($setting) {
			if ($setting->value1 != date("d-m-y")) {
				// once a day (by checking symbols_last_updated from setting)
				// {
				$exchangeInfos = $GLOBALS['Ds_bt_common']->sendRequest("GET", "api/v3/exchangeInfo", $key);
				$tickers = $GLOBALS['Ds_bt_common']->sendRequest("GET", "api/v3/ticker/24hr", $key);

				if (($exchangeInfos['code'] == 200 || $exchangeInfos['code'] == 201) && ($tickers['code'] == 200 || $tickers['code'] == 201)) {
					$exchangeInfos = json_decode($exchangeInfos['result'], true);
					$tickers = json_decode($tickers['result'], true);

					// $count = 0;
					foreach ($exchangeInfos['symbols'] as $symbol) {
						if ($symbol['quoteAsset'] == 'BUSD') {

							foreach ($tickers as $ticker) {
								if ($ticker['symbol'] == $symbol['symbol']) {

									$baseAsset = $wpdb->get_row("SELECT * FROM " . $wp_ds_bt_symbols_table . ' WHERE symbol="' . $symbol['baseAsset'] . '"');

									$min_lot_size = 0;
									foreach ($symbol['filters'] as $filter) {
										if ($filter['filterType'] == "PERCENT_PRICE")
											$precisionQuantity = $filter['multiplierUp'];
										else if ($filter['filterType'] == "LOT_SIZE")
											$min_lot_size = $filter['minQty'];
									}

									if ($baseAsset) { //update
										$data = [
											'precisionPrice' => -1,
											'precisionQuantity' => $precisionQuantity,
											'isSpotTradingAllowed' => $symbol['isSpotTradingAllowed'],
											'isMarginTradingAllowed' => $symbol['isMarginTradingAllowed'],
											'min_lot_size' => $min_lot_size,
											// 'permissions' => implode(" ",$symbol['permissions']),

											'lastPrice' => $ticker['lastPrice'],
											'asset_volume' => $ticker['volume'],
											'busd_volume' => $ticker['quoteVolume'],
											'priceChange' => $ticker['priceChange'],
											'priceChangePercent' => $ticker['priceChangePercent'],
										];
										$where = ['symbol' => $symbol['baseAsset']];
										$wpdb->update($wp_ds_bt_symbols_table, $data, $where);
									} else { //insert

										$wpdb->insert($wp_ds_bt_symbols_table, array(
											'symbol' => $symbol['baseAsset'],
											'precisionPrice' => -1,
											'precisionQuantity' => $precisionQuantity,
											'isSpotTradingAllowed' => $symbol['isSpotTradingAllowed'],
											'isMarginTradingAllowed' => $symbol['isMarginTradingAllowed'],
											'min_lot_size' => $min_lot_size,
											// 'permissions' => implode(" ",$symbol['permissions']),

											'lastPrice' => $ticker['lastPrice'],
											'asset_volume' => $ticker['volume'],
											'busd_volume' => $ticker['quoteVolume'],
											'priceChange' => $ticker['priceChange'],
											'priceChangePercent' => $ticker['priceChangePercent'],

										));
									}
								}
							}
						}
					}
					$data = ['value1' => date("d-m-y")];
					$where = ['_key' => "symbols_last_updated"];
					$wpdb->update($wp_ds_bt_settings_table, $data, $where);
				}
				return 1;
			} else //if ($setting->value1 == date("d-m-y")) 
			{
				return 1;
			}
		}
		return 0;
	}
	public function kline($symbol, $interval, $last_n_history, $key, $secret)
	{
		$query = self::buildQuery([
			'symbol' => $symbol,
			'interval' => $interval,
			'limit' => $last_n_history + 2
		]);

		$response = self::sendRequest("GET", "api/v3/klines?${query}", $key);

		if ($response['code'] == 200 || $response['code'] == 201) {

			return json_decode($response['result'], true);
		} else return null;
	}
	public function getPrice($symbol, $key, $secret)
	{

		$response = self::sendRequest("GET", "api/v3/ticker/price?symbol=$symbol", $key);

		if ($response['code'] == 200 || $response['code'] == 201) {

			$response = json_decode($response['result'], true);

			if (isset($response['price'])) {
				return $response['price'];
			} else return -1;
		} else return -1;
	}
	function sendRequest($method, $path, $key)
	{


		$BASE_URL = 'https://api.binance.com/'; // production
		// $BASE_URL = 'https://testnet.binance.vision/'; // testnet

		$url = "${BASE_URL}${path}";

		// echo "requested URL: " . PHP_EOL;
		// echo $url . PHP_EOL;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-MBX-APIKEY:' . $key));
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, $method == "POST" ? true : false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$execResult = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		// $response = curl_getinfo($ch);

		// if you wish to print the response headers
		// echo print_r($response);

		curl_close($ch);
		// return json_decode($execResult, true);
		return array("code" => $http_code, "result" => $execResult);
	}

	function signedRequest($method, $path, $parameters = [], $key, $secret)
	{

		$parameters['timestamp'] = round(microtime(true) * 1000);
		$query = self::buildQuery($parameters);
		$signature = self::signature($query, $secret);
		return self::sendRequest($method, "${path}?${query}&signature=${signature}", $key);
	}

	function buildQuery(array $params)
	{
		$query_array = array();
		foreach ($params as $key => $value) {
			if (is_array($value)) {
				$query_array = array_merge($query_array, array_map(function ($v) use ($key) {
					return urlencode($key) . '=' . urlencode($v);
				}, $value));
			} else {
				$query_array[] = urlencode($key) . '=' . urlencode($value);
			}
		}
		return implode('&', $query_array);
	}
	function signature($query_string, $secret)
	{
		return hash_hmac('sha256', $query_string, $secret);
	}
}
