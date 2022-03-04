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
class Ds_bt_margin
{

    public function __construct()
    {
    }

    public function main()
    {
        $secret = 'tSicM8dB17cncJzmKt4PnGxMh1OXE8aIBnbMnyEnayVNlXpgJhLKjqTZlXZp7yDO';
        $key = '0red2ruc3xogwntDl658JYQaNJAjx8wRQSbSGILRvjRMeHiGEt9Y3dcqp6X5wHf0';

        $symbol = "JASMY";
        $interval = "30m";
        //1m3m5m15m30m1h2h4h6h8h12h1d3d1w1M
        $recvWindow = 50000;

        $tradeType = "fixed_profit"; // normal_profit, fixed_profit
        $fixed_profit_value = 0.001;

        // self::myMarginAssets($symbol, $interval, $key, $secret);

        self::getCurrentLoan($key, $secret);
    }

    public function getCurrentLoan($key, $secret)
    {

        $query = self::buildQuery([
            'asset' => "BNB",
        ]);

        $response = self::signedRequest('GET', 'sapi/v1/margin/loan', $query, $key, $secret);

        echo "test start</br>";
        print_r($response);
        echo "test end</br>";

        // if ($response['code'] == 200 || $response['code'] == 201) {

        //     $response = json_decode($response['result'], true);

        //     if (isset($response['price'])) {
        //         // echo "current $symbol price is " . $response['price'] . "</br>";
        //         return $response['price'];
        //     } else return -1;
        // } else return -1;
    }
    public function myMarginAssets($symbol, $interval, $key, $secret)
    {
        // $currentPrice = self::getCurrentPrice($symbol . "BUSD", $key, $secret);
        // echo "test";
        // $klinePrices = self::kline($symbol, $interval, $key, $secret); // price range
        // print_r($klinePrices);
        // get account information, make sure API key and secret are set
        $response = self::signedRequest('GET', 'sapi/v1/margin/account', [], $key, $secret);
        // echo json_encode($response);
        // echo "end";

        if ($response['code'] == 200 || $response['code'] == 201) {

            $response = json_decode($response['result'], true);

            global $table_prefix, $wpdb;
            $wp_ds_table = $table_prefix . "ds_bt_symbols";

            foreach ($response['userAssets'] as $asset) {

                // echo "asset is " . $asset['asset']."</br>";
                if ($asset['asset'] != "BUSD" && $asset['free'] > 0) {
                    // echo "there is " . $asset['free'] . " free " . $asset['asset'] . " ";

                    $dbSymbol = $wpdb->get_row("SELECT * FROM " . $wp_ds_table . " WHERE symbol='" . $asset['asset'] . "' and market_price!='-1'");

                    if (!$dbSymbol) {
                        $currentPrice = self::getCurrentPrice($asset['asset'] . "BUSD", $key, $secret); // price range
                        echo "Current price * free is " . ($asset['free'] * $currentPrice) . " where current price is " . $currentPrice . "</br>";

                        $dbSymbol = $wpdb->get_row("SELECT * FROM " . $wp_ds_table . " WHERE symbol='" . $asset['asset'] . "'");
                        if ($dbSymbol) {
                            $data = ['market_price' => $currentPrice];
                            $where = ['symbol' => $asset['asset']];
                            $wpdb->update($wp_ds_table, $data, $where);
                        } else {
                            $wpdb->insert($wp_ds_table, array(
                                'symbol' => $asset['asset'],
                                'precisionPrice' => 10,
                                'precisionQuantity' => 10,
                                'is_available_on_margin' => "3",
                                'market_price' => $currentPrice,

                            ));
                            $dbSymbol = $wpdb->get_row("SELECT * FROM " . $wp_ds_table . " WHERE symbol='" . $asset['asset'] . "'");
                        }
                    } else $currentPrice = $dbSymbol->market_price;
                    $precisionPrice = $dbSymbol->precisionPrice;
                    $precisionQuantity = $dbSymbol->precisionQuantity;

                    if (($asset['free'] * $currentPrice) > 13) {
                        echo $asset['asset'] . " precisionPrice=" . $precisionPrice . " precisionQuantity=" . $precisionQuantity . "</br>";
                        $currentPrice = self::getCurrentPrice($asset['asset'] . "BUSD", $key, $secret); // price range
                        if ($currentPrice != -1) {
                            $data = ['market_price' => $currentPrice];
                            $where = ['symbol' => $asset['asset']];
                            $wpdb->update($wp_ds_table, $data, $where);
                        }
                        echo "sell will be ordered for " . $asset['asset'] . "BUSD";
                        self::requestNewOrder($asset['asset'] . "BUSD", "SELL", $asset['free'], $precisionPrice, $precisionQuantity, $interval, $key, $secret);
                    }
                }

                if ($asset['asset'] == "BUSD") {
                    echo "current BUSD is " . $asset['free'];
                    echo "</br>";

                    $asset['free'] = $asset['free'] - (($asset['free'] * 0.5) / 100);

                    if ($asset['free'] > 13) self::requestNewOrder($symbol . $asset['asset'], "BUY", $asset['free'], $precisionPrice, $precisionQuantity, $interval, $key, $secret);
                    // return $asset['free'];
                } //else return 0;
            }
        } else return 0;
    }

    public function requestNewOrder($symbol, $side, $freeAsset, $precisionPrice, $precisionQuantity, $interval, $key, $secret) //should pass buy or sell
    {
        $type = "LIMIT";
        $recvWindow = "50000";

        echo "new order Started</br>";
        $klinePrices = self::kline($symbol, $interval, $key, $secret); // price range
        print_r($klinePrices);
        $currentPrice = self::getCurrentPrice($symbol, $key, $secret); // price range

        // get my balance
        // $freeAsset = 110;

        if ($klinePrices['success'] == "true") {
            if ($currentPrice > 0) {
                // echo "side is " . $side;
                if ($side == "BUY") {

                    if ($currentPrice > $klinePrices['low']) $price = round($klinePrices['low'], $precisionPrice);
                    else $price = self::precisionAmount($currentPrice, $precisionPrice);

                    $quantity = self::precisionAmount(($freeAsset / $price), $precisionQuantity);
                    self::order($symbol, $side, $type, $quantity, $price, $recvWindow, $key, $secret);
                } else if ($side == "SELL") {

                    if ($currentPrice > $klinePrices['high']) $price = self::precisionAmount($currentPrice, $precisionPrice);
                    else $price = self::precisionAmount($klinePrices['high'], $precisionPrice);

                    $quantity = self::precisionAmount($freeAsset, $precisionQuantity);
                    self::order($symbol, $side, $type, $quantity, $price, $recvWindow, $key, $secret);
                }
            } else echo "We can't get current market price";
        } else echo "Kline returned false";
    }
    public function order($symbol, $side, $type, $quantity, $price, $recvWindow, $key, $secret)
    {
        echo "symbol = " . $symbol . "</br>";
        echo "price = " . $price . "</br>";
        echo "quantity = " . $quantity . "</br>";
        echo "side = " . $side . "</br>";

        // place order, make sure API key and secret are set, recommend to test on testnet.
        // $response = self::signedRequest('POST', 'sapi/v1/margin/order', [
        //     'symbol' => $symbol,
        //     'side' => $side,
        //     'type' => $type,
        //     'quantity' => $quantity,
        //     'price' => $price,
        //     'recvWindow' => $recvWindow,
        // ], $key, $secret);

        // if ($response['code'] == 200 || $response['code'] == 201) {

        //     $jsonResponse = json_decode($response['result'], true);
        //     print_r($jsonResponse);
        //     echo "Ordered simbol is " . $jsonResponse['symbol'] . "</br>";

        //     global $table_prefix, $wpdb;

        //     $wp_ds_table = $table_prefix . "ds_bt_trades";

        //     $dbResult = $wpdb->insert($wp_ds_table, array(
        //         'symbol' => $jsonResponse['symbol'],
        //         'side' => $jsonResponse['side'],
        //         'type' => $jsonResponse['type'],
        //         'quantity' => $jsonResponse['origQty'],
        //         'price' => $jsonResponse['price'],
        //         'status' => "NEW",

        //         'orderId' => $jsonResponse['orderId'],
        //         'orderListId' => $jsonResponse['orderListId'],
        //         'clientOrderId' => $jsonResponse['clientOrderId'],
        //         'transactTime' => $jsonResponse['transactTime'],
        //         'market' => 'MARGIN',

        //     ));
        //     // echo "dbResult is " . $dbResult;
        // } else {
        //     echo "response is error";
        //     print_r($response['result']);
        // }

        // echo json_encode($response);
    }

    public function precisionAmount($value, $precision)
    {
        if ($precision == 1) $precision = 10;
        else if ($precision == 2) $precision = 100;
        else if ($precision == 3) $precision = 1000;
        else if ($precision == 4) $precision = 10000;
        else if ($precision == 5) $precision = 100000;
        else if ($precision == 6) $precision = 1000000;

        return floor($value * $precision) / $precision;
    }
    public function kline($symbol, $interval, $key, $secret)
    {
        $query = self::buildQuery([
            'symbol' => $symbol,
            'interval' => $interval,
            'limit' => 1
        ]);
        // echo "interval is " . $interval;
        $response = self::sendRequest("GET", "api/v3/klines?${query}", $key);
        // print_r($response);
        if ($response['code'] == 200 || $response['code'] == 201) {

            $response = json_decode($response['result'], true);
            // echo "resp";
            // print_r(count($response));

            // echo "</br>kline result is :-</br>";

            $single = $response[0];

            $high = $single[2];
            $low = $single[3];


            // $avarage = ($high + $low) / 2;
            // $lowHalfOfAvarage = ($avarage + $low) / 2;
            // $lowHalfOfAvarage = ($lowHalfOfAvarage + $low) / 2; // buy when it comes too low

            // $HighHalfOfAvarage = ($high + $avarage) / 2;

            // echo "Low is " . $low . " high is " . $high . "</br>";
            // echo "avarage " . $avarage . " lowHalfOfAvarage is " . $lowHalfOfAvarage . "</br>";
            // echo "avarage " . $avarage . " HighHalfOfAvarage is " . $HighHalfOfAvarage . "</br>";

            // echo json_encode($response);

            return array("success" => "true", "low" => $low, "high" => $high); //, "lower" => $lowHalfOfAvarage, "higher" => $HighHalfOfAvarage);
        } else array("success" => "false");
    }
    public function getCurrentPrice($symbol, $key, $secret)
    {

        $response = self::sendRequest("GET", "sapi/v1/margin/priceIndex?symbol=$symbol", $key);

        // print_r( $response);
        // echo "test";
        if ($response['code'] == 200 || $response['code'] == 201) {

            $response = json_decode($response['result'], true);

            if (isset($response['price'])) {
                // echo "current $symbol price is " . $response['price'] . "</br>";
                return $response['price'];
            } else return -1;
        } else return -1;
    }

    function signature($query_string, $secret)
    {
        return hash_hmac('sha256', $query_string, $secret);
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
}
