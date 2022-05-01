<?php
// PayFast Woocommerce Gateway
// Author: GIWS

if (!class_exists('SrzWOGateway')) {
	/**
	 * main class 
	 */
	class SrzWOGateway
	{
		
		function __construct()
		{
			//plugins loaded hook
			add_action('plugins_loaded', array($this,'loaded'), 0);
		}
		
		public function loaded()
		{
			//hook for show links after plugin activation
		add_filter('plugin_action_links_' . wallet_base_name, array($this,'settings_link'));

		} 
		/**
		 * Show links on plugin name 
		 * @param array
		 * return array
		 */
		public function settings_link($links)
		{
		    $pluginLinks = array(
	            'settings' => '<a href="'. esc_url(admin_url( 'admin.php?page=wc-settings&tab=checkout&section='.payment_id_srz)) .'">Settings</a>',
	            'docs'     => '<a href="'.payment_site_url_srz.'/merchant" target="blank">Docs</a>',
	            'create_acc'     => '<a href="'.payment_site_url_srz.'/register" target="blank">Create Account</a>',
	            'support'  => '<a href="mailto:'.payment_site_support_mail.'">Support</a>'
	        );
		    $links = array_merge($links, $pluginLinks);
		    return $links;
		}

	} # class end
} # condition end