<?php
if (!class_exists('Uap_Woo')) :

class Uap_Woo extends Referral_Main{
	private $source_type = 'woo';
	private static $checkout_referrals_select_settings = array();
	protected static $coupon_code = '';

	public function __construct(){
		/*
		 * @param none
		 * @return none
		 */
		/// THE HOOKS

		if ( \Indeed\Uap\Integrations::isWooActive() === 0 ){
				return;
		}

		//add_action('woocommerce_checkout_order_processed', array($this, 'create_referral')); /// <8.4
		// version >=8.4
		add_action('woocommerce_checkout_update_order_meta', array($this, 'create_referral'));

		/*
		* Fires before an order is processed by the Checkout Block/Store API.
		* This is similar to existing core hook woocommerce_checkout_order_processed.
		*/
		add_action('woocommerce_store_api_checkout_order_processed', array($this, 'create_referral_for_blocks'));

		add_action('woocommerce_order_status_completed', array($this, 'make_referral_verified'));
		add_action('wc-on-hold_to_trash', array($this, 'make_referral_refuse'));
		add_action('wc-processing_to_trash', array($this, 'make_referral_refuse'));
		add_action('wc-completed_to_trash', array($this, 'make_referral_refuse'));

		add_action( 'woocommerce_order_status_on-hold_to_processing', [$this, 'make_referral_refuse'] );

		add_action( 'woocommerce_order_status_changed', [ $this, 'change_status' ], 999, 4 );

		/// CHECKOUT REFERRALS SELECT
		add_action('woocommerce_after_order_notes', array($this, 'insert_affiliate_select'));
		add_action('woocommerce_checkout_process', array( $this, 'checking_affiliate_select'));

		//// SUBSCRIPTION
		add_action( 'init', array( $this, 'registerSubscriptionHooks' ) );
		/*
		add_action('woocommerce_customer_changed_subscription_to_active', array($this, 'make_wcs_referral_verified'), 999, 2);
		add_action('woocommerce_customer_changed_subscription_to_cancelled', array($this, 'make_wcs_referral_refuse'), 999, 2);
		*/
		add_filter('wcs_new_order_created', array($this, 'create_referral_and_return_renewal_order'), 999, 3);
	}

	public function registerSubscriptionHooks()
	{
			if ( !class_exists( 'WC_Subscriptions' ) || !isset(\WC_Subscriptions::$version) ){
					return;
			}

			if ( version_compare( \WC_Subscriptions::$version, '2.0', '<=') ){
					// >= 2.0
					add_action('woocommerce_customer_changed_subscription_to_active', array($this, 'make_wcs_referral_verified'), 999, 2);
					add_action('woocommerce_customer_changed_subscription_to_cancelled', array($this, 'make_wcs_referral_refuse'), 999, 2);
			} else {
					add_action('woocommerce_customer_changed_subscription_to_active', array($this, 'make_wcs_new_version_referral_verified'), 999, 1 );
					add_action('woocommerce_customer_changed_subscription_to_cancelled', array($this, 'make_wcs_new_version_referral_refuse'), 999, 1 );
			}

	}


	public function create_referral($order_id=0){
		/*
		 * @param int (order id)
		 * @return none
		 */

		if (empty($order_id)){
			return; // out
		}
		if (is_object($order_id)){
			$order_id = (isset($order_id->ID)) ? $order_id->ID : 0;
		}

		$order = new WC_Order($order_id);
		self::$user_id = (int)$order->get_user_id();

		if (empty(self::$affiliate_id)){
			/// let's check the coupon...
			$this->check_coupon($order);
		}

		$this->set_affiliate_id();

		///CHECKOUT REFERRAL SELECT
		$this->check_for_selected_affiliate();
		///CHECKOUT REFERRAL SELECT

		if ($this->valid_referral()){
			// it's valid

			/// tax & shipping settings
			global $indeed_db;
			$temp_data = $indeed_db->return_settings_from_wp_option('general-settings');
			$exclude_shipping = (empty($temp_data['uap_exclude_shipping'])) ? FALSE : TRUE;
			$exclude_tax = (empty($temp_data['uap_exclude_tax'])) ? FALSE : TRUE;

			/// calculate the amount object
			require_once UAP_PATH . 'public/Affiliate_Referral_Amount.class.php';
			$do_math = new Affiliate_Referral_Amount(self::$affiliate_id, $this->source_type, self::$special_payment_type, self::$coupon_code);

			if (!empty(self::$coupon_code)){
				$temp_coupon_data = $indeed_db->get_coupon_data(self::$coupon_code);
				if ($temp_coupon_data['amount_type']=='flat'){
					$run_foreach_line_once = TRUE;
				}
			}

			$items = $order->get_items();

			$shipping = $order->get_total_shipping();
			if ($shipping){
				$shipping_per_item = $shipping / count($items);
			} else {
				$shipping_per_item = 0;
			}
			$sum = 0;
			$product_price_sum = 0;
			$products_arr = [];
			foreach ($items as $item){ /// foreach in lines

				$variableProductId = isset( $item['variation_id'] ) ? $item['variation_id'] : false;

				// since version 8.6
				// excluded cat
				if ( $this->productCatIsExcluded( $item['product_id'] ) ){
						continue;
				}
				// excluded product
				if ( $this->isProductExcluded( $item['product_id'] ) ){
						continue;
				}
				// excluded variable product
				if ( $variableProductId !== false && $this->isVariableProductExcluded( $variableProductId ) ){
						continue;
				}
				// end of 8.6

				$products_arr[] = $item['product_id'];

				///base price
				$product_price = round($item['line_total'], 3);

				///add shipping if it's case
				if (!empty($shipping_per_item) && !$exclude_shipping){
					$product_price += round($shipping_per_item, 3);
				}

				/// add taxes if it's case
				if (!empty($item['line_tax']) && !$exclude_tax){
					$product_price += round($item['line_tax'], 3);
				}

				$product_price_sum += $product_price;

				/// get amount
				$do_math->setVariableProductId( $variableProductId );
				$temp_amount = $do_math->get_result( $product_price, $item['product_id'] );// input price, product id

				$sum += $temp_amount;

				if (!empty($run_foreach_line_once)){
					/// user for coupon flat amount!
					break;
				}
			}
			if (!empty($products_arr)){
				$product_list = implode(',', $products_arr);
			} else {
				$product_list = '';
			}

			// since version 8.6
			if ( $product_list === '' ){
					// no product -> out
					return false;
			}
			// end of since version 8.6

			$wooCurrency = $order->get_currency();
			$wooTotal = $order->get_total();
			if ( class_exists( 'WOOCS' ) && method_exists( $order, 'get_order_currency' ) ) {
					$wooCurrency = $order->get_order_currency();
			    global $WOOCS;
			    if ( isset( $WOOCS->default_currency ) && $wooCurrency != $WOOCS->default_currency ) {
			        $currencies = $WOOCS->get_currencies();
			        $sum = $WOOCS->back_convert($sum, $currencies[$wooCurrency]['rate'] );
			    }
			}
			$sum = apply_filters( 'uap_public_filter_on_referral_insert_amount_value', $sum, $wooCurrency );

			$description = esc_html__('WooCommerce Sale', 'uap');
			if(isset($wooTotal) && !empty($wooTotal)){
				$description = esc_html__('Order Amount: ', 'uap').'<strong>'.wc_price( $wooTotal ).'</strong>';
			}
			$args = array(
							'refferal_wp_uid' => self::$user_id,
							'campaign' => self::$campaign,
							'affiliate_id' => self::$affiliate_id,
							'visit_id' => self::$visit_id,
							'description' => $description,
							'source' => $this->source_type,
							'reference' => $order_id,
							'reference_details' => $product_list,
							'amount' => $sum,
							'currency' => self::$currency,
							'product_price' => $product_price_sum,
			);
			$this->save_referral_unverified($args);

		}
	}

	public function make_referral_verified($order_id=0){
		/*
		 * @param int
		 * @return none
		 */
		if ($order_id){
			$this->referral_verified($order_id, $this->source_type);
		}
	}

	public function make_referral_refuse($order_id=0){
		/*
		 * @param int
		 * @return none
		 */
		if ($order_id){
			if (is_object($order_id)){
				$order_id = (isset($order_id->ID)) ? $order_id->ID : 0;
			}
			$this->referral_refuse($order_id, $this->source_type);
		}
	}

	private function check_coupon($order_object){
		/*
		 * check if coupon has a affiliate on it
		 * @param object
		 * @return none
		 */
		 if ($order_object){

			 $coupons_arr = $order_object->get_coupon_codes();
			 if (!empty($coupons_arr)){
			 	global $indeed_db;
			 	foreach ($coupons_arr as $coupon){
			 		$affiliate = $indeed_db->get_affiliate_for_coupon_code($coupon);
					if ($affiliate){
						self::$affiliate_id = $affiliate;
						self::$special_payment_type = 'coupon';
						self::$coupon_code = $coupon;
					}
			 	}
			 }
		 }
	}

	//////////////// CHECKOUT REFERRAL SELECT

	public function check_for_selected_affiliate(){
		/*
		 * @param none
		 * @return none
		 */
		 global $indeed_db;
		 if (empty(self::$checkout_referrals_select_settings)){
		 	self::$checkout_referrals_select_settings = $indeed_db->return_settings_from_wp_option('checkout_select_referral');
		 }
		 if (self::$checkout_referrals_select_settings['uap_checkout_select_referral_enable']){
		 	if (!empty($_POST['uap_affiliate_username'])){
		 		self::$affiliate_id = sanitize_text_field($_POST['uap_affiliate_username']);
		 	} else if (!empty($_POST['uap_affiliate_username_text'])){
				$_POST['uap_affiliate_username_text'] = sanitize_text_field( $_POST['uap_affiliate_username_text'] );
		 		$temp = $indeed_db->get_affiliate_id_by_username($_POST['uap_affiliate_username_text']);
				if ($temp){
					self::$affiliate_id = $temp;
				}
		 	}
		 }
	}

	public function insert_affiliate_select(){
		/*
		 * @param none
		 * @return none
		 */
		 global $indeed_db;
		 if (empty(self::$checkout_referrals_select_settings)){
		 	self::$checkout_referrals_select_settings = $settings = $indeed_db->return_settings_from_wp_option('checkout_select_referral');
		 }
		 /// check it's enable
		 if (self::$checkout_referrals_select_settings['uap_checkout_select_referral_enable']){
		 	$this->set_affiliate_id();
		 	if (self::$affiliate_id && !self::$checkout_referrals_select_settings['uap_checkout_select_referral_rewrite']){
		 		return; /// OUT
		 	}
			$who = self::$checkout_referrals_select_settings['uap_checkout_select_affiliate_list'];
			$type = self::$checkout_referrals_select_settings['uap_checkout_select_referral_name'];
			$data['affiliates'] = $indeed_db->get_affiliates_for_checkout_select($who, $type);
			$data['require'] = (self::$checkout_referrals_select_settings['uap_checkout_select_referral_require']) ? '<abbr class="required" title="required">*</abbr>' : '';
			$data['class'] = 'form-row form-row';
			if ($data['require']){
				$data['class'] .= ' validate-required';
			}
			$data['select_class'] = '';
			$data['input_class'] = '';
			$data['require_on_input'] = '';
			require_once UAP_PATH . 'public/views/checkout_referral_select.php';
		 }
	}

	public function checking_affiliate_select(){
		/*
		 * @param none
		 * @return none
		 */
		 global $indeed_db;
		 if (empty(self::$checkout_referrals_select_settings)){
		 	self::$checkout_referrals_select_settings = $indeed_db->return_settings_from_wp_option('checkout_select_referral');
		 }
		 if (self::$checkout_referrals_select_settings['uap_checkout_select_referral_enable'] && self::$checkout_referrals_select_settings['uap_checkout_select_referral_require']){
		 	if (isset($_POST['uap_affiliate_username']) && $_POST['uap_affiliate_username']==''){
		 		$error = TRUE;
		 	} else if (isset($_POST['uap_affiliate_username_text']) && $_POST['uap_affiliate_username_text']==''){
		 		$error = TRUE;
		 	}
			 if (!empty($error)){
				 wc_add_notice(__('Please complete all required fields!', 'uap'), 'error');
			 }
		 }
	}

	//// Woo Blocks
	public function create_referral_for_blocks($new_order){
		if (isset($new_order->id)){
			$this->create_referral($new_order->get_id());
		}
	}

	//// WCS
	public function create_referral_and_return_renewal_order($new_order, $subscription, $type){
		if (isset($new_order->id)){
			$this->create_referral($new_order->id);
		}
		return $new_order;
	}

	public function make_wcs_referral_verified($new_status, $subscription){
		$this->make_referral_verified($subscription);
	}

	public function make_wcs_referral_refuse($new_status, $subscription){
		$this->make_referral_refuse($subscription);
	}

	public function make_wcs_new_version_referral_verified( $subscription = null )
	{
		$this->make_referral_verified($subscription);
	}

	public function make_wcs_new_version_referral_refuse( $subscription = null )
	{
		$this->make_referral_refuse($subscription);
	}

	/*
	 * 0 - REFUSE
	 * 1 - UNVERIFIED
	 * 2 - VERIFIED
	 */
	 public function change_status( $order_id=0, $status_transition_from = '', $status_transition_to = '', $that = '' )
	 {
		 	global $indeed_db;
	 		$referral_id = $indeed_db->get_referral_id_for_reference( $order_id, $this->source_type );
			switch ( $status_transition_to ){
					case 'cancelled':
						$status = 0;
						break;
					case 'on-hold':
						$status = 1;
						break;
					case 'pending':
						$status = 1;
						break;
					case 'processing':
						$status = 1;
						break;
					case 'refunded':
						// since version 8.6
						$makeRefuseOnRefunded = get_option( 'uap_reject_refund_referrals', 1 );
						if ( ((int)$makeRefuseOnRefunded) === 0 ){
								return;
						}
						// end of 8.6
						$status = 0;
						break;
					case 'failed':
						$status = 0;
						break;
			}
	 		if ( $referral_id && isset( $status ) ){
	 				$indeed_db->change_referral_status( $referral_id, $status );
	 		}
			if( $referral_id && isset( $status ) && $status == 0){
				$new_description = esc_html__(' - Order Refunded', 'uap');
				$indeed_db->change_referral_description($referral_id, $new_description, TRUE);
			}
	 }

	 /**
	  * since version 8.6 .
	  * @param int
		* @return bool
		*/
	 private function productCatIsExcluded( $productId=0 )
	 {
		 	 if ( $productId === 0 ){
				 	return false;
			 }
			 $terms = wc_get_product_term_ids( $productId, 'product_cat' );
			 if ( !$terms ){
			     return false;
			 }
			 foreach ( $terms as $termId ){
			     $maybeHide = get_term_meta( $termId, 'uap_excluded', true );
					 if ( (int)$maybeHide === 1 ){
						 	return true;
					 }
			 }
			 return false;
	 }

	 /**
	  * since version 8.6 .
	  * @param int
		* @return bool
		*/
	 private function isProductExcluded( $productId=0 )
	 {
			 if ( $productId === 0 ){
						return false;
			 }
			 $maybeHide = get_post_meta( $productId, 'uap-woo-excluded-prod', true );
			 if ( (int)$maybeHide === 1 ){
				 	return true;
			 }
			 return false;
	 }

	 /**
		* since version 8.6 .
		* @param int
		* @return bool
		*/
	 private function isVariableProductExcluded( $productId=0 )
	 {
			 if ( $productId === 0 ){
						return false;
			 }
			 $maybeHide = get_post_meta( $productId, 'uap-woo-excluded-variable_prod', true );
			 if ( (int)$maybeHide === 1 ){
					return true;
			 }
			 return false;
	 }

}

endif;
