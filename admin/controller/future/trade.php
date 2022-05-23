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
class Ds_bt_future_trade
{

    public function __construct()
    {
        $GLOBALS['Ds_bt_future_common'] = new Ds_bt_future_common();
        $GLOBALS['Ds_bt_common'] = new Ds_bt_common();
    }

    public function main()
    {

        // $GLOBALS['openOrders'] = $GLOBALS['Ds_bt_future_common']->openOrders(self::api_key(), self::api_secret());
        // print_r($GLOBALS['openOrders']);
        // $GLOBALS['Ds_bt_future_common']->cancelBuyOrdersIfTooksLong($GLOBALS['openOrders'], self::api_key(), self::api_secret());



        if ($GLOBALS['Ds_bt_future_common']->isSymbolsUpdated(self::api_key())) {

            //     echo "test user";
            $myAssets = $GLOBALS['Ds_bt_future_common']->myAccount(self::api_key(), self::api_secret());
            // print_r($myAssets);
            if ($myAssets) {
                self::checkAssets($myAssets);
            }
        }
    }
    public function checkAssets($myAssets)
    {
        echo "checking assets " . time() . "</br>\n";

        $onHold = $GLOBALS['Ds_bt_future_common']->isNotHold($myAssets['positions']);
        // if (!$onHold) {
        foreach ($myAssets['assets'] as $asset) {

            // if ($asset['free'] > 0 && $asset['asset'] != "BUSD" && $asset['asset'] != "USDT") {
            //     self::checkAndSell($asset);
            // } else 
            if ($asset['asset'] == "BUSD" || $asset['asset'] == "USDT") {
                if ($asset['availableBalance'] > 7) {
                    echo $asset['asset'] . " buy started. free is " . $asset['availableBalance'] . "\n";
                    self::checkAndBuy($asset, $myAssets['positions']);
                }
            }
        }
        // } else 
        if ($onHold) {

            foreach ($myAssets['positions'] as $position) {
                if ($position['maintMargin'] > 0) {
                    // echo "there is on hold";
                    // print_r($onHold);
                    // ROE% = PnL / Initial Margin = side * (1 - entry price / exit price) / IMR
                    $ROE = ($position['unrealizedProfit'] / $position['initialMargin']) * 100;
                    $symbolRecomendation = $GLOBALS['Ds_bt_common']->symbol_status($position['symbol'], $GLOBALS['Ds_bt_future_common']->depend_on_interval());
                    if ($position['positionAmt'] < 0)  $short_long = "SHORT";
                    else $short_long = "LONG";
                    echo "\n" . $position['symbol'] . " is on hold - leverage = " . $position['leverage'] . " (" . $short_long . ") ROE% = " . $ROE . " Profit=" . $position['unrealizedProfit'] . " recommendation is " . $symbolRecomendation;

                    if ($ROE > 1) {
                        if (($position['positionAmt'] < 0 && ($symbolRecomendation == "SELL" || $symbolRecomendation == "NUTRAL" || $symbolRecomendation == "BUY" || $symbolRecomendation == "STRONG_BUY")
                            ) ||
                            ($position['positionAmt'] > 0 && ($symbolRecomendation == "SELL" || $symbolRecomendation == "NUTRAL" || $symbolRecomendation == "BUY" || $symbolRecomendation == "STRONG_SELL")
                            )
                        ) 
                        {
                            // echo "buyorderboook test " . $fullSymbol;
                            // print_r($buyOrderBook);
                            // echo "\n";
                            // $buyOrderBook['quantity'] > 0 && ($buyOrderBook['quantity'] * $buyOrderBook['lastOnOrderBook']) <= $amountToBuy

                            if ($position['positionAmt'] < 0) {
                                $position['positionAmt'] *= -1;
                                $side = "BUY";
                                // $price = $GLOBALS['Ds_bt_future_common']->buyOrderBook(substr($position['symbol'], 0, -4), $position['initialMargin'], $GLOBALS['Ds_bt_future_common']->baseAsset(), 5, self::api_key())['lastOnOrderBook'];
                            } else {
                                $side = "SELL";

                                // $orderBook = $GLOBALS['Ds_bt_future_common']->getDepth($position['symbol'], 5, self::api_key()); // get orderbook (BUY)

                                // $price = $orderBook['sell_by'];
                            }
                            $closePosition = $GLOBALS['Ds_bt_future_common']->closePosition($position['symbol'], $side, $position['positionAmt'], $GLOBALS['Ds_bt_future_common']->recvWindow(), self::api_key(), self::api_secret());
                            print_r($closePosition);
                        }
                    } else if ($ROE < -50) {
                        echo " Too much kisara!";
                    }
                }
            }
        }
    }
    public function checkAndSell($asset)
    {
        // echo "there is " . $asset['free'] . " free " . $asset['asset'] . " </br>/n";

        $dbSymbol = $GLOBALS['Ds_bt_future_common']->getSymbolFromDB($asset['asset']);

        if ($dbSymbol) {

            if (($asset['free'] * $dbSymbol->lastPrice) > 11) {

                // echo "Current price * free is " . ($asset['free'] * $dbSymbol->lastPrice) . " where asset['free'] is " . $asset['free'] . "dbSymbol->lastPrice price is " . $dbSymbol->lastPrice . "</br>/n";
                // get bought price
                // order sell by adding 1% on bought price or current price
                $myTrades = $GLOBALS['Ds_bt_future_common']->myTrades($asset['asset'] . $GLOBALS['Ds_bt_future_common']->baseAsset(), 15, self::api_key(), self::api_secret());

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
                            // $lastPrice = $GLOBALS['Ds_bt_future_common']->getPrice($asset['asset'] . $GLOBALS['Ds_bt_future_common']->baseAsset(), self::api_key(), self::api_secret()); // price range
                            // if ($price > $lastPrice) {
                            //     $sellingPrice = $price + (0.005 * $price);
                            // } else {
                            //     $sellingPrice = $lastPrice + (0.005 * $lastPrice);
                            // }
                            $orderBook = $GLOBALS['Ds_bt_future_common']->getDepth($asset['asset'], $GLOBALS['Ds_bt_future_common']->baseAsset(), 2, self::api_key()); // get orderbook (BUY)

                            if ($price > $orderBook['sell_by']) {
                                $sellingPrice = $price + (0.01 * $price);
                            } else {
                                $sellingPrice = $orderBook['sell_by'] + (0.01 * $orderBook['sell_by']);
                            }

                            // $afterPoint = 0;
                            // for ($i = 0; $i < strlen($dbSymbol->precisionPrice) - 1; $i++) {
                            //     if ($dbSymbol->precisionPrice[$i] == '1') {
                            //         break;
                            //     } else if ($dbSymbol->precisionPrice[$i] == '0')
                            //         $afterPoint++;
                            // }
                            // $sellingPrice = $GLOBALS['Ds_bt_future_common']->floorDec($sellingPrice, $afterPoint);
                            $sellingPrice = $GLOBALS['Ds_bt_future_common']->precisionPrice($dbSymbol->precisionPrice, $sellingPrice);

                            // $quantityAfterPoint = 0;
                            // for ($i = 0; $i < strlen($dbSymbol->min_lot_size) - 1; $i++) {
                            //     if ($dbSymbol->min_lot_size[$i] == '1') {
                            //         break;
                            //     } else if ($dbSymbol->min_lot_size[$i] == '0')
                            //         $quantityAfterPoint++;
                            // }

                            // $freeQuantity = $GLOBALS['Ds_bt_future_common']->floorDec($asset['free'], $quantityAfterPoint);
                            $freeQuantity = $GLOBALS['Ds_bt_future_common']->precisionQuantity($dbSymbol->min_lot_size, $asset['free']);
                            // order sell by price
                            // echo "price is $price freeQuantity $freeQuantity sellingPrice is " . $sellingPrice;
                            $type = "MARKET";
                            $orderResult = $GLOBALS['Ds_bt_future_common']->order($asset['asset'] . $GLOBALS['Ds_bt_future_common']->baseAsset(), "SELL", $type, $freeQuantity, $sellingPrice, $GLOBALS['Ds_bt_future_common']->recvWindow(), self::api_key(), self::api_secret());
                            print_r($orderResult);
                            break;
                        }
                    }
                }
            }
        }
    }
    public function checkAndBuy($asset, $positions)
    {
        // print_r($asset);

        if (isset($positions)) {

            // $openOrders = $GLOBALS['Ds_bt_future_common']->openOrders(self::api_key(), self::api_secret());


            // print_r($response['data']);
            foreach ($positions as $position) {
                // print_r($symbol);

                $fullSymbol = $position['symbol'];
                if (str_ends_with($fullSymbol, $asset['asset'])) {
                    echo "fullSymbol is " . $fullSymbol . "</br>";
                    // if (!$GLOBALS['Ds_bt_future_common']->isNotHold(substr($fullSymbol, 0, -4), $positions)) {
                    //     echo $fullSymbol . " already on hold</br>\n";
                    //     // } else if ($GLOBALS['Ds_bt_future_common']->isNotOnOrder($fullSymbol, $openOrders)) {
                    // } else if ($GLOBALS['Ds_bt_future_common']->isNotOnOrder($fullSymbol, $GLOBALS['openOrders'])) {

                    $symbolRecomendation = $GLOBALS['Ds_bt_common']->symbol_status($fullSymbol, $GLOBALS['Ds_bt_future_common']->depend_on_interval());

                    if ($symbolRecomendation == 'STRONG_BUY' || $symbolRecomendation == 'STRONG_SELL') {
                        $hourRecomendation = $GLOBALS['Ds_bt_common']->symbol_status($fullSymbol . "PERP", '1h');
                        echo $fullSymbol . " RECOMMENDATION is " . $symbolRecomendation . " hour recom is " . $hourRecomendation;
                        if ($symbolRecomendation == 'STRONG_BUY' && ($hourRecomendation == 'STRONG_BUY')) { //|| $hourRecomendation == 'BUY')) {
                            echo "buy this </br>\n";
                            //     //get last order

                            if ($asset['availableBalance'] > $GLOBALS['Ds_bt_future_common']->priceToTradeOnSingleCoin() * 2) {
                                $amountToBuy = $GLOBALS['Ds_bt_future_common']->priceToTradeOnSingleCoin();
                            } else {
                                $amountToBuy = $asset['availableBalance'];
                            }

                            // echo "buyorderboook test " . $fullSymbol;
                            $buyOrderBook = $GLOBALS['Ds_bt_future_common']->buyOrderBook(substr($fullSymbol, 0, -4), $amountToBuy, $GLOBALS['Ds_bt_future_common']->baseAsset(), 5, self::api_key());
                            // print_r($buyOrderBook);
                            // echo "\n";
                            if ($buyOrderBook['quantity'] > 0 && ($buyOrderBook['quantity'] * $buyOrderBook['lastOnOrderBook']) <= $amountToBuy) {
                                //         // echo substr($fullSymbol, 0, -4) . "   is ";
                                //         // print_r($buyOrderBook);
                                echo " quantity=" . $buyOrderBook['quantity'] . " lastOnOrderBook=" . $buyOrderBook['lastOnOrderBook'] . " amountToBuy=" . $buyOrderBook['amountToBuy'];


                                // if ($hourRecomendation == "STRONG_BUY")
                                $leverage = 10;
                                // else
                                //     $leverage = "5";
                                // $changeLeverage = $GLOBALS['Ds_bt_future_common']->changeLeverage($fullSymbol, $leverage, self::api_key(), self::api_secret());

                                // echo "print ";
                                // print_r($changeLeverage);

                                // if ($changeLeverage) {
                                $type = "MARKET";
                                $orderResult = $GLOBALS['Ds_bt_future_common']->order($fullSymbol, "BUY", $type, ($buyOrderBook['quantity'] * $leverage), $buyOrderBook['lastOnOrderBook'], $GLOBALS['Ds_bt_future_common']->recvWindow(), self::api_key(), self::api_secret());
                                print_r($orderResult);

                                global $table_prefix, $wpdb;
                                $wp_ds_table = $table_prefix . "ds_bt_symbols";

                                $data = ['lastPrice' => $buyOrderBook['lastOnOrderBook'],];
                                $where = ['symbol' => substr($fullSymbol, 0, -4)];
                                $wpdb->update($wp_ds_table, $data, $where);

                                $asset['availableBalance'] -= $amountToBuy;
                                if ($asset['availableBalance'] < $GLOBALS['Ds_bt_future_common']->priceToTradeOnSingleCoin())
                                    break;
                                // }
                            }
                        } else if ($symbolRecomendation == 'STRONG_SELL' && ($hourRecomendation == 'STRONG_SELL')) { // || $hourRecomendation == 'SELL')) {

                            echo "sell this </br>\n";


                            $orderBook = $GLOBALS['Ds_bt_future_common']->getDepth($fullSymbol, 5, self::api_key()); // get orderbook (BUY)

                            // if ($hourRecomendation == "STRONG_SELL")
                            $leverage = 10;
                            // else
                            //     $leverage = "5";

                            // $changeLeverage = $GLOBALS['Ds_bt_future_common']->changeLeverage($fullSymbol, $leverage, self::api_key(), self::api_secret());

                            // if ($changeLeverage) {

                            $dbSymbol = $GLOBALS['Ds_bt_common']->getSymbolFromDB(substr($fullSymbol, 0, -4));
                            $sellingPrice = $GLOBALS['Ds_bt_common']->precisionPrice($dbSymbol->precisionPrice, $orderBook['sell_by']);
                            $freeQuantity = $GLOBALS['Ds_bt_common']->precisionQuantity($dbSymbol->min_lot_size, ($asset['availableBalance'] / $orderBook['sell_by']));

                            $type = "MARKET";
                            $orderResult = $GLOBALS['Ds_bt_future_common']->order($fullSymbol, "SELL", $type, ($freeQuantity * $leverage), $sellingPrice, $GLOBALS['Ds_bt_common']->recvWindow(), self::api_key(), self::api_secret());
                            print_r($orderResult);

                            global $table_prefix, $wpdb;
                            $wp_ds_table = $table_prefix . "ds_bt_symbols";

                            $data = ['lastPrice' => $orderBook['sell_by'],];
                            $where = ['symbol' => substr($fullSymbol, 0, -4)];
                            $wpdb->update($wp_ds_table, $data, $where);

                            // $asset['availableBalance'] -= $amountToBuy;
                            // if ($asset['availableBalance'] < $GLOBALS['Ds_bt_future_common']->priceToTradeOnSingleCoin())
                            break;
                            // }
                        } else echo " </br>\n";
                    }
                }
            }
        }
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
