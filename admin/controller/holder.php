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

        global $table_prefix, $wpdb;
        $wp_ds_bt_settings_table = $table_prefix . "ds_bt_settings";
        $wp_ds_bt_symbols_table = $table_prefix . "ds_bt_symbols";
        $setting = $wpdb->get_row("SELECT * FROM " . $wp_ds_bt_settings_table . ' WHERE _key="symbols_last_updated"');
        if ($setting) {
            if ($setting->value1 != date("d-m-y")) {
                // echo "price expired should be updated :) setting=" . $setting->value1 . " != " . date("d-m-y");

                // once a day (by checking symbols_last_updated from setting)
                // {
                $exchangeInfos = $GLOBALS['Ds_bt_common']->sendRequest("GET", "api/v3/exchangeInfo", $key);
                $tickers = $GLOBALS['Ds_bt_common']->sendRequest("GET", "api/v3/ticker/24hr", $key);

                // print_r($tickers);

                if (($exchangeInfos['code'] == 200 || $exchangeInfos['code'] == 201) && ($tickers['code'] == 200 || $tickers['code'] == 201)) {
                    $exchangeInfos = json_decode($exchangeInfos['result'], true);
                    $tickers = json_decode($tickers['result'], true);

                    // $count = 0;
                    foreach ($exchangeInfos['symbols'] as $symbol) {
                        if ($symbol['quoteAsset'] == 'BUSD') {
                            // $count++;
                            // echo "symbol " . $symbol['baseAsset'] . " found (id=" . $count . ")!</br>/n";


                            $baseAsset = $wpdb->get_row("SELECT * FROM " . $wp_ds_bt_symbols_table . ' WHERE symbol="' . $symbol['baseAsset'] . '"');
                            if ($baseAsset) { //update
                                // $data = ['market_price' => $currentPrice];
                                // $where = ['symbol' => $asset['asset']];
                                // $wpdb->update($wp_ds_table, $data, $where);
                            } else { //insert
                                $$min_lot_size = 0;
                                foreach ($symbol['filters'] as $filter) {
                                    if ($filter->filterType == "PERCENT_PRICE")
                                        $precisionQuantity = $filter->multiplierUp;

                                    if ($filter->filterType == "LOT_SIZE")
                                        $min_lot_size = $filter->minQty;
                                }

                                $wpdb->insert($wp_ds_bt_symbols_table, array(
                                    'symbol' => $symbol['baseAsset'],
                                    'precisionPrice' => -1,
                                    'precisionQuantity' => $precisionQuantity,
                                    'isSpotTradingAllowed' => $symbol['isSpotTradingAllowed'],
                                    'isMarginTradingAllowed' => $symbol['isMarginTradingAllowed'],
                                    'min_lot_size' => $min_lot_size,
                                    'permissions' => $symbol['permissions'],

                                    'lastPrice' => $ticker->lastPrice,
                                    'asset_volume' => $ticker->volume,
                                    'busd_volume' => $ticker->volume * $ticker->lastPrice,

                                ));
                            }
                        }
                        //             // sendRequest("GET", "api/v3/exchangeInfo", $key); - update the symbols db.table
                        //             // {\"symbol\":\"BTCBUSD\",\"status\":\"TRADING\",\"baseAsset\":\"BTC\",\"baseAssetPrecision\":8,\"quoteAsset\":\"BUSD\",\"quotePrecision\":8,\"quoteAssetPrecision\":8,\"baseCommissionPrecision\":8,\"quoteCommissionPrecision\":8,\"orderTypes\":[\"LIMIT\",\"LIMIT_MAKER\",\"MARKET\",\"STOP_LOSS_LIMIT\",\"TAKE_PROFIT_LIMIT\"],\"icebergAllowed\":true,\"ocoAllowed\":true,\"quoteOrderQtyMarketAllowed\":true,\"allowTrailingStop\":false,\"isSpotTradingAllowed\":true,\"isMarginTradingAllowed\":true,\

                        //             //     "filters\":[
                        //             //{\"filterType\":\"PRICE_FILTER\",\"minPrice\":\"0.01000000\",\"maxPrice\":\"1000000.00000000\",\"tickSize\":\"0.01000000\"},
                        //             // {\"filterType\":\"PERCENT_PRICE\",\"multiplierUp\":\"5\",\"multiplierDown\":\"0.2\",\"avgPriceMins\":5},{\"filterType\":\"LOT_SIZE\",\"minQty\":\"0.00001000\",\"maxQty\":\"9000.00000000\",\"stepSize\":\"0.00001000\"},
                        //             //{\"filterType\":\"MIN_NOTIONAL\",\"minNotional\":\"10.00000000\",\"applyToMarket\":true,\"avgPriceMins\":5},{\"filterType\":\"ICEBERG_PARTS\",\"limit\":10},
                        //             //{\"filterType\":\"MARKET_LOT_SIZE\",\"minQty\":\"0.00000000\",\"maxQty\":\"54.87502179\",\"stepSize\":\"0.00000000\"},
                        //             // {\"filterType\":\"MAX_NUM_ORDERS\",\"maxNumOrders\":200},
                        //             // {\"filterType\":\"MAX_NUM_ALGO_ORDERS\",\"maxNumAlgoOrders\":5}]

                        //             // ,\"permissions\":[\"SPOT\",\"MARGIN\"]}

                        //             // $response = $GLOBALS['Ds_bt_common']->sendRequest("GET", "api/v3/ticker/24hr", $key);
                        //             // use the above for coin volume, last(current) price and volume
                        //             // {\"symbol\":\"BTCBUSD\",\"priceChange\":\"-312.04000000\",\"priceChangePercent\":\"-0.714\",\"weightedAvgPrice\":\"43553.61473197\",\"prevClosePrice\":\"43714.29000000\",\"lastPrice\":\"43402.25000000\",\"lastQty\":\"0.70452000\",\"bidPrice\":\"43400.00000000\",\"bidQty\":\"1.65713000\",\"askPrice\":\"43400.01000000\",\"askQty\":\"2.83788000\",\"openPrice\":\"43714.29000000\",\"highPrice\":\"43976.00000000\",\"lowPrice\":\"43022.10000000\",\"volume\":\"9727.78533000\",\"quoteVolume\":\"423680214.45817280\",\"openTime\":1649331645109,\"closeTime\":1649418045109,\"firstId\":331821409,\"lastId\":332157741,\"count\":336333}
                        //             // }
                        //         }
                        //     }
                        // }
                        // $data = ['value1' => date("d-m-y")];
                        // $where = ['_key' => "symbols_last_updated"];
                        // $wpdb->update($wp_ds_bt_settings_table, $data, $where);
                        // self::myAccount($interval, $priceToTradeOnSingleCoin, $depend_on_last_n_history, $key, $secret, $recvWindow);


                    }
                }
            }
            if ($setting->value1 == date("d-m-y")) {
                // self::myAccount($interval, $priceToTradeOnSingleCoin, $depend_on_last_n_history, $key, $secret, $recvWindow);
            }
        }

        // self::myAccount($interval, $priceToTradeOnSingleCoin, $depend_on_last_n_history, $key, $secret, $recvWindow);

    }

    public function myAccount($interval, $priceToTradeOnSingleCoin, $depend_on_last_n_history, $key, $secret, $recvWindow)
    {
        // https://github.com/binance/binance-spot-api-docs/blob/master/rest-api.md

        // >sendRequest("GET", "api/v3/ticker/price", $key); - get market price of all coins
        // $GLOBALS['Ds_bt_common']->kline($symbol, $interval, $last_n_history, $key, $secret) - get last n +2 coin history
        // sendRequest("GET", "api/v3/ticker/24hr?symbol=BTCBUSD", $key); - current price, get 24 hrs market volume (coin volume * lastPrice)

        // $response = $GLOBALS['Ds_bt_common']->sendRequest("GET", "api/v3/ticker/price", $key);
        // $response = $GLOBALS['Ds_bt_common']->kline("BTCBUSD", $interval, $depend_on_last_n_history, $key, $secret);
        // $response = $GLOBALS['Ds_bt_common']->sendRequest("GET", "api/v3/ticker/24hr", $key);
        // $response = $GLOBALS['Ds_bt_common']->signedRequest('GET', 'api/v3/account', [], $key, $secret);
        $response = $GLOBALS['Ds_bt_common']->sendRequest("GET", "api/v3/exchangeInfo", $key);
        // $response = $GLOBALS['Ds_bt_common']->sendRequest("GET", "api/v3/depth?symbol=WAVESBUSD&limit=5", $key); // get orderbook (BUY)


        echo json_encode($response);

        // $response = $GLOBALS['Ds_bt_common']->signedRequest('GET', 'api/v3/account', [], $key, $secret);
        // echo json_encode($response);

        // if ($response['code'] == 200 || $response['code'] == 201) {
        //     $response = json_decode($response['result'], true);

        //     global $table_prefix, $wpdb;
        //     $wp_ds_bt_symbols_table = $table_prefix . "ds_bt_symbols";

        //     foreach ($response['balances'] as $asset) {


        //         if ($asset['asset'] != "BUSD") {
        //             self::checkAndSellCoin($asset, $interval, $depend_on_last_n_history, $key, $secret, $recvWindow);
        //         } else if ($asset['asset'] == "BUSD") {
        //             // if last order time greater than interval - cancel
        //             self::buyAndHoldCoin($asset, $interval, $depend_on_last_n_history, $key, $secret, $recvWindow);
        //         }
        //     }
        // }
    }
    public function checkAndSellCoin($asset, $interval, $depend_on_last_n_history, $key, $secret, $recvWindow)
    {
        echo "there is " . $asset['free'] . " free " . $asset['asset'] . " </br>/n";

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
                if (($last_n_loss_count >= $depend_on_last_n_history ||
                    $loss_count >= ($depend_on_last_n_history + 1)) && $lastPrice > $bought_price) {
                    // order sell with last price from book order
                } else if ($lastPrice < $bought_price && $last_n_loss_count >= 1) {
                    // order sell with bought price from book order
                }
            }
        }
    }
    public function buyAndHoldCoin($asset, $interval, $depend_on_last_n_history, $key, $secret, $recvWindow)
    {
        global $table_prefix, $wpdb;
        $wp_ds_bt_symbols_table = $table_prefix . "ds_bt_symbols";

        if ($asset['free'] > 11) {

            $symbolLists = $wpdb->get_results("SELECT * FROM " . $wp_ds_bt_symbols_table . " WHERE volume > 10 million");
            foreach ($symbolLists as $symbolList) {
                // get coin list from db where volume > 10 min and symbol != BUSD
                $coinHistories = $GLOBALS['Ds_bt_common']->kline($symbolList->symbol . "BUSD", $interval, $depend_on_last_n_history, $key, $secret);

                if ($coinHistories < $depend_on_last_n_history)
                    break;
                $profit_count = 0;
                for ($i = 0; $i < $depend_on_last_n_history; $i++) {

                    //close price - open price
                    if ($coinHistories[$i][1] - $coinHistories[$i][4] > 0) { // if it's positive 
                        $profit_count++;
                    }
                }

                $lastPrice = $GLOBALS['Ds_bt_common']->getPrice($asset['asset'] . "BUSD", $key, $secret); // price range

                if ($profit_count == $depend_on_last_n_history && $lastPrice > $coinHistories[0]) {
                    $orderBook = $GLOBALS['Ds_bt_common']->sendRequest("GET", "api/v3/depth?symbol=WAVESBUSD&limit=5", $key); // get orderbook (BUY)
                    if ($orderBook['code'] == 200 || $orderBook['code'] == 201) {
                        $lastOnOrderBook = json_decode($orderBook['result'], true)->bids[0][0];
                        $freeAsset = $asset['free'] - ($asset['free'] % $lastOnOrderBook);
                        $type = "LIMIT";
                        $price = json_decode($orderBook['result'], true)->bids[0][0];
                        $quantity =  $freeAsset / $symbolList->min_slot;
                        $GLOBALS['Ds_bt_common']->order($symbolList->symbol . "BUSD", "BUY", $type, $quantity, $price, $recvWindow, $key, $secret);
                    }
                }
            }
        }
    }
}
