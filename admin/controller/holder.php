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
        $trade_coin_volume = 3000000;
        $GLOBALS['kline_last_n_history'] = $depend_on_last_n_history + 3;
        $interval = "15m";
        //1m3m5m15m30m1h2h4h6h8h12h1d3d1w1M

        if ($GLOBALS['Ds_bt_common']->isSymbolsUpdated($key)) {
            self::myAccount($interval, $priceToTradeOnSingleCoin, $depend_on_last_n_history, $trade_coin_volume, $key, $secret, $recvWindow);
        }
    }

    public function myAccount($interval, $priceToTradeOnSingleCoin, $depend_on_last_n_history, $trade_coin_volume, $key, $secret, $recvWindow)
    {
        // https://github.com/binance/binance-spot-api-docs/blob/master/rest-api.md

        // >sendRequest("GET", "api/v3/ticker/price", $key); - get market price of all coins
        // $GLOBALS['Ds_bt_common']->kline($symbol, $interval, $last_n_history, $key, $secret) - get last n +2 coin history
        // sendRequest("GET", "api/v3/ticker/24hr?symbol=BTCBUSD", $key); - current price, get 24 hrs market volume (coin volume * lastPrice)

        // $response = $GLOBALS['Ds_bt_common']->sendRequest("GET", "api/v3/ticker/price", $key);
        // $response = $GLOBALS['Ds_bt_common']->kline("BTCBUSD", $interval, $depend_on_last_n_history, $key, $secret);
        // $response = $GLOBALS['Ds_bt_common']->sendRequest("GET", "api/v3/ticker/24hr", $key);
        // $response = $GLOBALS['Ds_bt_common']->signedRequest('GET', 'api/v3/account', [], $key, $secret);
        // $response = $GLOBALS['Ds_bt_common']->sendRequest("GET", "api/v3/exchangeInfo", $key);
        // $response = $GLOBALS['Ds_bt_common']->sendRequest("GET", "api/v3/depth?symbol=WAVESBUSD&limit=5", $key); // get orderbook (BUY)


        // echo json_encode($response);

        $response = $GLOBALS['Ds_bt_common']->signedRequest('GET', 'api/v3/account', [], $key, $secret);
        // echo json_encode($response);

        if ($response['code'] == 200 || $response['code'] == 201) {
            $response = json_decode($response['result'], true);

            foreach ($response['balances'] as $asset) {

                // if last order time greater than interval - cancel

                if ($asset['asset'] != "BUSD") {
                    // self::checkAndSellCoin($asset, $interval, $depend_on_last_n_history, $key, $secret, $recvWindow);
                } else if ($asset['asset'] == "BUSD") {
                    $asset['free'] = 20;
                    self::buyAndHoldCoin($asset, $interval, $depend_on_last_n_history, $trade_coin_volume, $key, $secret, $recvWindow);
                }
            }
        }
    }
    public function checkAndSellCoin($asset, $interval, $depend_on_last_n_history, $key, $secret, $recvWindow)
    {
        // echo "there is " . $asset['free'] . " free " . $asset['asset'] . " </br>/n";

        global $table_prefix, $wpdb;
        $wp_ds_bt_symbols_table = $table_prefix . "ds_bt_symbols";

        $dbSymbol = $wpdb->get_row("SELECT * FROM " . $wp_ds_bt_symbols_table . " WHERE symbol='" . $asset['asset'] . "' and lastPrice > 0");

        if ($dbSymbol) {
            $lastPrice = $dbSymbol->lastPrice;
            // $precisionPrice = $dbSymbol->precisionPrice;
            // $precisionQuantity = $dbSymbol->precisionQuantity;

            if (($asset['free'] * $lastPrice) > 11) {
                $coinHistories = $GLOBALS['Ds_bt_common']->kline($asset['asset'] . "BUSD", $interval, $depend_on_last_n_history, $key, $secret);
                $last_n_loss_count = 0;
                $loss_count = 0;
                $bought_price = 0; // should get bought price first
                for ($i = 0; $i < count($coinHistories); $i++) {

                    // if($coinHistories[$i] was loss){ 
                    //     $loss_count++
                    // if($i<$depend_on_last_n_history)
                    // $last_n_loss_count++;
                    // }
                }
                // if prevous or current loss > 0.6 % - sell
                // if n loss out of n+1 - sell


                if (($last_n_loss_count >= $depend_on_last_n_history ||
                    $loss_count >= ($depend_on_last_n_history + 1)) && $lastPrice > $bought_price) {
                    // order sell with last price from book order
                } else if ($lastPrice < $bought_price && $last_n_loss_count >= 1) {
                    // order sell with bought price from book order
                }
            }
        }
    }
    public function buyAndHoldCoin($asset, $interval, $depend_on_last_n_history, $trade_coin_volume, $key, $secret, $recvWindow)
    {

        if ($asset['free'] > 11) {

            global $table_prefix, $wpdb;
            $wp_ds_bt_symbols_table = $table_prefix . "ds_bt_symbols";
            //             $coinHistories = $GLOBALS['Ds_bt_common']->kline("BALBUSD", $interval, $GLOBALS['kline_last_n_history'], $key, $secret);
            // print_r($coinHistories);    
            // $symbolLists = $wpdb->get_results("SELECT * FROM " . $wp_ds_bt_symbols_table . " WHERE busd_volume > ".$trade_coin_volume. ' and symbol="SHIB" order by priceChangePercent desc');
            $symbolLists = $wpdb->get_results("SELECT * FROM " . $wp_ds_bt_symbols_table . " WHERE busd_volume > " . $trade_coin_volume . ' and symbol!="BUSD" order by priceChangePercent desc');
            foreach ($symbolLists as $symbolList) {
                try {
                    //code...
                    $coinHistories = $GLOBALS['Ds_bt_common']->kline($symbolList->symbol . "BUSD", $interval, $GLOBALS['kline_last_n_history'], $key, $secret);
                    // print_r($coinHistories);
                    if (count($coinHistories) >= $GLOBALS['kline_last_n_history']){
                        // break;
                    $profit_count = 0;
                    // $lastPrice = $GLOBALS['Ds_bt_common']->getPrice($asset['asset'] . "BUSD", $key, $secret); // price range
                    $lastPrice = $coinHistories[$GLOBALS['kline_last_n_history'] - 1][4];
                    $i = $GLOBALS['kline_last_n_history'] - 1; //including last price
                    $countHistory = 0;
                    while ($countHistory <= $depend_on_last_n_history) {

                        //close price - open price
                        if ($coinHistories[$i][4] - $coinHistories[$i][1] > 0) { // if it's positive 
                            $profit_count++;
                        }
                        $i--;
                        $countHistory++;
                    }


                    if ($profit_count == ($depend_on_last_n_history + 1)) { //including last price
                    echo '{' . $symbolList->symbol . " profiting. busd_volume " . $symbolList->busd_volume. ' 24h% '.$symbolList->priceChangePercent.' lastPrice is ' . $lastPrice . '}, ';

                        // echo ' its profiting';
                        // print_r($coinHistories);
                        // break;

                        // to remove orderBook api call, order by current price - minium allowed order price ($symbolList->precisionPrice from db)
                        // $orderBook = $GLOBALS['Ds_bt_common']->sendRequest("GET", "api/v3/depth?symbol=".$symbolList->symbol."BUSD&limit=5", $key); // get orderbook (BUY)
                        // if ($orderBook['code'] == 200 || $orderBook['code'] == 201) {
                        //     $lastOnOrderBook = json_decode($orderBook['result'], true)->bids[0][0];
                        //     $freeAsset = $asset['free'] - ($asset['free'] % $lastOnOrderBook);
                        //     $type = "LIMIT";
                        //     $price = json_decode($orderBook['result'], true)->bids[0][0];
                        //     $quantity =  $freeAsset / $symbolList->min_slot;
                        //     $GLOBALS['Ds_bt_common']->order($symbolList->symbol . "BUSD", "BUY", $type, $quantity, $price, $recvWindow, $key, $secret);
                        // }
                    }
                }
                } catch (\Throwable $th) {
                    //throw $th;
                    echo 'error on ' . $symbolList->symbol;
                    print_r($coinHistories);
                }
            }
        }
    }
}
