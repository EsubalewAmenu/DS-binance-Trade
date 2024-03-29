<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
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
        $depend_on_interval = "15m";
        // //1m3m5m15m30m1h2h4h6h8h12h1d3d1w1M
        $trade_coin_volume = 3000000;

        // $BUSD_USDT = "BUSD";
        $BUSD_USDT = "USDT";

        if ($GLOBALS['Ds_bt_common']->isSymbolsUpdated($key)) {
            self::buyAndHoldCoin('TEST', $BUSD_USDT, $depend_on_interval, $trade_coin_volume, $key, $secret, $recvWindow);
            self::checkAndSellCoin('EOS', $BUSD_USDT, $depend_on_interval, $trade_coin_volume, $key, $secret, $recvWindow);

            // self::myLocalAccount($priceToTradeOnSingleCoin, $depend_on_interval, $trade_coin_volume, $key, $secret, $recvWindow);
        }
    }
    // public function myLocalAccount($priceToTradeOnSingleCoin, $depend_on_interval, $trade_coin_volume, $key, $secret, $recvWindow)
    // {

    //     global $table_prefix, $wpdb;
    //     $wp_ds_bt_symbols_table = $table_prefix . "ds_bt_symbols";
    //     $assets = $wpdb->get_results("SELECT * FROM " . $wp_ds_bt_symbols_table . ' WHERE busdValue > 11');

    //     $BUSD_USDT = "BUSD";
    //     // $BUSD_USDT = "USDT";
    //     foreach ($assets as $asset) {
    //         if ($asset->symbol != "BUSD") {
    //             // check sell
    //             self::checkAndSellCoin($asset, $depend_on_interval, $trade_coin_volume, $key, $secret, $recvWindow);
    //         } else {
    //             // check and buy
    //             self::buyAndHoldCoin($asset, $BUSD_USDT, $depend_on_interval, $trade_coin_volume, $key, $secret, $recvWindow);
    //         }
    //     }
    // }
    public function checkAndSellCoin($asset, $BUSD_USDT, $depend_on_interval, $trade_coin_volume, $key, $secret, $recvWindow)
    {
        // if ($asset->busdValue > 11) {

            $symbolRecomendation = self::symbol_status($asset . $BUSD_USDT, $depend_on_interval);

            if ($symbolRecomendation == 'SELL' ||$symbolRecomendation ==  'STRONG_SELL') {
                
                echo "sell $asset coin. rec = " . $symbolRecomendation;
                
                self::save_trade($asset . $BUSD_USDT, "SELL", "SPOT", 1000, 0, 'orderId', 'orderListId', 'clientOrderId', 'transactTime');

            }else echo $asset . " reco is " . $symbolRecomendation;

    }
    public function buyAndHoldCoin($asset, $BUSD_USDT, $depend_on_interval, $trade_coin_volume, $key, $secret, $recvWindow)
    {
        // echo "test";

        $url = "https://scanner.tradingview.com/crypto/scan";

        if ($depend_on_interval == "5m" && ($BUSD_USDT == "BUSD" || $BUSD_USDT == "USDT")) {
            //BUSD
            $data = '{"filter":[{"left":"change|5","operation":"nempty"},{"left":"exchange","operation":"equal","right":"BINANCE"},{"left":"volume","operation":"in_range","right":[2000000,50000000]},{"left":"change|5","operation":"greater","right":0.1},{"left":"Recommend.All","operation":"nequal","right":0.1},{"left":"name,description","operation":"match","right":"' . $BUSD_USDT . '"}],"options":{"lang":"en"},"filter2":{"operator":"and","operands":[{"operation":{"operator":"or","operands":[{"expression":{"left":"Recommend.All","operation":"in_range","right":[0.1,0.5]}},{"expression":{"left":"Recommend.All","operation":"in_range","right":[0.5,1]}}]}}]},"markets":["crypto"],"symbols":{"query":{"types":[]},"tickers":[]},"columns":["base_currency_logoid","currency_logoid","name","close","change","volume","Recommend.All","ask","exchange","change|5","change|15","description","type","subtype","update_mode","pricescale","minmov","fractional","minmove2"],"sort":{"sortBy":"change|5","sortOrder":"desc"},"range":[0,150]}';
            // response is order by 5m change :- symbol, last price, change24 %, volume, tech rating 24, ask, exchange, change5m %, change15m %
        } else if ($depend_on_interval == "15m" && ($BUSD_USDT == "BUSD" || $BUSD_USDT == "USDT")) {
            //BUSD
            $data = '{"filter":[{"left":"change|15","operation":"nempty"},{"left":"exchange","operation":"equal","right":"BINANCE"},{"left":"volume","operation":"in_range","right":[2000000,50000000]},{"left":"change|15","operation":"greater","right":0.1},{"left":"Recommend.All","operation":"nequal","right":0.1},{"left":"name,description","operation":"match","right":"' . $BUSD_USDT . '"}],"options":{"lang":"en"},"filter2":{"operator":"and","operands":[{"operation":{"operator":"or","operands":[{"expression":{"left":"Recommend.All","operation":"in_range","right":[0.1,0.5]}},{"expression":{"left":"Recommend.All","operation":"in_range","right":[0.5,1]}}]}}]},"markets":["crypto"],"symbols":{"query":{"types":[]},"tickers":[]},"columns":["base_currency_logoid","currency_logoid","name","close","change","volume","Recommend.All","ask","exchange","change|5","change|15","description","type","subtype","update_mode","pricescale","minmov","fractional","minmove2"],"sort":{"sortBy":"change|15","sortOrder":"desc"},"range":[0,150]}';
            // response is order by 15m change :- symbol, last price, change24 %, volume, tech rating 24, ask, exchange, change5m %, change15m %
        } else echo " Please choose correct BUSD_USDT and interval FIRST";

        $response = $GLOBALS['Ds_bt_common']->postAPI($url, $data);

        // print_r($response);

        if ($response['code'] == 200 || $response['code'] == 201) {
            $response = json_decode($response['result'], true);

            // print_r($response['data']);
            foreach ($response['data'] as $symbol) {
                $fullSymbol = $symbol['d'][2];
                if (str_ends_with($fullSymbol, $BUSD_USDT)) { //"BUSD")) {
                    $lastPrice = $symbol['d'][3];
                    $change24Perc = $symbol['d'][4];
                    $volume = $symbol['d'][5];
                    $techRate24 = $symbol['d'][6];
                    $ask = $symbol['d'][7];
                    $exchange = $symbol['d'][8];
                    $change5mPerc = $symbol['d'][9];
                    $change15mPerc = $symbol['d'][10];

                    $symbolRecomendation = self::symbol_status($fullSymbol, $depend_on_interval);
                    if ($symbolRecomendation == 'STRONG_BUY') {

                        echo "buy Symbol = " . $fullSymbol . " lastPrice=" . $lastPrice . " 24h change=" . $change24Perc . " volume=" . $volume .
                            " Techrate24=" . $techRate24 . " ask=" . $ask . " exchange=" . $exchange . " 5m change=" . $change5mPerc . " 15m chage=" . $change15mPerc . "</br>\n";

                        // $asset->currentAsset -= $asset->currentAsset % $lastPrice;
                        // $quantity = $asset->currentAsset / $lastPrice;
                        $quantity = 100 / $lastPrice;

                        // self::save_trade($fullSymbol, "BUY", "SPOT", $quantity, $lastPrice, 'orderId', 'orderListId', 'clientOrderId', 'transactTime');
                    } else
                        echo $fullSymbol . " not bought RECOMMENDATION is " . $symbolRecomendation . "</br>\n";
                }
            }
        }
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

    function save_trade($symbol, $side, $type, $quantity, $price, $orderId, $orderListId, $clientOrderId, $transactTime)
    {

        global $table_prefix, $wpdb;

        $wp_ds_table = $table_prefix . "ds_bt_trades";

        $dbResult = $wpdb->insert($wp_ds_table, array(
            'symbol' => $symbol,
            'side' => $side,
            'type' => $type,
            'quantity' => $quantity,
            'price' => $price,
            'status' => "NEW",

            'orderId' => $orderId,
            'orderListId' => $orderListId,
            'clientOrderId' => $clientOrderId,
            'transactTime' => $transactTime,

        ));

        $wp_ds_bt_symbols_table = $table_prefix . "ds_bt_symbols";

        if ($side == "BUY") {
            $data = [
                'lastPrice' => $price,

                'currentAsset' => $quantity, // + old amount
                'busdValue' => $quantity * $price,
            ];

            $wpdb->update($wp_ds_bt_symbols_table, ['currentAsset' => 0], ['symbol' => 'BUSD']);
        } else {
            $data = [
                'lastPrice' => $price,

                'currentAsset' => 0, // old amount - $quantity
                'busdValue' => 0, //(old amount - $quantity) * $price
            ];
            $wpdb->update($wp_ds_bt_symbols_table, ['currentAsset' => $price * $quantity], ['symbol' => 'BUSD']);
        }
        $where = ['symbol' => substr($symbol, 0, 4)];
        $wpdb->update($wp_ds_bt_symbols_table, $data, $where);
    }
}
