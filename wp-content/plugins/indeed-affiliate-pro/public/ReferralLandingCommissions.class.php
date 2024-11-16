<?php
if (!class_exists('ReferralLandingCommissions') && class_exists('Referral_Main')) :

class ReferralLandingCommissions extends Referral_Main{

	public function __construct($slug='', $uid=0){
		/*
		 * @param string, int
		 * @return none
		 */
		 if ($slug){
		 	global $indeed_db;
			self::$user_id = $uid;
			$this->set_affiliate_id();
			$data = $indeed_db->get_landing_commission($slug);
			if (empty($data['id']) || empty($data['status']) || empty(self::$affiliate_id)){
				return;
			}

			/// <<< check cookie >>>
			$cookie_key = 'uaplandingcommission_' . $data['id'];
		 	if (!empty($_COOKIE[$cookie_key])){
				return;
		 	}
			/// <<< check cookie >>>

			if (!$this->valid_referral()){
				return;
			}

			$referrence = 'ref_' . $data['id'] . '_' . self::$user_id . '_' . self::$affiliate_id . '_' . time();
			$source = (empty($data['source'])) ? 'from landing commissions' : $data['source'];
			require_once UAP_PATH . 'public/Affiliate_Referral_Amount.class.php';
			$do_math = new Affiliate_Referral_Amount(self::$affiliate_id, $source, self::$special_payment_type);

			$amount_to_calculate = $data['amount_value'];
			if (isset($_REQUEST['lc_amount']) && is_numeric(uap_sanitize_textarea_array($_REQUEST['lc_amount']))){
				$amount_to_calculate = uap_sanitize_textarea_array($_REQUEST['lc_amount']);
			}

			$sum = $do_math->get_result($amount_to_calculate, '');// input price, product id

			$args = array(
						'refferal_wp_uid' => self::$user_id,
						'campaign' => self::$campaign,
						'affiliate_id' => self::$affiliate_id,
						'visit_id' => self::$visit_id,
						'description' => isset($data['description']) ? $data['description'] : '',
						'source' => $source,
						'reference' => $referrence,
						'reference_details' => '',
						'amount' => $sum,
						'currency' => self::$currency,
			);
			$this->save_referral_unverified($args);
			if ($data['default_referral_status']==2){
				$this->referral_verified($referrence, '', FALSE);
			}

			if (!isset($data['cookie_expire'])){ /// for older version
				$data['cookie_expire'] = 0;
			}

			$this->set_cookie($data['cookie_expire'], $data['id']);
		 }
	}

	private function set_cookie($expire = 0, $shortcode_id = 0){
		/*
		 * @param int (expire time)
		 * @return none (print some javscript)
		 */
		 if ($expire && $shortcode_id){
		 	?>
			<span class="uap-js-referral-landing-commisions-data" data-expire="<?php echo esc_attr($expire);?>" data-shortcode_id="<?php echo esc_attr($shortcode_id);?>"></span>
		 	<?php
			wp_enqueue_script( 'uap-referral-landing-commissions', UAP_URL . 'assets/js/referral-landing-commissions.js', ['jquery'], 8.3 );
		 }
	}

}

endif;
