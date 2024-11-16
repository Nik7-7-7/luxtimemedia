<?php
if (!class_exists('AffiliateNotificationReports')):

class AffiliateNotificationReports{
	/**
	 * @var array
	 */
	private static $global_settings_single_notf = array();
	/**
	 * @var array
	 */
	private static $global_settings_reports = array();
	/**
	 * @var array
	 */
	private static $global_settings_admin_referral_notifications = array();
	private static $pushover_notifications = false;


	/**
	 * @param none
	 * @return none
	 */
	public function __construct(){
		global $indeed_db;
		if (empty(self::$global_settings_single_notf)){
			self::$global_settings_single_notf = $indeed_db->return_settings_from_wp_option('referral_notifications');
		}
		if (empty(self::$global_settings_admin_referral_notifications)){
			self::$global_settings_admin_referral_notifications = $indeed_db->return_settings_from_wp_option('admin_referral_notifications');
		}
		if (empty(self::$global_settings_reports)){
			self::$global_settings_reports = $indeed_db->return_settings_from_wp_option('periodically_reports');
		}
		if (empty(self::$pushover_notifications)){
			self::$pushover_notifications = $indeed_db->return_settings_from_wp_option('pushover_referral_notifications');
		}
	}

	public function report_constants(){
		/*
		 * @param none
		 * @return array
		 */
		 return array(
		 				'{visits}' => esc_html__('Visits', 'uap'),
	 					'{total_referrals}' => esc_html__('Total Referrals', 'uap'),
	 					'{total_earnings}' => esc_html__('Earnings', 'uap'),
	 					'{verified_referrals}' => esc_html__('Approved Referrals', 'uap'),
	 					'{unverified_referrals}' => esc_html__('Pending Referrals', 'uap'),
	 					'{refuse_referrals}' => esc_html__('Rejected Referrals', 'uap'),
		 );
	}

	public function notification_constants(){
		/*
		 * @param none
		 * @return array
		 */
		return array(
	 					'{referral_amount}' => esc_html__('Referral Amount', 'uap'),
	 					'{referral_source}' => esc_html__('Referral Source', 'uap'),
	 					'{referral_description}' => esc_html__('Referral Description', 'uap'),
	 					'{referral_reference}' => esc_html__('Referral Referece', 'uap'),
	 					'{referral_description}' => esc_html__('Referral Description', 'uap'),
	 					'{referral_date}' => esc_html__('Referral Date', 'uap'),
	 					'{referral_campaign}' => esc_html__('Referral Campaign', 'uap'),
	 					'{referral_status}' => esc_html__('Referral Status', 'uap'),
		);
	}

	public function report_referrals_message($affiliate_id=0, $user_email='', $interval=0){
		/*
		 * @param int
		 * @return string
		 */
		 if ($affiliate_id && $interval){
		 	global $indeed_db;
			$uid = $indeed_db->get_uid_by_affiliate_id($affiliate_id);

		 	$message = self::$global_settings_reports['uap_periodically_reports_content'];
			$subject = self::$global_settings_reports['uap_periodically_reports_subject'];

			/// SELECT REFERRALS by interval
			$end_time = date('Y-m-d', time());
			$start_time = time() - ($interval * 24 * 3600);
			$start_time = date('Y-m-d', $start_time);
			$referrals_data = $indeed_db->get_referral_report_by_date($affiliate_id, $start_time, $end_time);
			$constants = array(
		 				'{visits}' => $referrals_data['visits'],
	 					'{total_referrals}' => $referrals_data['total_referrals'],
	 					'{total_earnings}' => $referrals_data['total_earnings'],
	 					'{verified_referrals}' => $referrals_data['verified_referrals'],
	 					'{unverified_referrals}' => $referrals_data['unverified_referrals'],
	 					'{refuse_referrals}' => $referrals_data['refuse_referrals'],
			);

			/// REFERRAL CONSTANTS
			foreach ($constants as $key => $value){
				if (strpos($message, $key)!==FALSE){
					$message = str_replace($key, $value, $message);
				}
				if (strpos($subject, $key)!==FALSE){
					$subject = str_replace($key, $value, $subject);
				}
			}

			$message = uap_replace_constants($message, $uid);
			$subject = uap_replace_constants($subject, $uid);

		 	$sent = $this->send_email($uid, $message, $subject, $user_email);
			if ($sent){
				/// update time in db
				$indeed_db->update_affiliate_reports_last_sent($affiliate_id);
			}
		}
	}

	public function send_single_referral_notification($affiliate_id=0, $referral_id=0, $referral_type=''){
		/*
		 * @param int, int
		 * @return none
		 */
		 if ($affiliate_id && $referral_id){
		 	 global $indeed_db;

			 /// where to send
			 $send_to_affiliate = self::$global_settings_single_notf['uap_referral_notifications_enable'];
			 $send_to_admin = self::$global_settings_admin_referral_notifications['uap_admin_referral_notifications_enable'];

			 $uid = $indeed_db->get_uid_by_affiliate_id($affiliate_id);
			 /// CHECK REFERRAL TYPE
			 $affiliate_referral_type = get_user_meta($uid, 'uap_notifications_on_every_referral_types', TRUE); /// if this option is empty, means that affiliate wants to get notification on every referral
			 if ($affiliate_referral_type){
				 $types = explode(',', $affiliate_referral_type);
				 if ($types){
				 	if (!in_array($referral_type, $types)){
				 		/// AFFILIATE DON'T WANT NOTIFICATION FROM THIS KIND OF REFERRALS
				 		return;
				 	}
				 }
			 }

			/// MESSAGE & SUBJECT
			$message = '';
			$subject = '';
			$message_admin = '';
			$subject_admin = '';
			if ($send_to_affiliate){
			 	$message = self::$global_settings_single_notf['uap_referral_notification_content'];
				$subject = self::$global_settings_single_notf['uap_referral_notification_subject'];
			}
			if ($send_to_admin){
				$message_admin = self::$global_settings_admin_referral_notifications['uap_admin_referral_notification_content'];
				$subject_admin = self::$global_settings_admin_referral_notifications['uap_admin_referral_notification_subject'];
			}

			$referral_values = $indeed_db->get_referral($referral_id);
			$constants = array(
	 					'{referral_amount}' => $referral_values['amount'] . $referral_values['currency'],
	 					'{referral_source}' => uap_service_type_code_to_title($referral_values['source']),
	 					'{referral_description}' => $referral_values['description'],
	 					'{referral_reference}' => $referral_values['reference'],
	 					'{referral_description}' =>  $referral_values['description'],
	 					'{referral_date}' => $referral_values['date'],
	 					'{referral_campaign}' => $referral_values['campaign'],
	 					'{WOOCOMMERCE_ORDER_DETAILS}' => '',
			);
			switch ($referral_values['status']){
				case 0:
					$constants['{referral_status}'] = esc_html__('Rejected', 'uap');
					break;
				case 1:
					$constants['{referral_status}'] = esc_html__('Pending', 'uap');
					break;
				case 2:
					$constants['{referral_status}'] = esc_html__('Approved', 'uap');
					break;
			}
			/// {WOOCOMMERCE_ORDER_DETAILS}
			if ($referral_values['source']=='woo'){
				$constants['{WOOCOMMERCE_ORDER_DETAILS}'] = $this->getOrderDetails($referral_values['reference']);
			}

			/// REFERRAL CONSTANTS
			foreach ($constants as $key => $value){
				if (strpos($message, $key)!==FALSE){
					$message = str_replace($key, $value, $message);
				}
				if (strpos($message_admin, $key)!==FALSE){
					$message_admin = str_replace($key, $value, $message_admin);
				}
				if (strpos($subject, $key)!==FALSE){
					$subject = str_replace($key, $value, $subject);
				}
				if (strpos($subject_admin, $key)!==FALSE){
					$subject_admin = str_replace($key, $value, $subject_admin);
				}
			}

			$message = uap_replace_constants($message, $uid);
			$subject = uap_replace_constants($subject, $uid);
			$message_admin = uap_replace_constants($message_admin, $uid);
			$subject_admin = uap_replace_constants($subject_admin, $uid);

			/// notification to user
			if ($send_to_affiliate){
		 		$this->send_email($uid, $message, $subject);
				if (self::$pushover_notifications){
						$this->sendPushoverNotification($uid, $message, $subject);
				}
			}

			/// notification to admin
			if ($send_to_admin){
				$admin_email = get_option( 'uap_admin_notification_address', false );// >= 8.5
				if (empty($admin_email)){
					$admin_email = get_option('admin_email');//we change the destination
				}

		 		$this->send_email($uid, $message_admin, $subject_admin, $admin_email, TRUE);
				if (self::$pushover_notifications){
						$adminUid = $indeed_db->getUidByEmail($admin_email);
						$this->sendPushoverNotification($adminUid, $message_admin, $subject_admin);
				}
			}
		 }
	}

	private function send_email($uid=0, $message='', $subject='', $user_email='', $no_from_admin=FALSE){
		/*
		 * @param int, string, string, bool
		 * @return boolean
		 */
		 global $indeed_db;
		 $from_email = get_option('uap_notification_email_from');
		 if (empty($from_email)){
		 	if (empty($no_from_admin)){
				 $from_email = get_option('admin_email');
			}else{
				 $from_email = '';
			}
		 }
		 $from_name = get_option('uap_notification_name');
		 if (empty($from_name)){
		 	$from_name = get_option("blogname");
		 }
		 if (empty($user_email)){
			 $user_email = $indeed_db->get_email_by_uid($uid);
		 }
		 $message = stripslashes(htmlspecialchars_decode(uap_format_str_like_wp($message)));
		 $message = "<html><head></head><body>" . $message . "</body></html>";

		 if ($subject && $message && $user_email){
			$headers[] = "From: $from_name <$from_email>";
			$headers[] = 'Content-Type: text/html; charset=UTF-8';
			$sent = wp_mail($user_email, $subject, $message, $headers);

			// notification log ( v. >= 8.5 )
			if ( $sent ){
					$notificationType = $no_from_admin ? 'uap_admin_referral_notification' : 'uap_referral_notification';
					$log = [
						'notification_type'       => $notificationType,
						'email_address'           => $user_email,
						'message'                 => $message,
						'subject'									=> $subject,
						'uid'                     => $uid,
						'affiliate_id'            => $indeed_db->get_affiliate_id_by_wpuid($uid),
						'rank_id'                 => $indeed_db->get_affiliate_rank(false, $uid ),
					];
					\Indeed\Uap\Db\NotificationLogs::save( $log );
			}
			// notification log ( v. >= 8.5 )

			return $sent;
		}
		return FALSE;
	}


	/**
	 * @param int
	 * @return string
	 */
	private function getOrderDetails($woo_order_id=0){
		$return = '';
		if ($woo_order_id){
			global $indeed_db;
			$does_post_exists = $indeed_db->does_post_exists($woo_order_id);
			if (empty($does_post_exists)){
					return;
			}
			if (!class_exists('WC_Order')){
				return;
			}
			$woo = new WC_Order($woo_order_id);
			$string = array();
			$billing_email = $woo->get_billing_email();
			$billing_first_name = $woo->get_billing_first_name();
			$billing_last_name = $woo->get_billing_last_name();
			$order_date = $woo->get_date_created();
			$billing_phone = $woo->get_billing_phone();
			$order_total = $woo->get_formatted_order_total();
			$order_phone = $woo->get_billing_phone();
			$shipping_address = $woo->get_formatted_shipping_address();
			$billing_address = $woo->get_formatted_billing_address();

			$string[] = esc_html__('E-mail Address: ', 'uap') . $billing_email;
			$string[] = esc_html__('First name: ', 'uap') . $billing_first_name;
			$string[] = esc_html__('Last name: ', 'uap') . $billing_last_name;
			$string[] = esc_html__('Order date: ', 'uap') .  $order_date;
			$string[] = esc_html__('Order Amount: ', 'uap') . $order_total;
			$string[] = esc_html__('Phone: ', 'uap') . $billing_phone;
			$string[] = esc_html__('Shipping Address: ', 'uap') . '<div>' . $shipping_address . '</div>';
			$string[] = esc_html__('Billing Address: ', 'uap') . '<div>' . $billing_address . '</div>';
			$temp_arr = $woo->get_items();
			$cart_items = '';
			if ($temp_arr){
				foreach ($temp_arr as $item){
					$cart_items .= '<div>' . $item['name'] . ' * ' . $item['qty'] . '</div>';
				}
			}
			$string[] = esc_html__('Cart Items: ', 'uap') . $cart_items;
			foreach ($string as $str){
				$return .= '<div>' . $str . '</div>';
			}
		}
		return $return;
	}

	public function sendPushoverNotification($uid=0, $message='', $subject='')
	{
		$message = stripslashes(htmlspecialchars_decode($message));

		require_once UAP_PATH . 'classes/PushoverNotifications.class.php';
		$Uap_Pushover = new PushoverNotifications();
		$response = $Uap_Pushover->sendCustom($subject, $message, $uid);
	}

}

endif;
