<?php
if (!class_exists('Uap_Easy_Digital_Download')):

class Uap_Easy_Digital_Download extends Referral_Main{
	private $source_type = 'edd';
	private static $checkout_referrals_select_settings = array();

	public function __construct(){
		/*
		 * @param none
		 * @return none
		 */
		/// THE HOOKS
		if ( \Indeed\Uap\Integrations::isEddActive() === 0 ){
				return;
		}
		add_action('edd_insert_payment', array($this, 'create_referral'), 80, 2);
		add_action('edd_complete_purchase', array($this, 'make_referral_verified'), 80, 1);
		add_action('edd_payment_delete', array($this, 'make_referral_refuse'), 80,1);
		add_action( 'edd_refund_order', [ $this, 'order_become_refund' ], 80, 3 );

		/// CHECKOUT REFERRALS SELECT
		add_action('edd_purchase_form_before_submit', array($this, 'insert_affiliate_select'));
	}

	public function create_referral($order_id=0, $data=array()){
		/*
		 * @param int, array
		 * @return none
		 */

		/// set uid
		if (isset($data['user_info']) && !empty($data['user_info']['id'])){
			self::$user_id = $data['user_info']['id'];
		}

		if (empty(self::$affiliate_id)){
			/// let's check the coupon...
			$this->check_coupon($data['user_info']);
		}

		/// set affiliate id
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

			require_once UAP_PATH . 'public/Affiliate_Referral_Amount.class.php';
			$do_math = new Affiliate_Referral_Amount(self::$affiliate_id, $this->source_type, self::$special_payment_type, self::$coupon_code);

			if (!empty(self::$coupon_code)){
				$temp_coupon_data = $indeed_db->get_coupon_data(self::$coupon_code);
				if ($temp_coupon_data['amount_type']=='flat'){
					$run_foreach_line_once = TRUE;
				}
			}

			$products = $this->get_products_list($order_id);
			if ($products){
				$sum = 0;
				$product_price_sum = 0;
				foreach ($products as $key=>$product){
					$price = $product['price'];

					///exclude taxes
					if (!empty($product['tax']) && $exclude_tax){
						$price = $price - $product['tax'];
					}

					/// exclude shipping
					if (!empty($product['shipping']) && !$exclude_shipping){
						$price += $product['shipping'];
					}

					$product_price_sum += $price;

					$sum += $do_math->get_result($price, $key);

					if (!empty($run_foreach_line_once)){
						/// user for coupon flat amount!
						break;
					}

				}
			} else {
				$sum = $do_math->get_result($data['price'], '');
			}

			$eddCurrency = '';
		  $eddPrice = '';

			if(function_exists('edd_get_order')){
	 		   $orderDefails = edd_get_order($order_id);
	 		   $eddCurrency = $orderDefails->currency;
	 		   $eddPrice = $orderDefails->total;
 		 	}else{
	 		   $orderDefails = get_post_meta( $order_id, '_edd_payment_meta' );
	 		   $eddCurrency = isset( $orderDefails[0]['currency'] ) ? $orderDefails[0]['currency'] : '';
	 		   $eddPrice = isset( $orderDefails[0]['cart_details'][0]['price'] ) ? $orderDefails[0]['cart_details'][0]['price']  : '';
 		 	}


			$sum = apply_filters( 'uap_public_filter_on_referral_insert_amount_value', $sum, $eddCurrency );

			$description = esc_html__('Download Sale', 'uap');
			if(isset($eddPrice) && !empty($eddPrice)){
				$description = esc_html__('Order Amount: ', 'uap').'<strong>'.edd_currency_filter( edd_format_amount($eddPrice) ).'</strong>';
			}

			$args = array(
					'refferal_wp_uid' => $data['user_info']['id'],
					'campaign' => self::$campaign,
					'affiliate_id' => self::$affiliate_id,
					'visit_id' => self::$visit_id,
					'description' => $description,
					'source' => $this->source_type,
					'reference' => $order_id,
					'reference_details' => '',
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
			$this->referral_refuse($order_id, $this->source_type);
		}
	}


	public function order_become_refund($order_id=0)
	{
			// since version 8.6
			$makeRefuseOnRefunded = get_option( 'uap_reject_refund_referrals', 1 );
			if ( ((int)$makeRefuseOnRefunded) === 0 ){
				return;
			}
			// end of version 8.6

			return $this->make_referral_refuse($order_id);
	}

	public function get_products_list($id=0){
		/*
		 * @param int
		 * @return array
		 */
		$array = array();
		if ($id){
			$products = edd_get_payment_meta_cart_details($id);
			if ($products && is_array($products)){
				foreach ($products as $k=>$v){
					$array[$v['id']]['price'] = $v['price'];
					$array[$v['id']]['name'] = get_the_title($v['id']);
					if(isset($array[$v['id']]['tax'] ) && isset($v['tax'])){
						$array[$v['id']]['tax'] = $v['tax'];
					}
					$array[$v['id']]['shipping'] = 0;
					if (!empty($v['fees']) && !empty($v['fees']['shipping']) && !empty($v['fees']['shipping']['amount'])){
						if(isset($array[$v['id']]['shipping'] ) && isset($v['fees']['shipping']['amount'])){
							$array[$v['id']]['shipping'] = $v['fees']['shipping']['amount'];
						}
					}
				}
			}
		}
		return $array;
	}

	private function check_coupon($data=array()){
		/*
		 * @param array
		 * @return none
		 */
		 if ($data && !empty($data['discount']) && $data['discount']!='none'){
		 	global $indeed_db;

			/// AFFILIATE BY COUPON
	 		$affiliate = $indeed_db->get_affiliate_for_coupon_code($data['discount']);
			if ($affiliate){
				self::$affiliate_id = $affiliate;
				self::$special_payment_type = 'coupon';
				self::$coupon_code = $data['discount'];
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
			$data['require'] = '<span class="edd-required-indicator">*</span>';
			$data['class'] = '';
			$data['select_class'] = 'edd-select';
			$data['input_class'] = 'edd-input';
			if ($data['require']){
				$data['select_class'] .= ' required';
				$data['input_class'] .= ' required';
			}
			$data['require_on_input'] = 'required';
			require_once UAP_PATH . 'public/views/checkout_referral_select.php';
		 }
	}

}

endif;
