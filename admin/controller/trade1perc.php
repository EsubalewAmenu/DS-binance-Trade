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
class Ds_bt_trade1p
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

        // $trade_coin_volume = 3000000;

        $GLOBALS['Ds_bt_common']->cancelBuyOrdersIfTooksLong($GLOBALS['Ds_bt_common']->openOrders());


        // $assetTEST['asset'] = "BSW";
        // $assetTEST['free'] = "20";
        // self::checkAndSell($assetTEST);

        // $asset['asset'] = "BUSD";
        // $asset['free'] = "20";
        // self::checkAndBuy($asset);

        $myAssets = $GLOBALS['Ds_bt_common']->myAccount($key, $secret);
        if ($myAssets) {
            self::checkAssets($myAssets);
        }
    }
    public function checkAssets($myAssets)
    {
        echo "checking assets " . time() . "</br>\n";

        foreach ($myAssets['balances'] as $asset) {

            if ($asset['free'] > 0 && $asset['asset'] != "BUSD" && $asset['asset'] != "USDT") {
                self::checkAndSell($asset);
            } else if ($asset['asset'] == "BUSD" || $asset['asset'] == "USDT") {
                if ($asset['free'] > 11) {
                    echo $asset['asset'] . " buy started. free is " . $asset['free'] . "\n";
                    self::checkAndBuy($asset, $myAssets['balances']);
                }
            } else if ($asset['locked'] > 0 &&  $asset['asset'] != "BUSD" && $asset['asset'] != "USDT") {
                $symbolRecomendation = $GLOBALS['Ds_bt_common']->symbol_status($asset['asset'] . $GLOBALS['Ds_bt_common']->baseAsset(), $GLOBALS['Ds_bt_common']->depend_on_interval());
                echo $asset['asset'] . " locked=" . $asset['locked'] . " symbolRecomendation $symbolRecomendation</br>\n";
                //if sell or strong sell
                if ($symbolRecomendation == 'STRONG_SELL' || $symbolRecomendation == 'SELL') { // || $symbolRecomendation == 'NEUTRAL' ) {

                    $orderBook = $GLOBALS['Ds_bt_common']->getDepth($asset['asset'], $GLOBALS['Ds_bt_common']->baseAsset(), 2, $GLOBALS['Ds_bt_common']->api_key()); // get orderbook (BUY)
                    if (isset($orderBook['sell_by'])) {
                        $cancelOrder = $GLOBALS['Ds_bt_common']->cancelOrder($asset['asset'] . $GLOBALS['Ds_bt_common']->baseAsset(), time(), $GLOBALS['Ds_bt_common']->api_key(), $GLOBALS['Ds_bt_common']->api_secret());
                        print_r($cancelOrder);
                        //cancel symbol orders 
                        // and order with $orderBook['sell_by']
                        echo "order canceled WILL ORDER WITH " . $orderBook['sell_by'];

                        $type = "LIMIT";
                        $orderResult = $GLOBALS['Ds_bt_common']->order($asset['asset'] . $GLOBALS['Ds_bt_common']->baseAsset(), "SELL", $type, $asset['locked'], $orderBook['sell_by'], $GLOBALS['Ds_bt_common']->recvWindow(), $GLOBALS['Ds_bt_common']->api_key(), $GLOBALS['Ds_bt_common']->api_secret());
                        print_r($orderResult);
                    }
                }
            }
        }
    }
    public function checkAndSell($asset)
    {
        // echo "there is " . $asset['free'] . " free " . $asset['asset'] . " </br>/n";

        $dbSymbol = $GLOBALS['Ds_bt_common']->getSymbolFromDB($asset['asset']);

        if ($dbSymbol) {

            if (($asset['free'] * $dbSymbol->lastPrice) > 11) {

                // echo "Current price * free is " . ($asset['free'] * $dbSymbol->lastPrice) . " where asset['free'] is " . $asset['free'] . "dbSymbol->lastPrice price is " . $dbSymbol->lastPrice . "</br>/n";
                // get bought price
                // order sell by adding 1% on bought price or current price
                $myTrades = $GLOBALS['Ds_bt_common']->myTrades($asset['asset'] . $GLOBALS['Ds_bt_common']->baseAsset(), 15, $GLOBALS['Ds_bt_common']->api_key(), $GLOBALS['Ds_bt_common']->api_secret());

                if (isset($myTrades)) {
                    // foreach ($myTrades as $myTrade) {
                    for ($i = count($myTrades) - 1; $i >= 0; $i--) {
                        $myTrade = $myTrades[$i];
                        // echo "myTrade is";
                        // print_r($myTrade);
                        // echo "price is".$myTrade->price;

                        // $symbol  = $myTrade->symbol;
                        // $id  = $myTrade->id;
                        // $orderId  = $myTrade->orderId;
                        // $orderListId  = $myTrade->orderListId;
                        $price  = $myTrade->price;
                        // $qty  = $myTrade->qty;
                        // $quoteQty  = $myTrade->quoteQty;
                        // $commission  = $myTrade->commission;
                        // $commissionAsset  = $myTrade->commissionAsset;
                        // $time  = $myTrade->time;
                        $isBuyer  = $myTrade->isBuyer;
                        // $isMaker  = $myTrade->isMaker;
                        // $isBestMatch  = $myTrade->isBestMatch;

                        if ($isBuyer) {
                            // sell by adding 1 % on price column
                            //get last price
                            //take the grater and order sell by adding 1%
                            // $lastPrice = $GLOBALS['Ds_bt_common']->getPrice($asset['asset'] . $GLOBALS['Ds_bt_common']->baseAsset(), $GLOBALS['Ds_bt_common']->api_key(), $GLOBALS['Ds_bt_common']->api_secret()); // price range
                            // if ($price > $lastPrice) {
                            //     $sellingPrice = $price + (0.005 * $price);
                            // } else {
                            //     $sellingPrice = $lastPrice + (0.005 * $lastPrice);
                            // }
                            $orderBook = $GLOBALS['Ds_bt_common']->getDepth($asset['asset'], $GLOBALS['Ds_bt_common']->baseAsset(), 2, $GLOBALS['Ds_bt_common']->api_key()); // get orderbook (BUY)

                            if ($price > $orderBook['sell_by']) {
                                $sellingPrice = $price + (0.002 * $price);
                            } else {
                                $sellingPrice = $orderBook['sell_by']; //$lastPrice + (0.005 * $lastPrice);
                            }

                            $afterPoint = 0;
                            for ($i = 0; $i < strlen($dbSymbol->precisionPrice) - 1; $i++) {
                                if ($dbSymbol->precisionPrice[$i] == '1') {
                                    break;
                                } else if ($dbSymbol->precisionPrice[$i] == '0')
                                    $afterPoint++;
                            }
                            $sellingPrice = $GLOBALS['Ds_bt_common']->floorDec($sellingPrice, $afterPoint);

                            $quantityAfterPoint = 0;
                            for ($i = 0; $i < strlen($dbSymbol->min_lot_size) - 1; $i++) {
                                if ($dbSymbol->min_lot_size[$i] == '1') {
                                    break;
                                } else if ($dbSymbol->min_lot_size[$i] == '0')
                                    $quantityAfterPoint++;
                            }

                            $freeQuantity = $GLOBALS['Ds_bt_common']->floorDec($asset['free'], $quantityAfterPoint);
                            // order sell by price
                            // echo "price is $price freeQuantity $freeQuantity sellingPrice is " . $sellingPrice;
                            $type = "LIMIT";
                            $orderResult = $GLOBALS['Ds_bt_common']->order($asset['asset'] . $GLOBALS['Ds_bt_common']->baseAsset(), "SELL", $type, $freeQuantity, $sellingPrice, $GLOBALS['Ds_bt_common']->recvWindow(), $GLOBALS['Ds_bt_common']->api_key(), $GLOBALS['Ds_bt_common']->api_secret());
                            print_r($orderResult);
                            break;
                        }
                    }
                }
            }
        }
    }
    public function checkAndBuy($asset, $assets)
    {

        // echo "test";


        $response = $GLOBALS['Ds_bt_common']->scanCrypto($asset['asset']); // base (BUSD OR USDT)
        // echo " res test is ";
        // print_r($response);
        // echo "end";
        if (isset($response)) {

            // print_r($response['data']);
            foreach ($response['data'] as $symbol) {
                // print_r($symbol);

                $fullSymbol = $symbol['d'][2];
                if ($GLOBALS['Ds_bt_common']->isNotHold(substr($fullSymbol, 0, -4), $assets)) {
                    // $lastPrice = $symbol['d'][3];
                    // $change24Perc = $symbol['d'][4];
                    // $volume = $symbol['d'][5];
                    // $techRate24 = $symbol['d'][6];
                    // $ask = $symbol['d'][7];
                    // $exchange = $symbol['d'][8];
                    // $change5mPerc = $symbol['d'][9];
                    // $change15mPerc = $symbol['d'][10];
                    // $changeFromOpen = $symbol['d'][11];

                    $symbolRecomendation = $GLOBALS['Ds_bt_common']->symbol_status($fullSymbol, $GLOBALS['Ds_bt_common']->depend_on_interval());
                    if ($symbolRecomendation == 'STRONG_BUY' || $symbolRecomendation == 'BUY') {
                        // &&  where currently i didn hold this coin

                        // echo "reco is $symbolRecomendation Symbol = " . $fullSymbol . " lastPrice=" . $lastPrice . " 24h change=" . $change24Perc . " volume=" . $volume .
                        //     " Techrate24=" . $techRate24 . " ask=" . $ask . " exchange=" . $exchange . " 5m change=" . $change5mPerc . " 15m chage=" . $change15mPerc . "</br>\n";
                        //get last order


                        if ($asset['free'] > $GLOBALS['Ds_bt_common']->priceToTradeOnSingleCoin() * 2) {
                            $amountToBuy = $GLOBALS['Ds_bt_common']->priceToTradeOnSingleCoin();
                        } else {
                            $amountToBuy = $asset['free'];
                        }


                        $buyOrderBook = $GLOBALS['Ds_bt_common']->buyOrderBook(substr($fullSymbol, 0, -4), $amountToBuy, $GLOBALS['Ds_bt_common']->baseAsset(), 5, $GLOBALS['Ds_bt_common']->api_key());
                        if ($buyOrderBook['quantity'] > 0) {
                            // echo substr($fullSymbol, 0, -4) . "   is ";
                            // print_r($buyOrderBook);
                            // echo " quantity=" . $buyOrderBook['quantity'] . " lastOnOrderBook=" . $buyOrderBook['lastOnOrderBook'] . " amountToBuy=" . $buyOrderBook['amountToBuy'];
                            // self::save_trade($fullSymbol, "BUY", "SPOT", $quantity, $lastPrice, 'orderId', 'orderListId', 'clientOrderId', 'transactTime');
                            $type = "LIMIT";
                            $orderResult = $GLOBALS['Ds_bt_common']->order($fullSymbol, "BUY", $type, $buyOrderBook['quantity'], $buyOrderBook['lastOnOrderBook'], $GLOBALS['Ds_bt_common']->recvWindow(), $GLOBALS['Ds_bt_common']->api_key(), $GLOBALS['Ds_bt_common']->api_secret());
                            print_r($orderResult);

                            global $table_prefix, $wpdb;
                            $wp_ds_table = $table_prefix . "ds_bt_symbols";

                            $data = ['lastPrice' => $buyOrderBook['lastOnOrderBook'],];
                            $where = ['symbol' => substr($fullSymbol, 0, -4)];
                            $wpdb->update($wp_ds_table, $data, $where);

                            $asset['free'] -= $amountToBuy;
                            if ($asset['free'] < $GLOBALS['Ds_bt_common']->priceToTradeOnSingleCoin())
                                break;
                        }
                    } else
                        echo $fullSymbol . " not bought RECOMMENDATION is " . $symbolRecomendation . "</br>\n";
                }
            }
        } else
            echo "there is no good recommendation (not to buy at this time)</br>\n";
    }
}
