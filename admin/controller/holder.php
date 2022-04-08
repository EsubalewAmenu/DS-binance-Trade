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
class Ds_bt_holder
{

    public function __construct()
    {
    }

    public function main()
    {
        $Ds_bt_common_API = new Ds_bt_common_API();
        $secret = $Ds_bt_common_API->api_secret();
        $key = $Ds_bt_common_API->api_key();

        $interval = "5m";
        //1m3m5m15m30m1h2h4h6h8h12h1d3d1w1M

        $recvWindow = 50000;
        global $table_prefix, $wpdb;

        $wp_ds_table = $table_prefix . "ds_bt_trades";

        $orderList = $wpdb->get_results("SELECT * FROM " . $wp_ds_table . " WHERE status='NEW'");

        self::myAccount($interval, $key, $secret);
    }

    public function myAccount($interval, $key, $secret)
    {

        // get account information, make sure API key and secret are set
        $response = self::signedRequest('GET', 'api/v3/account', [], $key, $secret);
        echo json_encode($response);

        // if ($response['code'] == 200 || $response['code'] == 201) {

        //     $response = json_decode($response['result'], true);

        //     global $table_prefix, $wpdb;
        //     $wp_ds_table = $table_prefix . "ds_bt_symbols";

        //     foreach ($response['balances'] as $asset) {


        //         if ($asset['asset'] != "BUSD" && $asset['free'] > 0) {
        //             echo "there is " . $asset['free'] . " free " . $asset['asset'] . " </br>/n";

        //             $dbSymbol = $wpdb->get_row("SELECT * FROM " . $wp_ds_table . " WHERE symbol='" . $asset['asset'] . "' and market_price!='-1'");

        //             if (!$dbSymbol) {
        //                 $currentPrice = self::getPrice($asset['asset'] . "BUSD", $key, $secret); // price range
        //                 // echo "Current price * free is " . ($asset['free'] * $currentPrice) . " where current price is " . $currentPrice . "</br>";

        //                 $dbSymbol = $wpdb->get_row("SELECT * FROM " . $wp_ds_table . " WHERE symbol='" . $asset['asset'] . "'");
        //                 if ($dbSymbol) {
        //                     $data = ['market_price' => $currentPrice];
        //                     $where = ['symbol' => $asset['asset']];
        //                     $wpdb->update($wp_ds_table, $data, $where);
        //                 } else {
        //                     $wpdb->insert($wp_ds_table, array(
        //                         'symbol' => $asset['asset'],
        //                         'precisionPrice' => 10,
        //                         'precisionQuantity' => 10,
        //                         'is_available_on_margin' => "3",
        //                         'market_price' => $currentPrice,

        //                     ));
        //                 }
        //             } else $currentPrice = $dbSymbol->market_price;
        //             $precisionPrice = $dbSymbol->precisionPrice;
        //             $precisionQuantity = $dbSymbol->precisionQuantity;

        //             if (($asset['free'] * $currentPrice) > 13) {

        //                 // 
        //                 echo "Current price * free is " . ($asset['free'] * $currentPrice) . " where current price is " . $currentPrice . "</br>/n";
        //                 // 



        //                 $currentPrice = self::getPrice($asset['asset'] . "BUSD", $key, $secret); // price range
        //                 if ($currentPrice != -1) {
        //                     $data = ['market_price' => $currentPrice];
        //                     $where = ['symbol' => $asset['asset']];
        //                     $wpdb->update($wp_ds_table, $data, $where);
        //                 }
        //                 echo "sell will be ordered for " . $asset['asset'] . "BUSD";
        //                 self::requestNewOrder($asset['asset'] . "BUSD", "SELL", $asset['free'], $precisionPrice, $precisionQuantity, $buy_interval, $sell_interval, $key, $secret);
        //             }
        //         }

        //         if ($asset['asset'] == "BUSD") {
        //             $dbSymbol = $wpdb->get_row("SELECT * FROM " . $wp_ds_table . " WHERE symbol='" . $symbol . "' and market_price!='-1'");

        //             $precisionPrice = $dbSymbol->precisionPrice;
        //             $precisionQuantity = $dbSymbol->precisionQuantity;


        //             echo "current BUSD is " . $asset['free'] . ' precisionPrice ' . $precisionPrice . ' precisionQuantity ' . $precisionQuantity . ' symbol ' . $symbol;
        //             echo "</br>";

        //             $asset['free'] = $asset['free'] - (($asset['free'] * 0.5) / 100);

        //             if ($asset['free'] > 13) self::requestNewOrder($symbol . $asset['asset'], "BUY", $asset['free'], $precisionPrice, $precisionQuantity, $buy_interval, $sell_interval, $key, $secret);
        //             // return $asset['free'];
        //         } //else return 0;
        //     }
        // } else return 0;
    }
    public function requestNewOrder($symbol, $side, $freeAsset, $precisionPrice, $precisionQuantity, $buy_interval, $sell_interval, $key, $secret) //should pass buy or sell
    {
        $type = "LIMIT";
        $recvWindow = "50000";


        if ($side == "SELL")
            $interval = $sell_interval;
        else
            $interval = $buy_interval;

        echo " after sell_interval $sell_interval buy_interval $buy_interval interval is " . $interval . " where side is " . $side;

        $advisedPrices = self::kline($symbol, $interval, $key, $secret); // price range

        $currentPrice = self::getPrice($symbol, $key, $secret); // price range

        // get my balance
        // $freeAsset = 110;

        if ($advisedPrices['success'] == "true") {
            if ($currentPrice > 0) {
                echo "side is " . $side;
                if ($side == "BUY") {

                    echo "currentBuyPrice " . $currentPrice . " low " . $advisedPrices['low'];
                    if ($currentPrice <= $advisedPrices['low'])
                        $advisedPrices['low'] = $currentPrice - ($currentPrice / 100);

                    echo "final low" . $advisedPrices['low'];

                    // if($precisionPrice == 1) $precisionPrice = 10;
                    // else if($precisionPrice == 2) $precisionPrice = 100;
                    // else if($precisionPrice == 3) $precisionPrice = 1000;
                    // else if($precisionPrice == 4) $precisionPrice = 10000;
                    // else if($precisionPrice == 5) $precisionPrice = 100000;
                    // else if($precisionPrice == 6) $precisionPrice = 1000000;
                    // $price = floor($advisedPrices['low'])/$precisionPrice;

                    $price = round($advisedPrices['low'], $precisionPrice);


                    $quantity = round(($freeAsset / $price), $precisionQuantity);

                    // $quantity = floor($freeAsset / $price);// / $precisionQuantity;


                    echo "freeAsset = " . $freeAsset . "</br>";
                    echo "price = " . $price . "</br>";
                    echo "quantity = " . $quantity . "</br>";
                    echo "precisionQuantity = " . $precisionQuantity . "</br>";
                    echo "precisionPrice = " . $precisionPrice . "</br>";

                    self::order($symbol, $side, $type, $quantity, $price, $recvWindow, $key, $secret);
                } else if ($side == "SELL") {

                    global $table_prefix, $wpdb;
                    $wp_ds_table = $table_prefix . "ds_bt_trades";

                    $lastOrder = $wpdb->get_results("SELECT * FROM " . $wp_ds_table . " WHERE symbol='" . $symbol . "' and  status='FILLED' order by id desc LIMIT 1");
                    // echo "size is ". count($lastOrder). ' where sybol is '. $symbol;
                    if ($lastOrder) {
                        echo "last order quantity " . $lastOrder[0]->quantity . ' with ' . $lastOrder[0]->price;
                        if ($freeAsset >= $lastOrder[0]->quantity) {
                            if ($currentPrice <= $lastOrder[0]->price && $advisedPrices['high'] <= $lastOrder[0]->price) {
                                $currentPrice = $lastOrder[0]->price;
                            }
                        }
                    }

                    echo "currentPrice = " . $currentPrice . "</br> hih" . $advisedPrices['high'];


                    if ($currentPrice >= $advisedPrices['high'])
                        $advisedPrices['high'] = $currentPrice + ($currentPrice / 100);;


                    // if ($currentPrice > $advisedPrices['higher']) $price = round($currentPrice, $precisionPrice);
                    // else 
                    $price = round($advisedPrices['high'], $precisionPrice);

                    if ($precisionQuantity == 1) $precisionQuantity = 10;
                    else if ($precisionQuantity == 2) $precisionQuantity = 100;
                    else if ($precisionQuantity == 3) $precisionQuantity = 1000;
                    else if ($precisionQuantity == 4) $precisionQuantity = 10000;
                    else if ($precisionQuantity == 5) $precisionQuantity = 100000;
                    else if ($precisionQuantity == 6) $precisionQuantity = 1000000;

                    echo "freeAsset = " . $freeAsset . "</br>";
                    echo "precisionQuantity = " . $precisionQuantity . "</br>";
                    echo "floor = " . floor($freeAsset * $precisionQuantity) . "</br>";

                    $quantity = floor($freeAsset * $precisionQuantity) / $precisionQuantity;
                    // $quantity = round($freeAsset, $precisionQuantity);
                    // $quantity = $freeAsset;

                    echo "freeAsset = " . $freeAsset . "</br>";
                    echo "price = " . $price . "</br>";
                    echo "quantity = " . $quantity . "</br>";

                    self::order($symbol, $side, $type, $quantity, $price, $recvWindow, $key, $secret);
                }
            } else echo "We can't get current market price";
        } else echo "Kline returned false";

        echo " interval is " . $interval . " where side is " . $side;
    }

    public function getPrice($symbol, $key, $secret)
    {

        $response = self::sendRequest("GET", "api/v3/ticker/price?symbol=$symbol", $key);

        if ($response['code'] == 200 || $response['code'] == 201) {

            $response = json_decode($response['result'], true);

            if (isset($response['price'])) {
                // echo "current price is " . $response['price'];
                // echo "</br>";
                return $response['price'];
            } else return -1;
        } else return -1;
    }
    public function kline($symbol, $interval, $key, $secret)
    {
        $query = self::buildQuery([
            'symbol' => $symbol,
            'interval' => $interval,
            'limit' => 1
        ]);

        $response = self::sendRequest("GET", "api/v3/klines?${query}", $key);

        if ($response['code'] == 200 || $response['code'] == 201) {

            $response = json_decode($response['result'], true);
            echo "resp";
            // print_r(count($response));

            echo "</br>kline result is :-</br>";

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

            return array(
                "success" => "true", //"lower" => $lowHalfOfAvarage, //"higher" => $HighHalfOfAvarage, 
                "low" => $low, "high" => $high
            );
        } else array("success" => "false");
    }

    public function order($symbol, $side, $type, $quantity, $price, $recvWindow, $key, $secret)
    {

        // get orderbook
        // $response = self::sendRequest('GET', "api/v3/depth?symbol=$symbol&limit=5");
        // echo json_encode($response);

        echo "order p=" . $price . ' quantity is' . $quantity;
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

        if ($response['code'] == 200 || $response['code'] == 201) {

            echo "symbol is";

            $jsonResponse = json_decode($response['result'], true);
            print_r($jsonResponse);
            echo $jsonResponse['symbol'];

            global $table_prefix, $wpdb;

            $wp_ds_table = $table_prefix . "ds_bt_trades";

            $dbResult = $wpdb->insert($wp_ds_table, array(
                'symbol' => $jsonResponse['symbol'],
                'side' => $jsonResponse['side'],
                'type' => $jsonResponse['type'],
                'quantity' => $jsonResponse['origQty'],
                'price' => $jsonResponse['price'],
                'status' => "NEW",

                'orderId' => $jsonResponse['orderId'],
                'orderListId' => $jsonResponse['orderListId'],
                'clientOrderId' => $jsonResponse['clientOrderId'],
                'transactTime' => $jsonResponse['transactTime'],

            ));
            // echo "dbResult is " . $dbResult;
        } else {
            echo "response is error";
            print_r($response['result']);
        }

        // echo json_encode($response);
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
