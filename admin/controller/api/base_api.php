<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/EsubalewAmenu
 * @since      1.0.0
 *
 * @package    Mp_CF
 * @subpackage Mp_CF/admin
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Mp_CF
 * @subpackage Mp_CF/admin
 * @author     Esubalew Amenu <esubalew.a2009@gmail.com>
 */
class DS_bt_admin_base_api
{

    public function __construct()
    {
    }

    function rest_test_trading_view()
    {
        add_action('rest_api_init', function () {
            register_rest_route(ds_bt . '/v1', '/test/trading_view', array(
                'methods' => 'GET',
                'callback' => function (WP_REST_Request $request) {
                    // $tx_hash = $request->get_param('tx_hash');

                    $Ds_bt_usdtinr = new Ds_bt_usdtinr();
                    $Ds_bt_usdtinr->main();
                },
                'permission_callback' => function () {
                    return true; //current_user_can('edit_others_posts');
                }
            ));
        });
    }
    function rest_check_trade()
    {
        add_action('rest_api_init', function () {
            register_rest_route(ds_bt . '/v1', '/trade/spot', array(
                'methods' => 'GET',
                'callback' => function (WP_REST_Request $request) {
                    // $tx_hash = $request->get_param('tx_hash');

                    $Ds_bt_spot = new Ds_bt_spot();
                    $Ds_bt_spot->main();
                },
                'permission_callback' => function () {
                    return true; //current_user_can('edit_others_posts');
                }
            ));
        });
    }
    function rest_check_hold()
    {
        add_action('rest_api_init', function () {
            register_rest_route(ds_bt . '/v1', '/hold/spot', array(
                'methods' => 'GET',
                'callback' => function (WP_REST_Request $request) {
                    // $tx_hash = $request->get_param('tx_hash');
                    $Ds_bt_holder = new Ds_bt_holder();
                    $Ds_bt_holder->main();
                },
                'permission_callback' => function () {
                    return true; //current_user_can('edit_others_posts');
                }
            ));
        });
    }

    function rest_check_margin_trade()
    {
        add_action('rest_api_init', function () {
            register_rest_route(ds_bt . '/v1', '/trade/margin', array(
                'methods' => 'GET',
                'callback' => function (WP_REST_Request $request) {
                    // $tx_hash = $request->get_param('tx_hash');

                    $Ds_bt_margin = new Ds_bt_margin();
                    $Ds_bt_margin->main();
                },
                'permission_callback' => function () {
                    return true; //current_user_can('edit_others_posts');
                }
            ));
        });
    }
    function rest_check_tradingview()
    {
        add_action('rest_api_init', function () {
            register_rest_route(ds_bt . '/v1', '/tradingview/spot', array(
                'methods' => 'GET',
                'callback' => function (WP_REST_Request $request) {
                    $Ds_bt_tradingview = new Ds_bt_tradingview();
                    $Ds_bt_tradingview->main();
                },
                'permission_callback' => function () {
                    return true; //current_user_can('edit_others_posts');
                }
            ));
        });
    }
    function rest_check_future()
    {
        add_action('rest_api_init', function () {
            register_rest_route(ds_bt . '/v1', '/future/trade', array(
                'methods' => 'GET',
                'callback' => function (WP_REST_Request $request) {
                    $Ds_bt_future_trade = new Ds_bt_future_trade();
                    $Ds_bt_future_trade->main();
                },
                'permission_callback' => function () {
                    return true; //current_user_can('edit_others_posts');
                }
            ));
        });
    }
    function rest_check_trade1p()
    {
        add_action('rest_api_init', function () {
            register_rest_route(ds_bt . '/v1', '/trade1p/spot', array(
                'methods' => 'GET',
                'callback' => function (WP_REST_Request $request) {
                    $Ds_bt_trade1p = new Ds_bt_trade1p();
                    $Ds_bt_trade1p->main();
                },
                'permission_callback' => function () {
                    return true; //current_user_can('edit_others_posts');
                }
            ));
        });
    }
    function rest_check_holderv2()
    {
        add_action('rest_api_init', function () {
            register_rest_route(ds_bt . '/v1', '/holderv2/spot', array(
                'methods' => 'GET',
                'callback' => function (WP_REST_Request $request) {
                    $Ds_bt_holderv2 = new Ds_bt_holderv2();
                    $Ds_bt_holderv2->main();
                },
                'permission_callback' => function () {
                    return true; //current_user_can('edit_others_posts');
                }
            ));
        });
    }
    function rest_check_test()
    {
        add_action('rest_api_init', function () {
            register_rest_route(ds_bt . '/v1', '/test', array(
                'methods' => 'GET',
                'callback' => function (WP_REST_Request $request) {
                    $Ds_bt_test = new Ds_bt_test();
                    $Ds_bt_test->main();
                },
                'permission_callback' => function () {
                    return true; //current_user_can('edit_others_posts');
                }
            ));
        });
    }
}
