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
class Ds_bt_test
{

    public function __construct()
    {
        $GLOBALS['Ds_bt_common'] = new Ds_bt_common();
    }

    public function main()
    {
        $secret = $GLOBALS['Ds_bt_common']->api_secret();
        $key = $GLOBALS['Ds_bt_common']->api_key();
        $symbol ="DODO";

        $cancelOrder = $GLOBALS['Ds_bt_common']->cancelOrder('BSW' . $GLOBALS['Ds_bt_common']->baseAsset(), time(), $GLOBALS['Ds_bt_common']->api_key(), $GLOBALS['Ds_bt_common']->api_secret());
        // $cancelOrder = $GLOBALS['Ds_bt_common']->curl_del('BSW' . $GLOBALS['Ds_bt_common']->baseAsset(), time(), $GLOBALS['Ds_bt_common']->api_key(), $GLOBALS['Ds_bt_common']->api_secret());
        
        echo "cancelOrder RESponse is</br>\n";
        print_r($cancelOrder);

        // // $orderBook = $GLOBALS['Ds_bt_common']->sendRequest("GET", "api/v3/depth?symbol=" . $symbol . $GLOBALS['Ds_bt_common']->baseAsset() . "&limit=2", $key); // get orderbook (BUY)
        // $orderBook = $GLOBALS['Ds_bt_common']->getDepth($symbol, $GLOBALS['Ds_bt_common']->baseAsset(), 2, $key); // get orderbook (BUY)
        
        // echo "orderBook RESponse is</br>\n";
        // print_r($orderBook);
        // echo "will sell by " .$orderBook['sell_by'] ;


    }
}
