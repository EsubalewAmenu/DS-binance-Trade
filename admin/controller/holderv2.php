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
class Ds_bt_holderv2
{

    public function __construct()
    {
        $GLOBALS['Ds_bt_common'] = new Ds_bt_common();
    }

    public function main()
    {
        // $GLOBALS['Ds_bt_common']->cancelBuyOrdersIfTooksLong($GLOBALS['Ds_bt_common']->openOrders(self::api_key(), self::api_secret()), self::api_key(), self::api_secret());

        // echo " bought symbol STMXBUSD side BUY origQty 1567.00000000 price 0.01914000 </br>\N ";
        // $asset['asset'] = "STMX";
        // $asset['free'] = "1567";
        // self::checkAndSell($asset);

        // echo " bought when order id 38827456 symbol OMBUSD side BUY origQty 133.00000000 price 0.10520000</br>\n";
        // $asset['asset'] = "OM";                
        // $asset['free'] = "133.00000000";
        // self::checkAndSell($asset);

        // $myAssets = $GLOBALS['Ds_bt_common']->myAccount(self::api_key(), self::api_secret());
        // if ($myAssets) {
        //     self::checkAssets($myAssets);
        // }
    }
    public function checkAssets($myAssets)
    {
        echo "checking assets to hold " . time() . "</br>\n";

        foreach ($myAssets['balances'] as $asset) {

            if ($asset['free'] > 0 && $asset['asset'] != "BUSD" && $asset['asset'] != "USDT") {
                self::checkAndSell($asset);
            } else if ($asset['asset'] == "BUSD" || $asset['asset'] == "USDT") {
                if ($asset['free'] > 11) {
                    echo $asset['asset'] . " buy started to hold. free is " . $asset['free'] . "\n";
                    self::checkAndBuy($asset, $myAssets['balances']);
                }
            } else if ($asset['locked'] > 0 &&  $asset['asset'] != "BUSD" && $asset['asset'] != "USDT") {
                $symbolRecomendation = $GLOBALS['Ds_bt_common']->symbol_status($asset['asset'] . $GLOBALS['Ds_bt_common']->baseAsset(), $GLOBALS['Ds_bt_common']->depend_on_interval());
                $scanSingleCrypto = $GLOBALS['Ds_bt_common']->scanSingleCrypto($asset['asset'] . $GLOBALS['Ds_bt_common']->baseAsset());
                echo $asset['asset'] . " locked=" . $asset['locked'] . " symbolRecomendation $symbolRecomendation 5 min profit/loss " . $scanSingleCrypto['change5m'] . "</br>\n";
                if ($scanSingleCrypto['change5m'] < -1 || $symbolRecomendation == 'STRONG_SELL' || $symbolRecomendation == 'SELL') { // || $symbolRecomendation == 'NEUTRAL' ) {

                    $orderBook = $GLOBALS['Ds_bt_common']->getDepth($asset['asset'], $GLOBALS['Ds_bt_common']->baseAsset(), 2, self::api_key()); // get orderbook (BUY)
                    if (isset($orderBook['sell_by'])) {
                        $cancelOrder = $GLOBALS['Ds_bt_common']->cancelOrder($asset['asset'] . $GLOBALS['Ds_bt_common']->baseAsset(), time(), self::api_key(), self::api_secret());
                        print_r($cancelOrder);
                        //cancel symbol orders 
                        // and order with $orderBook['sell_by']
                        echo "order canceled WILL ORDER WITH " . $orderBook['sell_by'];

                        $type = "LIMIT";
                        $orderResult = $GLOBALS['Ds_bt_common']->order($asset['asset'] . $GLOBALS['Ds_bt_common']->baseAsset(), "SELL", $type, $asset['locked'], $orderBook['sell_by'], $GLOBALS['Ds_bt_common']->recvWindow(), self::api_key(), self::api_secret());
                        print_r($orderResult);
                    }
                }
            }
        }
    }
    public function checkAndSell($asset)
    {
        $dbSymbol = $GLOBALS['Ds_bt_common']->getSymbolFromDB($asset['asset']);

        if ($dbSymbol) {

            if (($asset['free'] * $dbSymbol->lastPrice) > 11) {
                $scanSingleCrypto = $GLOBALS['Ds_bt_common']->scanSingleCrypto($asset['asset'] . $GLOBALS['Ds_bt_common']->baseAsset());
                $symbolRecomendation = $GLOBALS['Ds_bt_common']->symbol_status($asset['asset'] . $GLOBALS['Ds_bt_common']->baseAsset(), $GLOBALS['Ds_bt_common']->depend_on_interval());
                echo "</br>\n".$asset['asset'] . " free=" . $asset['free'] . " symbolRecom $symbolRecomendation scanSingle ".$scanSingleCrypto['change5m']."</br>\n";
                // print_r($scanSingleCrypto);
                //if sell or strong sell
                if ($symbolRecomendation == 'STRONG_SELL' || $symbolRecomendation == 'SELL' || $symbolRecomendation == 'NEUTRAL') {

                    $orderBook = $GLOBALS['Ds_bt_common']->getDepth($asset['asset'], $GLOBALS['Ds_bt_common']->baseAsset(), 2, self::api_key()); // get orderbook (BUY)

                    $sellingPrice = $orderBook['sell_by']; //$lastPrice + (0.005 * $lastPrice);

                    // $afterPoint = 0;
                    // for ($i = 0; $i < strlen($dbSymbol->precisionPrice) - 1; $i++) {
                    //     if ($dbSymbol->precisionPrice[$i] == '1') {
                    //         break;
                    //     } else if ($dbSymbol->precisionPrice[$i] == '0')
                    //         $afterPoint++;
                    // }
                    // $sellingPrice = $GLOBALS['Ds_bt_common']->floorDec($sellingPrice, $afterPoint);
                    $sellingPrice = $GLOBALS['Ds_bt_common']->precisionPrice($dbSymbol->precisionPrice, $sellingPrice);

                    // $quantityAfterPoint = 0;
                    // for ($i = 0; $i < strlen($dbSymbol->min_lot_size) - 1; $i++) {
                    //     if ($dbSymbol->min_lot_size[$i] == '1') {
                    //         break;
                    //     } else if ($dbSymbol->min_lot_size[$i] == '0')
                    //         $quantityAfterPoint++;
                    // }

                    // $freeQuantity = $GLOBALS['Ds_bt_common']->floorDec($asset['free'], $quantityAfterPoint);
                    $freeQuantity = $GLOBALS['Ds_bt_common']->precisionQuantity($dbSymbol->min_lot_size, $asset['free']);
                    // order sell by price
                    echo $asset['asset'] . $GLOBALS['Ds_bt_common']->baseAsset() . " SELL freeQuantity " . $freeQuantity . ' sellingPrice ' . $sellingPrice . ' total ' . $freeQuantity * $sellingPrice;
                    $type = "LIMIT";
                    // $orderResult = $GLOBALS['Ds_bt_common']->order($asset['asset'] . $GLOBALS['Ds_bt_common']->baseAsset(), "SELL", $type, $freeQuantity, $sellingPrice, $GLOBALS['Ds_bt_common']->recvWindow(), self::api_key(), self::api_secret());
                    // print_r($orderResult);
                }
            }
        }
    }
    public function checkAndBuy($asset, $myAssets)
    {
        $response = $GLOBALS['Ds_bt_common']->scanCrypto($asset['asset']); // base (BUSD OR USDT)
        if (isset($response)) {
            foreach ($response['data'] as $symbol) {
                $fullSymbol = $symbol['d'][2];
                if ($GLOBALS['Ds_bt_common']->isNotHold(substr($fullSymbol, 0, -4), $myAssets)) {
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

                    if ($symbolRecomendation == 'STRONG_BUY') { // || $symbolRecomendation == 'BUY') {

                        if ($asset['free'] > $GLOBALS['Ds_bt_common']->priceToTradeOnSingleCoin() * 2) {
                            $amountToBuy = $GLOBALS['Ds_bt_common']->priceToTradeOnSingleCoin();
                        } else {
                            $amountToBuy = $asset['free'];
                        }


                        $buyOrderBook = $GLOBALS['Ds_bt_common']->buyOrderBook(substr($fullSymbol, 0, -4), $amountToBuy, $GLOBALS['Ds_bt_common']->baseAsset(), 5, self::api_key());
                        if ($buyOrderBook['quantity'] > 0) {
                            echo $fullSymbol . " BUY  quantity " . $buyOrderBook['quantity'] . ' lastOnOrderBook ' . $buyOrderBook['lastOnOrderBook'] . ' total ' .  $buyOrderBook['quantity'] * $buyOrderBook['lastOnOrderBook'];
                            $type = "LIMIT";
                            // $orderResult = $GLOBALS['Ds_bt_common']->order($fullSymbol, "BUY", $type, $buyOrderBook['quantity'], $buyOrderBook['lastOnOrderBook'], $GLOBALS['Ds_bt_common']->recvWindow(), self::api_key(), self::api_secret());
                            // print_r($orderResult);

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
    public function api_secret()
    {
        return 'tSicM8dB17cncJzmKt4PnGxMh1OXE8aIBnbMnyEnayVNlXpgJhLKjqTZlXZp7yDO';
    }
    public function api_key()
    {
        return '0red2ruc3xogwntDl658JYQaNJAjx8wRQSbSGILRvjRMeHiGEt9Y3dcqp6X5wHf0';
    }
}
