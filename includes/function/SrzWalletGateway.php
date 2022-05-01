<?php 
// PayFast Woocommerce Gateway
// Author: GIWS

/***
* run this method on plugins_loaded
* reurn void
***/
function SrzGatewayMakeWallet(){  
srz_woo_method();
}
//hook for load when plugins loaded
add_action( 'plugins_loaded','SrzGatewayMakeWallet', 11 );

/***
* run this method on check woocomerce payment gateways
* @param array
*return array
***/
function wc_Srz_wallet_add_to_gateways( $gateways ) {
	$gateways[] = 'WC_Gateway_SrzWoo_Gateway';
	return $gateways;
}
//register woocomerce gateways
add_filter( 'woocommerce_payment_gateways', 'wc_Srz_wallet_add_to_gateways' ); 

