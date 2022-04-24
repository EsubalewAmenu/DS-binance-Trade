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
        $symbol = "IOST";

        $symbolRecomendation = $GLOBALS['Ds_bt_common']->symbol_status($symbol . $GLOBALS['Ds_bt_common']->baseAsset(), $GLOBALS['Ds_bt_common']->depend_on_interval());

        echo "openOrders RESponse is</br>\n";
        print_r($symbolRecomendation);
        
        $symbolRecomendation = $GLOBALS['Ds_bt_common']->scanSingleCrypto($symbol . $GLOBALS['Ds_bt_common']->baseAsset());

        echo "openOrders RESponse is</br>\n";
        print_r($symbolRecomendation);
    }
}
