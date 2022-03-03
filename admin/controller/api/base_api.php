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
}
