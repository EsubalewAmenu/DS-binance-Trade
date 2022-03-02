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
class Ds_bt_spot
{

    public function __construct()
    {
    }

    public function main()
    {

        global $table_prefix, $wpdb;

        $wp_ds_table = $table_prefix . "ds_bt_trades";

        $orderList = $wpdb->get_results("SELECT * FROM " . $wp_ds_table . " WHERE status='NEW'");

        if ($orderList) {
            echo "There is open order </br>";
            print_r($orderList);
            foreach ($orderList as $order) {
                # code...
                self::openOrder($order);
            }
        } else {
            self::requestNewOrder();
        }
    }

    public function openOrder($order)
    {
        $secret = 'tSicM8dB17cncJzmKt4PnGxMh1OXE8aIBnbMnyEnayVNlXpgJhLKjqTZlXZp7yDO';
        $key = '0red2ruc3xogwntDl658JYQaNJAjx8wRQSbSGILRvjRMeHiGEt9Y3dcqp6X5wHf0';

        $response = self::signedRequest('GET', 'api/v3/order', [
            'symbol' => $order->symbol,
            'orderId' => $order->orderId,
            'origClientOrderId' => $order->clientOrderId,
            'recvWindow' => 50000,
        ], $key, $secret);



        // echo "</br>open order</br>";
        // print_r($response);
        if ($response['code'] == 200 || $response['code'] == 201) {

            $response = json_decode($response['result'], true);
            // print_r($response);

            if ($response['status'] != $order->status) {

                global $table_prefix, $wpdb;
                $wp_ds_table = $table_prefix . "ds_bt_trades";

                $data = ['status' => $response['status']];
                $where = ['orderId' => $order->orderId, 'origClientOrderId' => $order->clientOrderId];
                $wpdb->update($wp_ds_table, $data, $where);


                self::requestNewOrder();
            } else echo "there is no change on status";
        }
    }

    public function requestNewOrder()//should pass buy or sell
    {
        $symbol = "BTCBUSD";
        $side = "BUY";
        $type = "LIMIT";
        $quantity = "0.00256";
        $price = "44090";
        $recvWindow = "50000";

        $precisionPrice = 2;
        $precisionQuantity = 5;

        $secret = 'tSicM8dB17cncJzmKt4PnGxMh1OXE8aIBnbMnyEnayVNlXpgJhLKjqTZlXZp7yDO';
        $key = '0red2ruc3xogwntDl658JYQaNJAjx8wRQSbSGILRvjRMeHiGEt9Y3dcqp6X5wHf0';


        $advisedPrices = self::kline($symbol, $key, $secret); // price range

        $currentPrice = self::getPrice($symbol, $key, $secret); // price range

        // get my balance
        $myAvailableBUSD = 110;

        if ($advisedPrices['success'] == "true") {
            if ($currentPrice > 0) {

                if ($currentPrice > $advisedPrices['lower']) $price = round($advisedPrices['lower'], $precisionPrice);
                else $price = round($currentPrice, $precisionPrice);


                $quantity = round(($myAvailableBUSD / $price), $precisionQuantity);

                echo "myAvailableBUSD = " . $myAvailableBUSD . "</br>";
                echo "price = " . $price . "</br>";
                echo "quantity = " . $quantity . "</br>";

                self::order($symbol, $side, $type, $quantity, $price, $recvWindow, $key, $secret);
                // // self::order($symbol, "SELL", $type, "0.00162", "43750.20", $recvWindow, $key, $secret);

                // self::order($symbol, $side, $type, 0.00250, "40000.00", $recvWindow, $key, $secret);
            } else echo "We can't get current market price";
        } else echo "Kline returned false";
    }

    public function getPrice($symbol, $key, $secret)
    {

        $response = self::sendRequest("GET", "api/v3/ticker/price?symbol=$symbol", $key);

        if ($response['code'] == 200 || $response['code'] == 201) {

            $response = json_decode($response['result'], true);

            if (isset($response['price'])) {
                echo "current price is " . $response['price'];
                echo "</br>";
                return $response['price'];
            } else return 0;
        } else return 0;
    }
    public function kline($symbol, $key, $secret)
    {
        $query = self::buildQuery([
            'symbol' => $symbol,
            'interval' => "5m",
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


            $avarage = ($high + $low) / 2;
            $lowHalfOfAvarage = ($avarage + $low) / 2;
            $HighHalfOfAvarage = ($high + $avarage) / 2;

            echo "Low is " . $low . " high is " . $high . "</br>";
            echo "avarage " . $avarage . " lowHalfOfAvarage is " . $lowHalfOfAvarage . "</br>";
            echo "avarage " . $avarage . " HighHalfOfAvarage is " . $HighHalfOfAvarage . "</br>";

            // echo json_encode($response);

            return array("success" => "true", "lower" => $lowHalfOfAvarage, "higher" => $HighHalfOfAvarage);
        } else array("success" => "false");
    }

    public function order($symbol, $side, $type, $quantity, $price, $recvWindow, $key, $secret)
    {

        // get orderbook
        // $response = self::sendRequest('GET', "api/v3/depth?symbol=$symbol&limit=5");
        // echo json_encode($response);

        // get account information, make sure API key and secret are set
        // $response = self::signedRequest('GET', 'api/v3/account');
        // echo json_encode($response);

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
            echo "dbResult is " . $dbResult;
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
