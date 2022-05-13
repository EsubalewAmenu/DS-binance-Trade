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
class Ds_bt_future_common
{

    public function priceToTradeOnSingleCoin()
    {
        return 24;
    }
    public function depend_on_interval()
    {
        return "5m";
        // return "15m";
        // //1m3m5m15m30m1h2h4h6h8h12h1d3d1w1M
    }

    public function baseAsset()
    {
        return "BUSD";
        // return "USDT";
    }

    public function recvWindow()
    {
        return 60000;
    }
    public function buyOrderBook($symbol, $amountToBuy, $baseAsset, $limit, $key)
    {

        $Ds_bt_common = new Ds_bt_common();

        $dbSymbol = $Ds_bt_common->getSymbolFromDB($symbol);

        if ($dbSymbol) {
            $orderBook = self::sendRequest("GET", "fapi/v1/depth?symbol=" . $symbol . $baseAsset . "&limit=$limit", $key); // get orderbook (BUY)
            if ($orderBook['code'] == 200 || $orderBook['code'] == 201) {
                $lastOnOrderBook = json_decode($orderBook['result'], true);
                // print_r($lastOnOrderBook);
                $lastOnOrderBook = $lastOnOrderBook['bids'][$limit - 1][0];

                // $min_lot_size = $dbSymbol->min_lot_size;

                // $amountToBuy -= fmod($amountToBuy, $lastOnOrderBook); //$amountToBuy % $lastOnOrderBook;
                $quantity = $amountToBuy / $lastOnOrderBook;
                $quantity = $Ds_bt_common->precisionQuantity($dbSymbol->min_lot_size, $quantity);

                // echo " afterPoint $afterPoint after float " . $quantity;
                return array("quantity" => $quantity, "lastOnOrderBook" => $lastOnOrderBook, "amountToBuy" => $amountToBuy);
            }
        }
        return null;
    }
    public function order($symbol, $side, $type, $quantity, $price, $stopPrice, $recvWindow, $key, $secret)
    {
        // place order, make sure API key and secret are set, recommend to test on testnet.
        $args = [
            'symbol' => $symbol,
            'side' => $side,
            'type' => $type,
            'timeInForce' => 'GTC',
            'quantity' => $quantity,
            'price' => $price,
            'recvWindow' => $recvWindow,
            // 'stopPrice' => '', // $price + 0.1 percent
            'newOrderRespType' => 'FULL' //optional
        ];
        if ($stopPrice > 0)
            $args[] = array('stopPrice' => $stopPrice);

            print_r($args);

        // $response = self::signedRequest('POST', 'fapi/v1/order', $args, $key, $secret);
        // if ($response['code'] == 200 || $response['code'] == 201) {
        //     $order = json_decode($response['result'], true);
        //     return "order id " . $order['orderId'] . " symbol $symbol side $side origQty " . $order['origQty'] . " price " . $order['price'] . " </br>\n";
        // }
        // return $response;
    }
    public function isNotHold($asset, $positions)
    {

        foreach ($positions as $position) {
            if (str_ends_with($position['symbol'], $asset)) {

                $Ds_bt_common = new Ds_bt_common();

                $dbSymbol = $Ds_bt_common->getSymbolFromDB($asset);

                $amount_holded = $position['free'] + $position['locked'];

                if (($amount_holded * $dbSymbol->lastPrice) < 10)
                    return true;
            }
        }
        return false;
    }

    public function scanCrypto($baseAsset, $positions)
    {

        // echo "test tickers</br>";
        // print_r($positions);

        $tickers = "";
        foreach ($positions as $position) {
            if (str_ends_with($position['symbol'], $baseAsset)) {
                if ($tickers != "") $tickers = $tickers . '|' . $position['symbol'] . "PERP";
                else $tickers = $tickers . $position['symbol'] . "PERP";
            }
        }

        // print_r($tickers);
        // echo "</br>";
        // return null;


        $Ds_bt_common = new Ds_bt_common();

        $url = "https://scanner.tradingview.com/crypto/scan";

        // if (self::depend_on_interval() == "5m") {
        //     //buy strong, buy and nutral and filtered by changed from open above 0
        $data = '{"filter":[{"left":"name","operation":"nempty"},{"left":"exchange","operation":"equal","right":"BINANCE"},{"left":"name,description","operation":"match","right":"' . $tickers . '"}],"options":{"lang":"en"},"markets":["crypto"],"symbols":{"query":{"types":[]},"tickers":[]},"columns":["base_currency_logoid","currency_logoid","name","close","change","volume","Recommend.All","ask","exchange","change|5","change|15","change_from_open","description","type","subtype","update_mode","pricescale","minmov","fractional","minmove2"],"sort":{"sortBy":"name","sortOrder":"desc"},"range":[0,150]}';
        // } else if (self::depend_on_interval() == "15m") {
        //     //buy strong, buy and nutral and filtered by changed from open above 0
        //     $data = '{"filter":[{"left":"change|15","operation":"nempty"},{"left":"exchange","operation":"equal","right":"BINANCE"},{"left":"volume","operation":"in_range","right":[2000000,50000000]},{"left":"change|15","operation":"greater","right":0},{"left":"change_from_open","operation":"greater","right":0},{"left":"name,description","operation":"match","right":"' . $baseAsset . '"}],"options":{"lang":"en"},"filter2":{"operator":"and","operands":[{"operation":{"operator":"or","operands":[{"expression":{"left":"Recommend.All","operation":"in_range","right":[0.1,0.5]}},{"expression":{"left":"Recommend.All","operation":"in_range","right":[-0.1,0.1]}},{"expression":{"left":"Recommend.All","operation":"in_range","right":[0.5,1]}}]}}]},"markets":["crypto"],"symbols":{"query":{"types":[]},"tickers":[]},"columns":["base_currency_logoid","currency_logoid","name","close","change","volume","Recommend.All","ask","exchange","change|5","change|15","change_from_open","description","type","subtype","update_mode","pricescale","minmov","fractional","minmove2"],"sort":{"sortBy":"change|15","sortOrder":"desc"},"range":[0,150]}';
        // } else
        //     return null;
        // " Please choose correct BUSD_USDT and interval FIRST";

        $response = $Ds_bt_common->postAPI($url, $data);

        if ($response['code'] == 200 || $response['code'] == 201) {
            return json_decode($response['result'], true);
        }
        return null;
    }
    public function myAccount($key, $secret)
    {

        // get account information, make sure API key and secret are set
        $response = self::signedRequest('GET', 'fapi/v1/account', [], $key, $secret);
        // echo "json_encode " . json_encode($response);

        if ($response['code'] == 200 || $response['code'] == 201) {

            return json_decode($response['result'], true);
        }
        return null;
    }

    function sendRequest($method, $path, $key)
    {


        $BASE_URL = 'https://fapi.binance.com/'; // production
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
}
