<?php
// PayFast Woocommerce Gateway
// Author: GIWS

if (!class_exists('SrzWalletRewrite')) {
	/**
	 * rewrite class
	 */
	class SrzWalletRewrite
	{
		
		function __construct()
		{
			//init hook
			add_action('init',array($this,'reWrite'),10,1);
			//query var register
			add_filter('query_vars', array($this,'query_vars'));
			//showing order page
			add_action( 'template_include', function( $template ) {
				if ( get_query_var( 'srz_mimi' ) == false || get_query_var( 'srz_mimi' ) == '' && isset($_POST) ) {
					return $template;
				}
				$this->SohagMimi();
			} );
		}
 
		/**
		 * callback & process of the order.
		 * return void
		 */
		public function SohagMimi()
		{ 
			if (isset($_GET['order_id']) && isset($_GET['srzsecurity']) && isset($_POST)) {  
				if (get_option('woocommerce_'.payment_id_srz.'_settings') === '' or empty(get_option('woocommerce_'.payment_id_srz.'_settings')))  exit(__('Please do setting first','payfast-pro'));
			 $data=get_option('woocommerce_'.payment_id_srz.'_settings');
			  if (!isset($data['api']) or !isset($data['srz_merchant_acc_key'])) exit(__('Please do setting first!','payfast-pro'));
				$order_id=$_GET['order_id'];
				$order =  wc_get_order($order_id);
				if (!$order) {wp_redirect(home_url()); exit(__('Invalid Id','payfast-pro'));}
           $product_title = array();
           $items = $order->get_items();
            foreach($items as $item => $values) 
            { 
            	$product        = $values->get_product(); 
                $product_title[] = $product->get_title();
            } 
             $product_name = implode(",",$product_title);
             $txid=  (isset($_POST['txid'])) ?$_POST['txid'] : '' ;
				$allfields=array(
					'access_key' => $data['srz_merchant_acc_key'],
					'item_number' =>  $order->get_item_count(),
					'details' => $product_name,
					'amount' => $order->get_total(),
					'currency_code' =>  get_woocommerce_currency(),
					'txid' => $txid,
					'payment_time_' => '',
					'payee_account_' => ''); 
				if ($this->checkAllPost($allfields)) {
					 if($order->get_status() ==='pending')  {}else{ wp_redirect($order->get_view_order_url());
					 	exit(__('Its Already Processing','payfast-pro'));} 
					 $merchant_key = $data['api'];
					 $access_key = $data['srz_merchant_acc_key'];
					$access_key = $_POST['access_key'];
					$Url = payment_site_url_srz."/web_hook.php";
					$response = wp_remote_post($Url, array(
						'method'      => 'GET',
						'timeout'     => 30,
						'redirection' => 10,
						'httpversion' => '1.1',
						'blocking'    => true,
						'headers'     => array(),
						'body'        => array(),
						'cookies'     => array(), 
					)
				);
		if($response['response']['code'] == 200)
		{	
			$result = json_decode($response['body']);
			if (isset($result->status) && $result->status==='success') {
				global $woocommerce;
				#payment ok :)  
				$order->payment_complete();
				$order->add_order_note($_POST['payee_account_'].' Payment success');
				$woocommerce->cart->empty_cart();
				$return_url = $order->get_checkout_order_received_url();
				$order->reduce_order_stock();
				wp_redirect($return_url);
				exit();
			}else{
				# payment error
				wp_redirect($order->get_view_order_url());
				exit();
			} 
		}else{
			wp_redirect(home_url());
			exit(__('Server Issue!','payfast-pro'));
		}
	  }else{
			wp_redirect(home_url());
				exit(__('No valid data!','payfast-pro'));
			}
		}

			wp_redirect(home_url());
			exit(__('Server Issue!','payfast-pro'));

	}

	/**
	*** Data matching to valid
	* @param $data array
	*return bool
	***/
	public function checkAllPost($data)
		{  
			if(is_array($data)){
				foreach ($data as $name => $value) {
					if (!isset($_POST[$name])) {return false;}
					$postDts = $_POST[$name];
					if(isset($postDts) && !empty($postDts) && $postDts !=='' ){}else{return false;}
					if ($name === 'payment_time_' or $name ==='payee_account_') continue;
					if ($value === $postDts or $value == $postDts) {
					}else{
						return false;
					} 
				}
			}else{
				return false;
			}
			return true;
		}

	/**
	* Query var callback
	* @param $vars array
	*return array
	*/
	public function query_vars($vars) {
			$vars[] = 'srz_mimi';
			 return $vars;
			}

	/**
	* Custom rewrite for order check page
	*/
	public function reWrite()
		{
			flush_rewrite_rules();
			$regex ='^'.payment_slug_srz.'/gateway/v1/?';
			add_rewrite_rule($regex, 'index.php?srz_mimi=sohagMimi', 'top');
		}
	}
}