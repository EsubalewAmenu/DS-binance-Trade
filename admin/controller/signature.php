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
class Ds_bt_Signature
{

	public function __construct()
	{
	}


	public function generate($symbol, $side, $type, $quantity, $price, $recvWindow, $timestamp)
	{
        $query_string = 'timestamp=1646218991480';
        $secret = 'tSicM8dB17cncJzmKt4PnGxMh1OXE8aIBnbMnyEnayVNlXpgJhLKjqTZlXZp7yDO';
        
        // echo "hashing the string:".PHP_EOL;
        // echo $query_string.PHP_EOL;
        // echo "and return:".PHP_EOL;
        // echo self::signature($query_string, $secret).PHP_EOL.PHP_EOL;

        // return self::signature($query_string, $secret).PHP_EOL.PHP_EOL;
        
        $another_string = "symbol=$symbol&side=$side&type=$type&timeInForce=GTC&quantity=$quantity&price=$price&recvWindow=$recvWindow&timestamp=$timestamp";
        // echo "hashing the string:".PHP_EOL;
        // echo $another_string.PHP_EOL;
        // echo "and return:".PHP_EOL;
        return self::signature($another_string, $secret);
	}


    function signature($query_string, $secret) {
        return hash_hmac('sha256', $query_string, $secret);
    }

}
