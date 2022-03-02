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
class Ds_bt_buysell
{

	public function __construct()
	{
	}
	public function main()
	{
		$symbol = "BTCBUSD";
		$side = "BUY";
		$type = "LIMIT";
		$quantity = "1";
		$price = "0.1";
		$recvWindow = "50000";

		self::order($symbol, $side, $type, $quantity, $price, $recvWindow);
	}
	public function order($symbol, $side, $type, $quantity, $price, $recvWindow)
	{

		// $BASE_URL = 'https://testnet.binance.vision/api/v3/'; // testnet
		$BASE_URL = "https://api.binance.com/api/v3/";

		require_once ds_bt_PLAGIN_DIR . 'admin/controller/signature.php';
		require_once ds_bt_PLAGIN_DIR . 'admin/controller/common.php';

		$timestamp = time();

		$Ds_bt_Signature = new Ds_bt_Signature();
		$newSignature = $Ds_bt_Signature->generate($symbol, $side, $type, $quantity, $price, $recvWindow, $timestamp);

		echo "newSignature " . $newSignature;



		echo "</br>Timestamp is " . $timestamp . " </br>";

		$data = array(
			"symbol" => $symbol,
			"side" => $side,
			"type" => $type,
			"quantity" => $quantity,
			"price" => $price,
			"timestamp" => $timestamp,
			"recvWindow" => $recvWindow,
			"signature" => $newSignature,
			"timeInForce" => "GTC"
		);

		$url = $BASE_URL . "order/test";



		$API_channel = new Ds_bt_common_API();
		$result = $API_channel::callAPI("POST", $url, $data);

		print_r($result);
	}
}
