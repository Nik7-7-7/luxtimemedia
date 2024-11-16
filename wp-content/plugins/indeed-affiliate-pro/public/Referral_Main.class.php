<?php
/*
 * Main Class for deal with referrals.
 * Extend this to each service.(Woo, UMP, etc.)
 */

if (!class_exists('Referral_Main')):

class Referral_Main{
	protected static $user_id;
	protected static $affiliate_id;
	protected static $source;
	protected static $campaign;
	protected static $visit_id = 0;
	protected static $currency;
	protected static $special_payment_type = '';
	protected static $coupon_code = '';

	public function __construct($user_id=0, $affiliate_id=0){
		/*
		 * @param int, int
		 * @return non
		 */
		if ($user_id){
			self::$user_id = $user_id;
		}
		if ($affiliate_id){
			self::$affiliate_id = $affiliate_id;
		}
		self::$affiliate_id = apply_filters( 'uap_filter_affiliate_id', self::$affiliate_id );

		self::$source = '';
		self::$campaign = '';
		self::$currency = get_option('uap_currency');
		if (!self::$currency){
			self::$currency = 'USD';
		}

		/// SIGN UP REFERRALS
		add_action( 'user_register', array($this, 'insert_signup_referral'), 99, 1 );

		/// PPC
		add_action('uap_insert_into_cookie_new_affiliate', [$this, 'ppc'], 1, 2);
	}

	protected function set_affiliate_id(){
		/*
		 * @param none
		 * @return none
		 */
		global $indeed_db;
		$lifetime = get_option('uap_lifetime_commissions_enable');
		$recurring = get_option('uap_reccuring_referrals_enable');
		self::$affiliate_id = apply_filters( 'uap_set_affiliate_id_filter', self::$affiliate_id );

		if (empty(self::$affiliate_id) && empty($_COOKIE['uap_referral'])){ /// SEARCH INTO DB
			if ($lifetime && (!isset( self::$special_payment_type ) || self::$special_payment_type != 'reccuring' ) ){ // adding && (!isset( self::$special_payment_type ) && self::$special_payment_type != 'reccuring' ) - since version 8.6
			 	/// LIFETIME
				self::$affiliate_id = $indeed_db->search_affiliate_id_for_current_user(self::$user_id);
				if (self::$affiliate_id){
					self::$special_payment_type = 'lifetime';
				}
			} else if ($recurring && self::$special_payment_type=='reccuring'){
				/// RECCURING
				self::$affiliate_id = $indeed_db->search_affiliate_id_for_current_user(self::$user_id);
			}

		} else if (empty(self::$affiliate_id) && !empty($_COOKIE['uap_referral'])){ /// SEARCH INTO COOKIE
			/// get affiliate id from cookie
			$cookie_data = json_decode( stripslashes($_COOKIE['uap_referral']), true );// since version 9.1
			if (!empty($cookie_data['affiliate_id'])){
				if ( get_option( 'uap_default_ref_format' ) == 'username' ){
						$temporaryAffiliateId = $indeed_db->get_affiliate_id_by_username( $cookie_data['affiliate_id'] );
				}
				if ( empty( $temporaryAffiliateId ) ){
						self::$affiliate_id = $cookie_data['affiliate_id'];
				}

				self::$campaign = (empty($cookie_data['campaign'])) ? '' : $cookie_data['campaign'];
				self::$visit_id = (empty($cookie_data['visit_id'])) ? 0 : $cookie_data['visit_id'];
			}
		}

		if (self::$affiliate_id){
			$old_affiliate = $indeed_db->search_affiliate_id_for_current_user(self::$user_id);
			if ($old_affiliate){
				$rewrite_referrals = get_option('uap_rewrite_referrals_enable');
				if ($rewrite_referrals){
					/// update user - affiliate relation, use new affiliate
					$indeed_db->update_affiliate_referral_user_relation_by_ids($old_affiliate, self::$affiliate_id, self::$user_id);
				} else {
					/// use old affiliate
					$lifetime = get_option('uap_lifetime_commissions_enable');
					if ( $lifetime ){
							self::$affiliate_id = $old_affiliate;
					}

				}
			} else {
				/// insert user - affiliate relation
				$indeed_db->insert_affiliate_referral_user_new_relation(self::$affiliate_id, self::$user_id);
			}
		}
	}

	protected function valid_referral(){
		/*
		 * @param none
		 * @return boolean
		 */
		global $indeed_db;
		/// CHECK FOR OWN REFERRENCE
		$isValid = apply_filters( 'uap_filter_before_valid_referral', true, self::$affiliate_id, self::$user_id );
		if ( !$isValid ){
				return false;
		}

		if (self::$affiliate_id && self::$user_id && $indeed_db->affiliate_get_id_by_uid(self::$user_id)==self::$affiliate_id){
			$allowOwnRefference = true;
			if (!get_option('uap_allow_own_referrence_enable')){
					$allowOwnRefference = false;//own referrence not allowed
			}
			$allowOwnRefference = apply_filters( 'uap_allow_own_referrence_filter', $allowOwnRefference );
			if ( !$allowOwnRefference ){
					return false;
			}
		}
		if (self::$affiliate_id && $indeed_db->is_affiliate_active(self::$affiliate_id)){
				return TRUE;
		}

		return FALSE;
	}

	public function save_referral_unverified($args=array()){ // protected
		/*
		 * UNVERIFIED STATUS
		 * @param array
		 * @return boolean
		 */
		global $indeed_db;
		$keys = array(
						'refferal_wp_uid',
						'campaign',
						'affiliate_id',
						'visit_id',
						'description',
						'source',
						'reference',
						'reference_details',
						'amount',
						'currency',
		);

		$args = apply_filters( 'uap_public_filter_insert_referral_args', $args );

		foreach ($keys as $key){
			if (!isset($args[$key])){
				return FALSE;
			}
		}

		/// NEGATIVE REFERRALS?
		if ($args['amount']<0){
			$args['amount'] = 0;
		}

		/// EMPTY REFERRALS
		$general_settings_data = $indeed_db->return_settings_from_wp_option('general-settings');
		if (empty($general_settings_data['uap_empty_referrals_enable'])){
			///don't insert referrals with 0$
			$min = 0.01;
			if ($args['amount']<$min){
				return;
			}
		}
		/// EMPTY REFERRALS

		// since version 8.6 - New Customer Commissions
		if ( ((int)get_option( 'uap_new_customer_commissions', 0 )) === 1 && isset( $args['refferal_wp_uid'] ) && ( (int)$args['refferal_wp_uid'] ) > 0 ){
				switch ( $args['source'] ){
						case 'woo':
							if ( function_exists( 'wc_get_orders') && \Indeed\Uap\Integrations::isWooActive() === 1 ){
									$args_order = [
										'customer_id' => $args['refferal_wp_uid'],
										'limit'       => -1, // retrieve all orders for this user
									];
									$orders = wc_get_orders( $args_order );
									if ( is_array( $orders ) && count( $orders ) > 1 ){
											return; // out, we already have orders from this user
									}
							}
							break;
						case 'edd':
							if ( function_exists( 'edd_get_payments') && \Indeed\Uap\Integrations::isEddActive() === 1 ){
									$orders = edd_get_payments( [ 'user' => $args['refferal_wp_uid'] ] );
									if ( is_array( $orders ) && count( $orders ) > 1 ){
											return; // out, we already have orders from this user
									}
							}
							break;
						case 'ump':
							if ( class_exists( '\Ihc_Db' ) && \Indeed\Uap\Integrations::isUmpActive() === 1 ){
									$orders = new \Indeed\Ihc\Db\Orders();
									$countOrders = $orders->countWithFilter( $args['refferal_wp_uid'] );
									if ( (int)$countOrders > 1 ){
											return; // out, we already have orders from this user
									}
							}
							break;
						case 'ulp':
							if ( class_exists( '\DbUlp' ) && \Indeed\Uap\Integrations::isUlpActive() === 1 ){
									$orders = \DbUlp::getOrdersByUser( $args['refferal_wp_uid'] );
									if ( is_array( $orders ) && count( $orders ) > 1 ){
											return; // out, we already have orders from this user
									}
							}
							break;
						default:
							$clientRegisterDate = $indeed_db->getRegisterDate( $args['refferal_wp_uid'] );
							$currentTime = indeed_get_unixtimestamp_with_timezone();
							$clientRegisterDateAsTimestamp = strtotime( $clientRegisterDate );
							if ( $clientRegisterDateAsTimestamp + 600 < $currentTime ){ // one hour
									return;
							}
							break;
				}
		}
		// end of 8.6 - New Customer Commissions

		$args['date'] = current_time( 'Y-m-d H:i:s' );//date('Y-m-d H:i:s', time());
		$args['status'] = 1;//unverified
		$args['payment'] = 0;//unpaid
		$args['parent_referral_id'] = '';// empty for moment, will be updated if it's case
		$args['child_referral_id'] = '';//always will be empty
		$referral_id = $indeed_db->save_referral($args);
		if ($referral_id){
			$indeed_db->update_visit_referral_id($args['visit_id'], $referral_id);
			if (get_option('uap_mlm_enable')){
				$limit = get_option('uap_mlm_matrix_depth');
				$first_child_username = $indeed_db->get_wp_username_by_affiliate_id($args['affiliate_id']);

				$theAmount = $args['amount'];
				$uap_mlm_use_amount_from = get_option('uap_mlm_use_amount_from');
				if ($uap_mlm_use_amount_from && $uap_mlm_use_amount_from=='product_price' && isset($args['product_price'])){
					$theAmount = $args['product_price'];
				}

				$this->mlm_do_save_referral_unverified($args['affiliate_id'], $referral_id, 1, $limit, $theAmount, $first_child_username, $referral_id);
			}
		}
		return TRUE;
	}

	protected function referral_verified($reference='', $source='', $check_if_can_do=TRUE){
		/*
		 * VERIFIED STATUS
		 * @param string, string
		 * @return none
		 */
		if ($check_if_can_do){
			/// Don't change the Referral Status to Verified
			$dont = get_option('uap_workflow_referral_status_dont_automatically_change');
			if ($dont){
				return; /// stop from change status of referral
			}
		}
		global $indeed_db;
		$referral_id = $indeed_db->get_referral_id_for_reference($reference, $source);
		if ($referral_id){
			$indeed_db->change_referral_status($referral_id, 2);
		}
	}

	protected function referral_refuse($reference='', $source=''){
		/*
		 * REFUSE STATUS
		 * @param string, string
		 * @return none
		 */
		global $indeed_db;
		$referral_id = $indeed_db->get_referral_id_for_reference($reference, $source);
		if ($referral_id){
			$indeed_db->change_referral_status($referral_id, 0);
		}
		if(isset($source) && ($source == 'woo' || $source == 'ump' || $source == 'edd' || $source == 'ulp')){
			$new_description = esc_html__(' - Order Refunded', 'uap');
			$indeed_db->change_referral_description($referral_id, $new_description, TRUE);
		}
	}

	protected function mlm_do_save_referral_unverified($child_affiliate_id=0, $child_referral_id=0, $count=1, $limit=0, $amount=0, $first_child_username='', $first_child_referrence=''){
		/*
		 * @param int, int, int, int, int, string, string
		 * @return none
		 */
		/// CHECK LIMIT DEPTH
		if ($limit<$count){
			return;
		}
		if ($child_affiliate_id && $child_referral_id){
			global $indeed_db;
			$parent_id = $indeed_db->mlm_get_parent($child_affiliate_id);
			$description = esc_html__('Generated based on the Main Referral', 'uap');
			if (!empty($first_child_username)){
				$description = esc_html__('Generated based on the Main Referral from ', 'uap').'<strong>'. $first_child_username.'</strong>';
			}
			$reference = '-';
			if (!empty($first_child_referrence)){
				$reference = 'mlm_' . $first_child_referrence;
			}

			if ($parent_id){
				$args = array(
						'refferal_wp_uid' => '-',
						'campaign' => '-',
						'affiliate_id' => $parent_id,
						'visit_id' => '-',
						'description' => $description,
						'source' => 'mlm',
						'reference' => $reference,
						'reference_details' => '-',
						'parent_referral_id' => '',//will be updated if it;s case
						'child_referral_id' => $child_referral_id,
				);
				$args['date'] = current_time( 'Y-m-d H:i:s' );//date('Y-m-d H:i:s', time());
				$args['status'] = 1;//unverified
				$args['payment'] = 0;//unpaid

				/// SET AMOUNT
				$args['amount'] = $indeed_db->mlm_get_amount($parent_id, $amount, $count);
				$args['currency'] = self::$currency;

				/// save referral
				$inserted_referral_id = $indeed_db->save_referral($args);

				//update the child referral
				$indeed_db->referral_update_child($child_referral_id, $inserted_referral_id);

				/// search for parent
				$count++;
				$this->mlm_do_save_referral_unverified($parent_id, $inserted_referral_id, $count, $limit, $amount, $first_child_username, $first_child_referrence);
			}
		}
	}

	public function insert_signup_referral($user_id=0){
		/*
		 * @param int
		 * @return none
		 */
		if (get_option('uap_sign_up_referrals_enable') && $user_id){
			self::$user_id = $user_id;
			$this->set_affiliate_id();
			if ($this->valid_referral()){
				require_once UAP_PATH . 'public/Affiliate_Referral_Amount.class.php';
				$do_math = new Affiliate_Referral_Amount(self::$affiliate_id, '');
				$user_data = get_userdata( $user_id);
				$description = '';
				if(isset($user_data->display_name) && !empty($user_data->display_name)){
					$description = '<strong>'.$user_data->display_name.'</strong> '.esc_html__('has Signed up', 'uap');
				}else{
					$description = esc_html__('New Sign up User', 'uap');
				}
				$amount = $do_math->get_signup_amount();
				$args = array(
						'refferal_wp_uid' => self::$user_id,
						'campaign' => self::$campaign,
						'affiliate_id' => self::$affiliate_id,
						'visit_id' => self::$visit_id,
						'description' => $description,
						'source' => 'User SignUp',
						'reference' => 'user_id_' . $user_id,
						'reference_details' => 'User SignUp',
						'amount' => $amount,
						'currency' => self::$currency,
				);
				$this->save_referral_unverified($args);
				$default_sts = get_option('uap_sign_up_default_referral_status');
				if ($default_sts==2){
					/// MAKE VERIFIED
					$this->referral_verified('user_id_' . $user_id, '', FALSE);
				}
			}
		}
	}

	public function pay_bonus($amount_value=0, $rank_name=''){
		/*
		 * @param double, string
		 * @return none
		 */
		global $indeed_db;
		$status = get_option('uap_bonus_on_rank_default_referral_sts');
		if ($status===FALSE){
			$status = 2; /// verified
		}
		$args = array(
				'refferal_wp_uid' => 0,
				'campaign' => '',
				'affiliate_id' => self::$affiliate_id,
				'visit_id' => '',
				'description' => esc_html__('Bonus for reaching rank: ', 'uap') .'<strong>'. $rank_name.'</strong>',
				'source' => 'bonus',
				'reference' => 0,
				'reference_details' => 'Bonus',
				'amount' => $amount_value,
				'currency' => self::$currency,
				'date' => current_time( 'Y-m-d H:i:s' ), //date('Y-m-d H:i:s', time()),
				'status' => $status,
				'payment' => 0,
				'parent_referral_id' => '',
				'child_referral_id' => '',
		);
		$indeed_db->save_referral($args);
	}

	public function ppc($affiliateId=0, $visitId=0)
	{
			global $indeed_db;
			if (empty($affiliateId)){
					return;
			}
			$isOn = get_option('uap_pay_per_click_enabled');
			if (empty($isOn)){
					return;
			}
			$referralStatus = get_option('uap_pay_per_click_default_referral_sts');

			self::$user_id = $indeed_db->get_uid_by_affiliate_id($affiliateId);
			self::$affiliate_id = $affiliateId;

			self::$visit_id = '';
			if(!empty($visitId)){
				self::$visit_id = $visitId;
			}

			$affiliateRank = $indeed_db->get_affiliate_rank(self::$affiliate_id);
			$amountValue = $indeed_db->getPPCValueForRank($affiliateRank);

			$args = array(
					'refferal_wp_uid' => 0,
					'campaign' => '',
					'affiliate_id' => self::$affiliate_id,
					'visit_id' => self::$visit_id,
					'description' => 'Pay Per Click',
					'source' => 'ppc',
					'reference' => 0,
					'reference_details' => 'ppc',
					'amount' => $amountValue,
					'currency' => self::$currency,
					'date' => current_time( 'Y-m-d H:i:s' ),//date('Y-m-d H:i:s', time()),
					'status' => $referralStatus,
					'payment' => 0,
					'parent_referral_id' => '',
					'child_referral_id' => '',
			);
			$referralId = $indeed_db->save_referral($args);

			// since version 8.4
			if ( $referralStatus == 2 && (int)$referralId > 0 && self::$visit_id > 0 ){ // make visits complete
					$indeed_db->updateVisitReferralId( self::$visit_id, $referralId );
			}
	}

}

endif;
