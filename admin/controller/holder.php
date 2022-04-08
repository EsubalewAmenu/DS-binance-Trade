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
        $GLOBALS['Ds_bt_common'] = new Ds_bt_common();
    }

    public function main()
    {
        $secret = $GLOBALS['Ds_bt_common']->api_secret();
        $key = $GLOBALS['Ds_bt_common']->api_key();
        $recvWindow = $GLOBALS['Ds_bt_common']->recvWindow();

        $priceToTradeOnSingleCoin = 15;
        $depend_on_last_n_history = 2;

        $interval = "5m";
        //1m3m5m15m30m1h2h4h6h8h12h1d3d1w1M

        // global $table_prefix, $wpdb;
        // $wp_ds_table = $table_prefix . "ds_bt_trades";
        // $orderList = $wpdb->get_results("SELECT * FROM " . $wp_ds_table . " WHERE status='NEW'");

        // once a day
        // sendRequest("GET", "api/v1/exchangeInfo", $key); - update the symbols db.table


        self::myAccount($interval, $priceToTradeOnSingleCoin, $depend_on_last_n_history, $key, $secret, $recvWindow);
    }

    public function myAccount($interval, $priceToTradeOnSingleCoin, $depend_on_last_n_history, $key, $secret, $recvWindow)
    {
        // https://github.com/binance/binance-spot-api-docs/blob/master/rest-api.md

        // >sendRequest("GET", "api/v3/ticker/price", $key); - get market price of all coins
        // $GLOBALS['Ds_bt_common']->kline($symbol, $interval, $last_n_history, $key, $secret) - get last n +2 coin history
        // sendRequest("GET", "api/v3/ticker/24hr?symbol=BTCBUSD", $key); - current price, get 24 hrs market volume (coin volume * lastPrice)

        $response = $GLOBALS['Ds_bt_common']->sendRequest("GET", "api/v1/exchangeInfo", $key);
        echo json_encode($response);

        $response['code'] = 0;
        // $response = $GLOBALS['Ds_bt_common']->signedRequest('GET', 'api/v3/account', [], $key, $secret);
        // echo json_encode($response);

        if ($response['code'] == 200 || $response['code'] == 201) {
            $response = json_decode($response['result'], true);

            global $table_prefix, $wpdb;
            $wp_ds_table = $table_prefix . "ds_bt_symbols";

            foreach ($response['balances'] as $asset) {


                if ($asset['asset'] != "BUSD") {
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
                } else if ($asset['asset'] == "BUSD") {
                    // if last order time greater than interval - cancel

                    //             $dbSymbol = $wpdb->get_row("SELECT * FROM " . $wp_ds_table . " WHERE symbol='" . $symbol . "' and market_price!='-1'");

                    //             $precisionPrice = $dbSymbol->precisionPrice;
                    //             $precisionQuantity = $dbSymbol->precisionQuantity;


                    //             echo "current BUSD is " . $asset['free'] . ' precisionPrice ' . $precisionPrice . ' precisionQuantity ' . $precisionQuantity . ' symbol ' . $symbol;
                    //             echo "</br>";

                    //             $asset['free'] = $asset['free'] - (($asset['free'] * 0.5) / 100);

                    //             if ($asset['free'] > 13) self::requestNewOrder($symbol . $asset['asset'], "BUY", $asset['free'], $precisionPrice, $precisionQuantity, $buy_interval, $sell_interval, $key, $secret);
                    //             // return $asset['free'];
                }
            }
        }
    }
}
