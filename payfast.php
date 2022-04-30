<?php 
/***
Plugin Name: PayFast - Woocommerce Payment Gateway
Plugin URI: https://www.payfast.pro
Description: PayFast - Woocommerce Payment Gateway.
Author: PayFast
Version: 1.0
***/

// No direct accessable
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Make sure WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}

define('srz_wallet_dir', plugin_dir_path(__FILE__));
define('srz_wallet_ass', plugins_url( 'assets/',__FILE__));
define('wallet_base_name', plugin_basename(__FILE__));

/****site config ****/
//site link
define('payment_site_url_srz', 'https://www.payfast.pro');

//site link
define('payment_site_support_mail', 'contact@payfast.pro');

//Wallet Site Name 
define('payment_name_srz', 'PayFast');

//Payment Id (Use Unique id)
define('payment_id_srz', 'payfast');

//payment slug ex slug/gateway/v1/
define('payment_slug_srz', 'PayFast');


/***
*    Including main Loader of this plugin 
***/
require(srz_wallet_dir.'includes/load.php');
if (!function_exists('wallet_srz_Plugin'))
{
	function wallet_srz_Plugin()
	{
		//main class
		$runsrz= new WalletSrz();
		$runsrz->run();
	}
}
// run the plugin 
wallet_srz_Plugin();