<?php
// PayFast Woocommerce Gateway
// Author: GIWS


//function checking
if (!function_exists('srz_woo_method')) {

function srz_woo_method()
{
	/***
	extending woocomerce payment object
	***/
class WC_Gateway_SrzWoo_Gateway extends WC_Payment_Gateway {
	
		/**
		 * Constructor for the gateway.
		 */
		public function __construct() {
			$this->id                 = payment_id_srz;
			$this->icon               = apply_filters('woocommerce_offline_icon', '');
			$this->has_fields         = false;
			$this->method_title       = __( payment_name_srz, 'payfast-pro');
			$this->method_description = __( 'Take payments via '.payment_name_srz,'payfast-pro');
			// Load the settings.
			$this->init_form_fields();
			$this->init_settings(); 
			// Define user set variables
			$this->title        = $this->get_option( 'title' );
			$this->description  = $this->get_option( 'description' );
			$this->instructions = $this->get_option( 'instructions', $this->description );
			$this->srz_merchant_acc_key =$this->get_option('srz_merchant_acc_key');
			$this->api =$this->get_option('api');
			// Actions
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );  
			// Customer Emails
			//add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
			add_action('woocommerce_receipt_'.payment_id_srz, array($this, 'receipt_page'));

		}
		public function receipt_page($order)
		{
            echo '<p>' . __('Thank you for your order, please click the button below to pay with '.payment_name_srz.'.', 'payfast-pro') . '</p>';
           
           	echo $this->generate_form($order);
		} 
		public function generate_form($order_id)
		{
            global $woocommerce; 
            $order = new WC_Order($order_id);
            //main valiadtion page  
            $redirect_url = home_url(payment_slug_srz.'/gateway/v1/');
            //fail url
            $fail_url =  get_site_url();
            //redirect url after complete transection
            $redirect_url =  add_query_arg( array('order_id' => $order_id,'srzsecurity' => wp_create_nonce('wallet-nonce-srz')),$redirect_url );
            
            $declineURL = $order->get_cancel_order_url();
            $items = $woocommerce->cart->get_cart();
            #shipping method
            $shipping_method = @array_shift($order->get_shipping_methods());
            $shipping_method_id = $shipping_method['method_id'];
            if($shipping_method_id != "") {
                $shipping_enabled = "YES";
            } else {
                $shipping_enabled = "NO";
            }
            $product_title = array();
            foreach($items as $item => $values) 
            { 
                $_product =  wc_get_product( $values['data']->get_id()); 
                $product_title[] = $_product->get_title();
            } 
            $product_name = implode(",",$product_title);
            //ipn link 
            $IpnLink=payment_site_url_srz.'/payment/process';
            // API OF PayFast
            $post_data = array( 
                'access_key'  => $this->get_option('srz_merchant_acc_key'),
                'mcid'  => $this->get_option('srz_merchant_mcid'),
                'amount'  => $order->get_total(),
                'tran_id'       => $order_id,
                'success_url'   => $redirect_url,
                'cancel_url'      => $fail_url,
                'cancel_url'    => $declineURL,
                'ipn_url'       => $IpnLink, 
                'currency_code'      => get_woocommerce_currency(),
                'shipping_method'   => $shipping_enabled,
                'item_number'       => $woocommerce->cart->cart_contents_count,
                'details'      => $product_name,
            ); 

            { 
            	return '<form action="'.$IpnLink.'" method="POST">
            	<input type="hidden" name="access_key" value="'.$this->get_option('srz_merchant_acc_key').'">
            	<input type="hidden" name="mcid" value="'.$this->get_option('srz_merchant_mcid').'">
            	<input type="hidden" name="item_number" value="'. $woocommerce->cart->cart_contents_count.'">
            	<input type="hidden" name="details" value="'. $product_name.'">
            	<input type="hidden" name="amount" value="'.$order->get_total().'">
            	<input type="hidden" name="currency_code" value="'.get_woocommerce_currency().'">
            	<input type="hidden" name="custom" value="none">
            	<input type="hidden" name="site_url" value="'.$this->get_option('srz_merchant_site_url').'">
            	<input type="hidden" name="success_url" value="'.$redirect_url .'">
            	<input type="hidden" name="return_fail" value="'.$declineURL.'">
            	<input type="hidden" name="cancel_url" value="'.$declineURL.'">
				<input type="hidden" name="web_hook" value="'.$Url.'">
            	<input type="submit" class="button-alt" id="submit_srz_payment_form" value="' . __('Pay via '.payment_name_srz, 'payfast-pro') . '" />
            	<a class="button cancel" href="' . $order->get_cancel_order_url() . '">' . __('Cancel order &amp; restore cart', 'payfast-pro') . '</a>
            	<script type="text/javascript">
	                    jQuery(function(){
	                        jQuery("body").block({
	                            message: "' . __('Thank you for your order. We are now redirecting you to Payment Gateway to make payment.', 'payfast-pro') . '",
	                            overlayCSS: {
	                                background: "#fff",
	                                    opacity: 0.6
	                            },
	                            css: {
	                                padding:        20,
	                                textAlign:      "center",
	                                color:          "#555",
	                                border:         "3px solid #aaa",
	                                backgroundColor:"#fff",
	                                cursor:         "wait",
	                                lineHeight:"32px"
	                            }
	                        });
	                       jQuery("#submit_srz_payment_form").click();
	                    });
	                </script>
	                </form> ';
            }
		}
		/**
		 * Initialize Gateway Settings Form Fields
		 */
		public function init_form_fields() {
	  
			$this->form_fields = apply_filters( 'wc_'.payment_id_srz.'_form_fields', array(
				'enabled' => array(
					'title'   => __( 'Enable/Disable', 'payfast-pro'),
					'type'    => 'checkbox',
					'label'   => __( 'Enable '.payment_name_srz, 'payfast-pro'),
					'default' => 'no'
				),
				'title' => array(
					'title'       => __( 'Title', 'payfast-pro'),
					'type'        => 'text',
					'description' => __( 'Take payments vai '.payment_name_srz.'.', 'payfast-pro'),
					'default'     => __( payment_name_srz, 'payfast-pro'),
					'desc_tip'    => true,
				),
				'description' => array(
					'title'       => __( 'Description', 'payfast-pro'),
					'type'        => 'textarea',
					'description' => __( 'Payment method description that the customer will see on your checkout.', 'payfast-pro'),
					'default'     => __( 'Use your '.payment_name_srz.' account to pay!.', 'payfast-pro'),
					'desc_tip'    => true,
				),
				'instructions' => array(
					'title'       => __( 'Instructions', 'payfast-pro'),
					'type'        => 'textarea',
					'description' => __( 'Instructions that will be added to the thank you page and emails.', 'payfast-pro'),
					'default'     => '',
					'desc_tip'    => true,
				),
				'api' => array(
					'title'       => __( 'Api key', 'payfast-pro'),
					'type'        => 'text',
					'description' => __( 'Api from Access Key.', 'payfast-pro'),
					'default'     => '',
					'desc_tip'    => true,
				),
				'srz_merchant_acc_key' => array(
					'title'       => __( 'Access Key', 'payfast-pro'),
					'type'        => 'text',
					'description' => __( 'Access Key!', 'payfast-pro'),
					'default'     => '',
					'desc_tip'    => true,
				),
				'srz_merchant_site_url' => array(
					'title'       => __( 'Site URL', 'payfast-pro'),
					'type'        => 'text',
					'description' => __( 'Enter Your Site URL (Ex: https://www.domain.com/)', 'payfast-pro'),
					'default'     => '',
					'desc_tip'    => true,
				),
			) );
		}

		/**
		 * Payment fields on checkout field
		 */

		public function payment_fields()
		{

			if ( $this->description ) {
				if (empty($this->api) or $this->api ==='') {
				echo __('Setup '.payment_name_srz.' settings first!','payfast-pro');
				}elseif(empty($this->srz_merchant_acc_key) or $this->srz_merchant_acc_key ===''){
				echo __('Setup '.payment_name_srz.' settings first!','payfast-pro');
				}else{
				echo wpautop( wptexturize( $this->description ) );
				}
			} 
		}

		/**
		 * validation check
		 */
		public function validate_fields(){  
			return true;
		}

		/**
		 * Output for the order received page.
		 */
		public function thankyou_page() {
			if ( $this->instructions ) {
				if (empty($this->api) or $this->api ==='') {
				echo __('Setup '.payment_name_srz.' settings first!','payfast-pro');
				}elseif(empty($this->srz_merchant_acc_key) or $this->srz_merchant_acc_key ===''){
				echo __('Setup '.payment_name_srz.' settings first!','payfast-pro');
				}else{
				echo wpautop( wptexturize( $this->instructions ) );
				}
			}
		}
		/**
		 * Process the payment and return the result
		 *
		 * @param int $order_id
		 * @return array
		 */
		public function process_payment( $order_id ) {
			global $woocommerce;
			// we need it to get any order detailes
			$order = wc_get_order( $order_id );
			$error= false;
			if ($error){
			//wc_add_notice(  'I love Mimi<3.', 'error' );
			return;
			}else{
			 // Redirect to the thank you page
            return array('result' => 'success', 'redirect' => $order->get_checkout_payment_url(true));
			} 
		}
	
  } # end  class
}
} # fucntion check