<?php
// PayFast Woocommerce Gateway
// Author: GIWS

if (!class_exists('WalletSrz')) {
	/**
	 * this class for plugin load :)
	 */
	class WalletSrz 
	{
		public function depencies(){  
			require_once( srz_wallet_dir . "includes/class/srzwogateway.class.php" );
			require_once( srz_wallet_dir . "includes/class/woo.class.php" );
			require_once( srz_wallet_dir . "includes/class/rewrite.class.php" );
			require_once( srz_wallet_dir . "includes/function/SrzWalletGateway.php" );
		}
		public function run()
		{
			//depencies load
			self::depencies();
			
			//loading all object & class
			new SrzWOGateway();
			new SrzWalletRewrite();
		} 
	}
}
