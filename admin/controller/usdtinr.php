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
class Ds_bt_usdtinr
{

    public function __construct()
    {
    }

    public function main()
    {
        $secret = self::api_secret();
        $key = self::api_key();


        if (isset($_GET['symbol']))
            $symbol = $_GET['symbol'];
        else        $symbol = "INR";



        $buy_interval = "5m";
        $sell_interval = "5m";
        //1m3m5m15m30m1h2h4h6h8h12h1d3d1w1M
        // $precisionPrice = 2;
        // $precisionQuantity = 5;//btc
        // $precisionQuantity = 2; //luna
        // $precisionQuantity = 1; //GLMR
        $recvWindow = 50000;

        $tradeType = "normal"; // normal_profit, fixed_profit
        $fixed_profit_value = 0.001;

        $BUSD_USDT = "USDT";
        self::checkUsdtInr($symbol, $BUSD_USDT, $sell_interval, $key, $secret, $recvWindow);
    }


    public function checkUsdtInr($asset, $BUSD_USDT, $depend_on_interval, $key, $secret, $recvWindow)
    {
        // echo "test";

        $url = "https://scanner.tradingview.com/crypto/scan";


        $data = '{"filter":[{"left":"exchange","operation":"nempty"},{"left":"exchange","operation":"equal","right":"BITMEX"},{"left":"name,description","operation":"match","right":"usdtinr"}],"options":{"lang":"en"},"markets":["crypto"],"symbols":{"query":{"types":[]},"tickers":[]},"columns":["base_currency_logoid","currency_logoid","name","close","change","change_abs","high","low","volume","Recommend.All","exchange","description","type","subtype","update_mode","pricescale","minmov","fractional","minmove2"],"sort":{"sortBy":"exchange","sortOrder":"asc"},"range":[0,150]}';

        $Ds_bt_common = new Ds_bt_common();
        $response = $Ds_bt_common->postAPI($url, $data);

        // print_r($response);

        if ($response['code'] == 200 || $response['code'] == 201) {
            $response = json_decode($response['result'], true);

            // print_r($response['data']);
            foreach ($response['data'] as $symbol) {
                $fullSymbol = $symbol['d'][2];
                if ($fullSymbol == $BUSD_USDT . $asset) { //"BUSD")) {
                    $lastPrice = $symbol['d'][3];
                    $change24Perc = $symbol['d'][4];
                    $volume = $symbol['d'][5];
                    $techRate24 = $symbol['d'][6];
                    $ask = $symbol['d'][7];
                    $exchange = $symbol['d'][8];
                    $change5mPerc = $symbol['d'][9];
                    $change15mPerc = $symbol['d'][10];

                    echo "buy Symbol = " . $fullSymbol . " lastPrice=" . $lastPrice . " 24h change=" . $change24Perc . " volume=" . $volume .
                        " Techrate24=" . $techRate24 . " ask=" . $ask . " exchange=" . $exchange . " 5m change=" . $change5mPerc . " 15m chage=" . $change15mPerc . "</br>\n";
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
