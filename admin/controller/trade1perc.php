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

        self::checkOrderList();

        $asset['asset'] = "BUSD";
        $asset['free'] = "20";
        self::checkAndBuy($asset);
        // $myAssets = $GLOBALS['Ds_bt_common']->myAccount($key, $secret);
        // if ($myAssets) {
        //     self::checkAssets($myAssets);
        // }
    }
    public function checkOrderList()
    {
        // if it tooks to long to buy, cancel
        // if it tooks to long to sell, cancel and order based on
    }
    public function checkAssets($myAssets)
    {

        foreach ($myAssets['balances'] as $asset) {


            if ($asset['free'] > 0 && $asset['asset'] != "BUSD" && $asset['asset'] != "USDT") {
                self::checkAndSell($asset);
            } else if ($asset['asset'] == "BUSD" || $asset['asset'] == "USDT") {
                if ($asset['free'] > 13) {
                    self::checkAndBuy($asset);
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

                echo "Current price * free is " . ($asset['free'] * $dbSymbol->lastPrice) . " where asset['free'] is " . $asset['free'] . "dbSymbol->lastPrice price is " . $dbSymbol->lastPrice . "</br>/n";
                // get bought price
                // order sell by adding 1%

            }
        }
    }
    public function checkAndBuy($asset)
    {

        // echo "test";


        $response = $GLOBALS['Ds_bt_common']->scanCrypto($asset['asset']); // base (BUSD OR USDT)
        // echo " res test is ";
        // print_r($response);
        // echo "end";
        if (isset($response)) {

            // print_r($response['data']);
            foreach ($response['data'] as $symbol) {
                $fullSymbol = $symbol['d'][2];
                if (str_ends_with($fullSymbol, $asset['asset'])) { //"BUSD")) {
                    $lastPrice = $symbol['d'][3];
                    $change24Perc = $symbol['d'][4];
                    $volume = $symbol['d'][5];
                    $techRate24 = $symbol['d'][6];
                    $ask = $symbol['d'][7];
                    $exchange = $symbol['d'][8];
                    $change5mPerc = $symbol['d'][9];
                    $change15mPerc = $symbol['d'][10];

                    $symbolRecomendation = $GLOBALS['Ds_bt_common']->symbol_status($fullSymbol, $GLOBALS['Ds_bt_common']->depend_on_interval());
                    if ($symbolRecomendation == 'STRONG_BUY' || $symbolRecomendation == 'BUY') {
                        // &&  where currently i didn hold this coin

                        echo "reco is $symbolRecomendation Symbol = " . $fullSymbol . " lastPrice=" . $lastPrice . " 24h change=" . $change24Perc . " volume=" . $volume .
                            " Techrate24=" . $techRate24 . " ask=" . $ask . " exchange=" . $exchange . " 5m change=" . $change5mPerc . " 15m chage=" . $change15mPerc . "</br>\n";
                        //get last order


                        if ($asset['free'] > $GLOBALS['Ds_bt_common']->priceToTradeOnSingleCoin() * 2) {
                            $amountToBuy = $GLOBALS['Ds_bt_common']->priceToTradeOnSingleCoin();
                        } else {
                            $amountToBuy = $asset['free'];
                        }


                        $buyOrderBook = $GLOBALS['Ds_bt_common']->buyOrderBook(substr($fullSymbol, 0, -4), $amountToBuy, $GLOBALS['Ds_bt_common']->baseAsset(), 5, $GLOBALS['Ds_bt_common']->api_key());
                        echo substr($fullSymbol, 0, -4)."   is ";
                        // print_r($buyOrderBook);
echo "quantity=" . $buyOrderBook['quantity']. " lastOnOrderBook=" . $buyOrderBook['lastOnOrderBook']. " amountToBuy=" . $buyOrderBook['amountToBuy'];
                            // break;
                            // self::save_trade($fullSymbol, "BUY", "SPOT", $quantity, $lastPrice, 'orderId', 'orderListId', 'clientOrderId', 'transactTime');
                    } else
                        echo $fullSymbol . " not bought RECOMMENDATION is " . $symbolRecomendation . "</br>\n";
                }
            }
        } echo "there is no good recommendation (not to buy at this time)</br>\n";
    }
}
