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
        $symbol = "DODO";

        $openOrders = $GLOBALS['Ds_bt_common']->openOrders();

        // echo "openOrders RESponse is</br>\n";
        // print_r($openOrders);
        foreach ($openOrders as $openOrder) {
            if (abs(round(microtime(true) * 1000) - $openOrder['time']) > 300000) { // && $openOrder['side'] == 'BUY') {
                //cancel order $openOrder['orderId']
                echo 'order ' . $openOrder['symbol'] . " " . $openOrder['side'] . ' tooks long WILL CANCEL\n';

                $openOrders = $GLOBALS['Ds_bt_common']->cancelSingleOrder( $openOrder['symbol'],  $openOrder['orderId']);
                print_r($openOrders);
            }
        }
    }
}
