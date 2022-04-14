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
class Ds_bt_tradingview
{


    // get strong buys from trading view
    // buy
    //check if my coin is on sell
    // sell







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
        $depend_on_interval = "1D";
        // //1m3m5m15m30m1h2h4h6h8h12h1d3d1w1M
        $trade_coin_volume = 3000000;

        if ($GLOBALS['Ds_bt_common']->isSymbolsUpdated($key)) {
            self::myLocalAccount($priceToTradeOnSingleCoin, $depend_on_interval, $trade_coin_volume, $key, $secret, $recvWindow);
        }
    }
    public function myLocalAccount($priceToTradeOnSingleCoin, $depend_on_interval, $trade_coin_volume, $key, $secret, $recvWindow)
    {

        global $table_prefix, $wpdb;
        $wp_ds_bt_symbols_table = $table_prefix . "ds_bt_symbols";
        $assets = $wpdb->get_results("SELECT * FROM " . $wp_ds_bt_symbols_table . ' WHERE busdValue > 11');

        foreach ($assets as $asset) {
            if ($asset->symbol != "BUSD") {
                // check sell
            } else {
                // check and buy
                self::buyAndHoldCoin($asset, $depend_on_interval, $trade_coin_volume, $key, $secret, $recvWindow);
            }
        }
    }
    public function buyAndHoldCoin($asset, $depend_on_interval, $trade_coin_volume, $key, $secret, $recvWindow)
    {
        // get strong buy and buy from tradingview
        // check status with inter vall for each
        // order buy
        echo "test";
        $url = "https://scanner.tradingview.com/crypto/scan";
        $data = '{"filter":[{"left":"change","operation":"nempty"},{"left":"exchange","operation":"equal","right":"BINANCE"},{"left":"Recommend.All","operation":"nequal","right":0.1},{"left":"name,description","operation":"match","right":"busd"}],"options":{"lang":"en"},"filter2":{"operator":"and","operands":[{"operation":{"operator":"or","operands":[{"expression":{"left":"Recommend.All","operation":"in_range","right":[0.5,1]}},{"expression":{"left":"Recommend.All","operation":"in_range","right":[0.1,0.5]}}]}}]},"markets":["crypto"],"symbols":{"query":{"types":[]},"tickers":[]},"columns":["base_currency_logoid","currency_logoid","name","close","change","change_abs","high","low","volume","Recommend.All","exchange","description","type","subtype","update_mode","pricescale","minmov","fractional","minmove2"],"sort":{"sortBy":"change","sortOrder":"asc"},"range":[0,150]}';
        $response = $GLOBALS['Ds_bt_common']->postAPI($url, $data);

        // print_r($response);

        if ($response['code'] == 200 || $response['code'] == 201) {
            $response = json_decode($response['result'], true);

            // print_r($response['data']);
            foreach ($response['data'] as $symbol) {
                // print_r($symbol['d'][2]); // BTCBUSD
                $fullSymbol = $symbol['d'][2]; // check if it ends with BUUSD

                // $cmd = "python3 /home/esubalew/Desktop/tezt.py ";
                $cmd = "python3 " . ds_bt_PLAGIN_DIR . 'admin/controller/recommendation/ta.py';
                $output = shell_exec($cmd);
                $output = substr($output, 0, -1);
                echo 'ta recommendation is truef' . $output.'e';
                $recommendation = json_decode($output, true);
                print_r($recommendation);
                echo 'ta recommendation end';

                echo $recommendation;
break;  

            }
        }
    }
}
