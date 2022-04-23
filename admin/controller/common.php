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
	public function priceToTradeOnSingleCoin()
	{
		return 15;
	}
	public function depend_on_interval()
	{
		return "5m";
		// return "15m";
		// //1m3m5m15m30m1h2h4h6h8h12h1d3d1w1M
	}

	public function recvWindow()
	{
		return 60000;
	}
	public function baseAsset()
	{
		return "BUSD";
		// return "USDT";
	}
	public function getDepth($symbol, $baseAsset, $limit, $key)
	{
		$response = self::sendRequest("GET", "api/v3/depth?symbol=" . $symbol . $baseAsset . "&limit=$limit", $key); // get orderbook (BUY)
		if ($response['code'] == 200 || $response['code'] == 201) {
			$orderBook = json_decode($response['result'], true);
			return array("buy_with" => $orderBook['bids'][0][0], "sell_by" => $orderBook['asks'][0][0]);
			// return array("buy_with" => $orderBook['bids'][count($orderBook)-1][0], "sell_by" => $orderBook['asks'][count($orderBook)-1][0]);
		}
		return null;
	}
	public function buyOrderBook($symbol, $amountToBuy, $baseAsset, $limit, $key)
	{


		$dbSymbol = self::getSymbolFromDB($symbol);

		if ($dbSymbol) {
			$orderBook = self::sendRequest("GET", "api/v3/depth?symbol=" . $symbol . $baseAsset . "&limit=$limit", $key); // get orderbook (BUY)
			if ($orderBook['code'] == 200 || $orderBook['code'] == 201) {
				$lastOnOrderBook = json_decode($orderBook['result'], true);
				$lastOnOrderBook = $lastOnOrderBook['bids'][count($lastOnOrderBook) - 1][0];

				$min_lot_size = $dbSymbol->min_lot_size;

				// $amountToBuy -= fmod($amountToBuy, $lastOnOrderBook); //$amountToBuy % $lastOnOrderBook;
				$quantity = $amountToBuy / $lastOnOrderBook;
				// echo " amountToBuy is $amountToBuy buyOrderBook $lastOnOrderBook  min_lot_size  $min_lot_size";
				//get index of one
				$afterPoint = 0;
				for ($i = 0; $i < strlen($min_lot_size) - 1; $i++) {
					if ($min_lot_size[$i] == '1') {
						break;
					} else if ($min_lot_size[$i] == '0')
						$afterPoint++;
				}

				$quantity = self::floorDec($quantity, $afterPoint);
				// echo " afterPoint $afterPoint after float " . $quantity;
				return array("quantity" => $quantity, "lastOnOrderBook" => $lastOnOrderBook, "amountToBuy" => $amountToBuy);
			}
		}
		return null;
	}
	function floorDec($val, $precision = 2)
	{
		if ($precision < 0) {
			$precision = 0;
		}
		$numPointPosition = intval(strpos($val, '.'));
		if ($numPointPosition === 0) { //$val is an integer
			return $val;
		}
		return floatval(substr($val, 0, $numPointPosition + $precision + 1));
	}

	public function scanCrypto($baseAsset)
	{

		$url = "https://scanner.tradingview.com/crypto/scan";

		if (self::depend_on_interval() == "5m") {
			//buy strong, buy and nutral and filtered by changed from open above 0
			$data = '{"filter":[{"left":"change|5","operation":"nempty"},{"left":"exchange","operation":"equal","right":"BINANCE"},{"left":"volume","operation":"in_range","right":[2000000,50000000]},{"left":"change|5","operation":"greater","right":0},{"left":"change_from_open","operation":"greater","right":0},{"left":"name,description","operation":"match","right":"' . $baseAsset . '"}],"options":{"lang":"en"},"filter2":{"operator":"and","operands":[{"operation":{"operator":"or","operands":[{"expression":{"left":"Recommend.All","operation":"in_range","right":[0.1,0.5]}},{"expression":{"left":"Recommend.All","operation":"in_range","right":[-0.1,0.1]}},{"expression":{"left":"Recommend.All","operation":"in_range","right":[0.5,1]}}]}}]},"markets":["crypto"],"symbols":{"query":{"types":[]},"tickers":[]},"columns":["base_currency_logoid","currency_logoid","name","close","change","volume","Recommend.All","ask","exchange","change|5","change|15","change_from_open","description","type","subtype","update_mode","pricescale","minmov","fractional","minmove2"],"sort":{"sortBy":"change|5","sortOrder":"desc"},"range":[0,150]}';
			//buy strong buy sell and nutral and filtered by changed from open
			// $data = '{"filter":[{"left":"change|5","operation":"nempty"},{"left":"exchange","operation":"equal","right":"BINANCE"},{"left":"volume","operation":"in_range","right":[2000000,50000000]},{"left":"change|5","operation":"greater","right":0.1},{"left":"change_from_open","operation":"greater","right":0.1},{"left":"name,description","operation":"match","right":"' . $baseAsset . '"}],"options":{"lang":"en"},"filter2":{"operator":"and","operands":[{"operation":{"operator":"or","operands":[{"expression":{"left":"Recommend.All","operation":"in_range","right":[0.1,0.5]}},{"expression":{"left":"Recommend.All","operation":"in_range","right":[-0.1,0.1]}},{"expression":{"left":"Recommend.All","operation":"in_range","right":[-0.5,-0.1]}},{"expression":{"left":"Recommend.All","operation":"in_range","right":[0.5,1]}}]}}]},"markets":["crypto"],"symbols":{"query":{"types":[]},"tickers":[]},"columns":["base_currency_logoid","currency_logoid","name","close","change","volume","Recommend.All","ask","exchange","change|5","change|15","change_from_open","description","type","subtype","update_mode","pricescale","minmov","fractional","minmove2"],"sort":{"sortBy":"change|5","sortOrder":"desc"},"range":[0,150]}';			
			// only buy and strong buy
			// $data = '{"filter":[{"left":"change|5","operation":"nempty"},{"left":"exchange","operation":"equal","right":"BINANCE"},{"left":"volume","operation":"in_range","right":[2000000,50000000]},{"left":"change|5","operation":"greater","right":0.1},{"left":"Recommend.All","operation":"nequal","right":0.1},{"left":"name,description","operation":"match","right":"' . $baseAsset . '"}],"options":{"lang":"en"},"filter2":{"operator":"and","operands":[{"operation":{"operator":"or","operands":[{"expression":{"left":"Recommend.All","operation":"in_range","right":[0.1,0.5]}},{"expression":{"left":"Recommend.All","operation":"in_range","right":[0.5,1]}}]}}]},"markets":["crypto"],"symbols":{"query":{"types":[]},"tickers":[]},"columns":["base_currency_logoid","currency_logoid","name","close","change","volume","Recommend.All","ask","exchange","change|5","change|15","description","type","subtype","update_mode","pricescale","minmov","fractional","minmove2"],"sort":{"sortBy":"change|5","sortOrder":"desc"},"range":[0,150]}';

			// response is order by 5m change :- symbol, last price, change24 %, volume, tech rating 24, ask, exchange, change5m %, change15m %
		} else if (self::depend_on_interval() == "15m") {
			//buy strong, buy and nutral and filtered by changed from open above 0
			$data = '{"filter":[{"left":"change|15","operation":"nempty"},{"left":"exchange","operation":"equal","right":"BINANCE"},{"left":"volume","operation":"in_range","right":[2000000,50000000]},{"left":"change|15","operation":"greater","right":0},{"left":"change_from_open","operation":"greater","right":0},{"left":"name,description","operation":"match","right":"' . $baseAsset . '"}],"options":{"lang":"en"},"filter2":{"operator":"and","operands":[{"operation":{"operator":"or","operands":[{"expression":{"left":"Recommend.All","operation":"in_range","right":[0.1,0.5]}},{"expression":{"left":"Recommend.All","operation":"in_range","right":[-0.1,0.1]}},{"expression":{"left":"Recommend.All","operation":"in_range","right":[0.5,1]}}]}}]},"markets":["crypto"],"symbols":{"query":{"types":[]},"tickers":[]},"columns":["base_currency_logoid","currency_logoid","name","close","change","volume","Recommend.All","ask","exchange","change|5","change|15","change_from_open","description","type","subtype","update_mode","pricescale","minmov","fractional","minmove2"],"sort":{"sortBy":"change|15","sortOrder":"desc"},"range":[0,150]}';
			//buy strong buy sell and nutral and filtered by changed from open
			// $data = '{"filter":[{"left":"ask","operation":"nempty"},{"left":"exchange","operation":"equal","right":"BINANCE"},{"left":"volume","operation":"in_range","right":[2000000,50000000]},{"left":"change|15","operation":"greater","right":0.1},{"left":"change_from_open","operation":"greater","right":0.1},{"left":"name,description","operation":"match","right":"' . $baseAsset . '"}],"options":{"lang":"en"},"filter2":{"operator":"and","operands":[{"operation":{"operator":"or","operands":[{"expression":{"left":"Recommend.All","operation":"in_range","right":[0.1,0.5]}},{"expression":{"left":"Recommend.All","operation":"in_range","right":[-0.1,0.1]}},{"expression":{"left":"Recommend.All","operation":"in_range","right":[-0.5,-0.1]}},{"expression":{"left":"Recommend.All","operation":"in_range","right":[0.5,1]}}]}}]},"markets":["crypto"],"symbols":{"query":{"types":[]},"tickers":[]},"columns":["base_currency_logoid","currency_logoid","name","close","change","volume","Recommend.All","ask","exchange","change|5","change|15","change_from_open","description","type","subtype","update_mode","pricescale","minmov","fractional","minmove2"],"sort":{"sortBy":"ask","sortOrder":"desc"},"range":[0,150]}';
			// only buy and strong buy
			// $data = '{"filter":[{"left":"change|15","operation":"nempty"},{"left":"exchange","operation":"equal","right":"BINANCE"},{"left":"volume","operation":"in_range","right":[2000000,50000000]},{"left":"change|15","operation":"greater","right":0.1},{"left":"Recommend.All","operation":"nequal","right":0.1},{"left":"name,description","operation":"match","right":"' . $baseAsset . '"}],"options":{"lang":"en"},"filter2":{"operator":"and","operands":[{"operation":{"operator":"or","operands":[{"expression":{"left":"Recommend.All","operation":"in_range","right":[0.1,0.5]}},{"expression":{"left":"Recommend.All","operation":"in_range","right":[0.5,1]}}]}}]},"markets":["crypto"],"symbols":{"query":{"types":[]},"tickers":[]},"columns":["base_currency_logoid","currency_logoid","name","close","change","volume","Recommend.All","ask","exchange","change|5","change|15","description","type","subtype","update_mode","pricescale","minmov","fractional","minmove2"],"sort":{"sortBy":"change|15","sortOrder":"desc"},"range":[0,150]}';
			// response is order by 15m change :- symbol, last price, change24 %, volume, tech rating 24, ask, exchange, change5m %, change15m %
		} else
			return null;
		// " Please choose correct BUSD_USDT and interval FIRST";

		$response = self::postAPI($url, $data);

		if ($response['code'] == 200 || $response['code'] == 201) {
			return json_decode($response['result'], true);
		}
		return null;
	}
	function symbol_status($fullSymbol, $depend_on_interval)
	{

		$cmd = "python3 " . ds_bt_PLAGIN_DIR . 'admin/controller/recommendation/ta.py --symbol ' . $fullSymbol . ' --interval ' . $depend_on_interval;
		$output = shell_exec($cmd);
		// echo $output;
		if (str_starts_with($output, "{'RECOMMENDATION': 'STRONG_BUY'")) {
			return "STRONG_BUY";
		} else if (str_starts_with($output, "{'RECOMMENDATION': 'BUY'"))
			return "BUY";
		else if (str_starts_with($output, "{'RECOMMENDATION': 'SELL'"))
			return "SELL";
		else if (str_starts_with($output, "{'RECOMMENDATION': 'STRONG_SELL'"))
			return "STRONG_SELL";
		else if (str_starts_with($output, "{'RECOMMENDATION': 'NEUTRAL'"))
			return "NEUTRAL";
		else
			return $output;
	}
	public function order($symbol, $side, $type, $quantity, $price, $recvWindow, $key, $secret)
	{
		echo "order symbol $symbol side $side quantity $quantity price $price </br>\n";
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
    public function cancelBuyOrdersIfTooksLong($openOrders)
    {
        foreach ($openOrders as $openOrder) {
            if (abs(round(microtime(true) * 1000) - $openOrder['time']) > 120000 && $openOrder['side'] == 'BUY') {
                //cancel order $openOrder['orderId']
                echo 'order ' . $openOrder['symbol'] . " " . $openOrder['side'] . ' tooks too long CANCELED!\n';

                $openOrders = self::cancelSingleOrder($openOrder['symbol'],  $openOrder['orderId']);
                // print_r($openOrders);
            }
        }
    }
	public function openOrders()
	{
		$response = self::signedRequest('GET', 'api/v3/openOrders', [
			'recvWindow' => self::recvWindow(),
		], self::api_key(), self::api_secret());
		// print_r($response);
		if ($response['code'] == 200 || $response['code'] == 201) {
			return json_decode($response['result'], true);
		}
		return null;
	}

	public function cancelSingleOrder($symbol, $orderId)
	{
		$cancelResponse = self::signedRequest('DELETE', 'api/v3/order', [
			'symbol' => $symbol,
			"orderId" => $orderId,
			'recvWindow' => self::recvWindow(),
		], self::api_key(), self::api_secret());

		if ($cancelResponse['code'] == 200 || $cancelResponse['code'] == 201) {
			return json_decode($cancelResponse['result'], true);
		}

		return null;
	}
	public function cancelOrder($symbol, $origClientOrderId, $key, $secret)
	{
		echo "cancel symbol $symbol origClientOrderId $origClientOrderId order </br>\n";
		// place order, make sure API key and secret are set, recommend to test on testnet.

		$response = self::signedRequest('DELETE', 'api/v3/openOrders', [
			'symbol' => $symbol,
			// "origClientOrderId" => "myOrder1",//$origClientOrderId,
			'recvWindow' => self::recvWindow(),
		], $key, $secret);
		print_r($response);
		// if ($response['code'] == 200 || $response['code'] == 201) {

		// 	$allOrders = json_decode($response['result'], true);
		// 	foreach ($allOrders as $order) {
		// 		echo "order is ";
		// 		echo "order1 is " . $order['orderId'];
		// 		$cancelResponse = self::signedRequest('DELETE', 'api/v3/order', [
		// 			'symbol' => $symbol,
		// 			"orderId" => $order['orderId'],
		// 			'recvWindow' => self::recvWindow(),
		// 		], $key, $secret);
		// 		echo " response of cancel is ";
		// 		print_r($cancelResponse);
		// 	}
		// }

		return $response['code'];
	}

	function getSymbolFromDB($symbol)
	{
		global $table_prefix, $wpdb;
		$wp_ds_bt_symbols_table = $table_prefix . "ds_bt_symbols";

		return $wpdb->get_row("SELECT * FROM " . $wp_ds_bt_symbols_table . ' WHERE symbol="' . $symbol . '"');
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
				$exchangeInfos = self::sendRequest("GET", "api/v3/exchangeInfo", $key);
				$tickers = self::sendRequest("GET", "api/v3/ticker/24hr", $key);

				if (($exchangeInfos['code'] == 200 || $exchangeInfos['code'] == 201) && ($tickers['code'] == 200 || $tickers['code'] == 201)) {
					$exchangeInfos = json_decode($exchangeInfos['result'], true);
					$tickers = json_decode($tickers['result'], true);

					// $count = 0;
					foreach ($exchangeInfos['symbols'] as $symbol) {
						if ($symbol['quoteAsset'] == self::baseAsset()) {

							foreach ($tickers as $ticker) {
								if ($ticker['symbol'] == $symbol['symbol']) {

									$baseAsset = $wpdb->get_row("SELECT * FROM " . $wp_ds_bt_symbols_table . ' WHERE symbol="' . $symbol['baseAsset'] . '"');

									$min_lot_size = 0;
									foreach ($symbol['filters'] as $filter) {
										if ($filter['filterType'] == "PRICE_FILTER")
											$precisionPrice = $filter['minPrice'];
										else if ($filter['filterType'] == "LOT_SIZE")
											$min_lot_size = $filter['minQty'];
										// else if ($filter['filterType'] == "PERCENT_PRICE")
										// $precisionQuantity = $filter['multiplierUp'];
									}

									if ($baseAsset) { //update
										$data = [
											'precisionPrice' => $precisionPrice,
											'min_lot_size' => $min_lot_size,
											'isSpotTradingAllowed' => $symbol['isSpotTradingAllowed'],
											'isMarginTradingAllowed' => $symbol['isMarginTradingAllowed'],
											// 'permissions' => implode(" ",$symbol['permissions']),

											'lastPrice' => $ticker['lastPrice'],
											'asset_volume' => $ticker['volume'],
											'busd_volume' => $ticker['quoteVolume'],
											'priceChange' => $ticker['priceChange'],
											'priceChangePercent' => $ticker['priceChangePercent'],

											'currentAsset' => $baseAsset->currentAsset, //$ticker['currentAsset'],
											'busdValue' => $baseAsset->busdValue, //$ticker['busdValue'],
										];
										$where = ['symbol' => $symbol['baseAsset']];
										$wpdb->update($wp_ds_bt_symbols_table, $data, $where);
									} else { //insert

										$wpdb->insert($wp_ds_bt_symbols_table, array(
											'symbol' => $symbol['baseAsset'],
											'precisionPrice' => $precisionPrice,
											'min_lot_size' => $min_lot_size,
											'isSpotTradingAllowed' => $symbol['isSpotTradingAllowed'],
											'isMarginTradingAllowed' => $symbol['isMarginTradingAllowed'],
											// 'permissions' => implode(" ",$symbol['permissions']),

											'lastPrice' => $ticker['lastPrice'],
											'asset_volume' => $ticker['volume'],
											'busd_volume' => $ticker['quoteVolume'],
											'priceChange' => $ticker['priceChange'],
											'priceChangePercent' => $ticker['priceChangePercent'],

											'currentAsset' => 0, //$ticker['currentAsset'],
											'busdValue' => 0, //$ticker['busdValue'],

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
			'limit' => $last_n_history
		]);

		$response = self::sendRequest("GET", "api/v3/klines?${query}", $key);

		if ($response['code'] == 200 || $response['code'] == 201) {

			return json_decode($response['result'], true);
		} else return array('error' => '45632');
	}
	public function myTrades($symbol, $limit, $key, $secret)
	{

		// place order, make sure API key and secret are set, recommend to test on testnet.
		$response = self::signedRequest('GET', 'api/v3/myTrades', [
			'symbol' => $symbol,
			'limit' => $limit,
		], $key, $secret);
		if ($response['code'] == 200 || $response['code'] == 201) {
			return json_decode($response['result'], false);
		}
		return null;
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
		if ($method == "DELETE")
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		else
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

	public function myAccount($key, $secret)
	{

		// get account information, make sure API key and secret are set
		$response = self::signedRequest('GET', 'api/v3/account', [], $key, $secret);
		// echo "json_encode " . json_encode($response);

		if ($response['code'] == 200 || $response['code'] == 201) {

			return json_decode($response['result'], true);
		}
		return null;
	}

	public static function postAPI($url, $data)
	{

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);

		$header = array();


		curl_setopt($ch, CURLOPT_POST, 1);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		$header[] = 'Content-Type: application/json';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		// Receive server response ...
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		return array("code" => $http_code, "result" => $result);
	}
	// public static function callAPI($method, $url, $data)
	// {
	// 	$curl = curl_init();

	// 	switch ($method) {
	// 		case "POST":
	// 			curl_setopt($curl, CURLOPT_POST, 1);

	// 			if ($data)
	// 				curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	// 			break;
	// 		case "PUT":
	// 			curl_setopt($curl, CURLOPT_PUT, 1);
	// 			break;
	// 		default:
	// 			if ($data)
	// 				$url = sprintf("%s?%s", $url, http_build_query($data));
	// 	}

	// 	curl_setopt($curl, CURLOPT_URL, $url);
	// 	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

	// 	$result = curl_exec($curl);

	// 	curl_close($curl);

	// 	// print_r( $result );

	// 	return $result;
	// }
}
