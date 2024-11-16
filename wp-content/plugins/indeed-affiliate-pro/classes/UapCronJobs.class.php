<?php
if (!class_exists('UapCronJobs')) :

class UapCronJobs{

	/**
	 * @param none
	 * @return none
	 */
	public function __construct()
	{
			add_action( 'uap_cron_job', array($this, 'update_affiliates_rank') );
			add_action( 'uap_cron_job_payments', array($this, 'update_affiliates_payments_status') );
			add_action( 'uap_cron_error_notifications', array($this, 'send_email_notification_for_slug_username_errors') );
			add_action( 'uap_cron_send_reports_to_affiliate', array($this, 'send_reports_to_affiliate') );
	  	add_action( 'uap_cron_delete_unverified_affiliates', array($this, 'do_delete_unverified_affiliates') );
			add_action( 'uapDoRanksReset', array( $this, 'doResetRanksAction' ) );
			add_action( 'uap_cron_job_admin_weely_reports', [ $this, 'weeklyReports' ] );
	}

	public function registerCrons()
	{
			/////////// RANKS
			$repeat = get_option('uap_update_ranks_interval');
			if (empty($repeat)){
				$repeat = 'daily';
			}
			$schedule = wp_next_scheduled('uap_cron_job');
			if (empty($schedule)){
				//create cron
				wp_schedule_event( time(), $repeat, 'uap_cron_job');//modify time
			}

			///////// PAYMENTS
			$repeat = get_option('uap_update_payments_status');
			if (empty($repeat)){
				$repeat = 'daily';
			}
			$schedule = wp_next_scheduled('uap_cron_job_payments');
			if (empty($schedule)){
				//create cron
				wp_schedule_event( time(), $repeat, 'uap_cron_job_payments');//modify time
			}

			///////// RANKS
			$repeat = 'daily';
			$schedule = wp_next_scheduled('uap_cron_error_notifications');
			if (empty($schedule)){
					//create cron
					wp_schedule_event( time(), $repeat, 'uap_cron_error_notifications');//modify time
			}

			/// REPORTS
			$repeat = 'daily';
			$schedule = wp_next_scheduled('uap_cron_send_reports_to_affiliate');
			if (empty($schedule)){
				//create cron
				$middlenight = strtotime(date('m/d/Y', time()));
				wp_schedule_event($middlenight, $repeat, 'uap_cron_send_reports_to_affiliate');//modify time
			}

			/// DELETE USERS E-MAIL NOT VERIFY
			$repeat = 'daily';
			$schedule = wp_next_scheduled('uap_cron_delete_unverified_affiliates');
			if (empty($schedule)){
				//create cron
				wp_schedule_event(time(), $repeat, 'uap_cron_delete_unverified_affiliates');//modify time
			}
	}

	public function update_affiliates_rank(){
		/*
		 * @param none
		 * @return none
		 */
		require_once UAP_PATH . 'public/ChangeRanks.class.php';
		$object = new ChangeRanks();
	}

	public function update_affiliates_payments_status(){
		/*
		 * @param none
		 * @return none
		 */
		global $indeed_db;
		$indeed_db->update_paypal_transactions();
	}

	public function update_cron_time($new=''){
		/*
		 * @param string
		 * @return none
		 */
		wp_clear_scheduled_hook('uap_cron_job');
		wp_schedule_event( time(), $new, 'uap_cron_job');
	}


	public function send_email_notification_for_slug_username_errors(){
		/*
		 * @param none
		 * @return none
		 */
		global $indeed_db;
		$data = $indeed_db->select_all_same_slugs_with_usernames();
		if ($data){
			$output = '';
			foreach ($data as $arr){
				$owner_of_username = $indeed_db->get_username_by_wpuid($arr['user']);
				$owner_of_slug = $indeed_db->get_username_by_wpuid($arr['slug']);
				$output .= esc_html__('User ', 'uap') . $owner_of_slug . esc_html__(' has a custom slug that match with nickname of ', 'uap') . $owner_of_username . '. <br/>';
			}
			if ($output){
				$output = esc_html__('We inform You that: ', 'uap') . '<br/>' . $output . '<br/>' . esc_html__('This could cause some errors into Ultimate Affiliate Pro.', 'uap');

				$admin_email = get_option( 'uap_admin_notification_address', false );// >= 8.5
				if (empty($admin_email)){
					$admin_email = get_option('admin_email');//we change the destination
				}

				$output = "<html><head></head><body>" . $output . "</body></html>";
				$subject = esc_html__('Hello', 'uap');
				$from_email = get_option('uap_notification_email_from');
				if (!$from_email){
					$from_email = get_option('admin_email');
				}
				$from_name = get_option('uap_notification_name');
				if (empty($from_name)){
					$from_name = get_option("blogname");
				}
				$headers[] = "From: $from_name <$from_email>";
				$headers[] = 'Content-Type: text/html; charset=UTF-8';
				if ($admin_email){
					$sent = wp_mail($admin_email, $subject, $output, $headers);

					if ( $sent ){
							// notification log ( v. >= 8.5 )
							$log = [
								'notification_type'       => 'slug_username_errors',
								'email_address'           => $admin_email,
								'message'                 => $output,
								'subject'									=> $subject,
								'uid'                     => 0,
								'affiliate_id'            => 0,
								'rank_id'                 => 0,
							];
							\Indeed\Uap\Db\NotificationLogs::save( $log );
							// notification log ( v. >= 8.5 )
					}

				}
			}
		}
	}

	public function send_reports_to_affiliate(){
		/*
		 * @param none
		 * @return none
		 */
		 global $indeed_db;
		 if (!get_option('uap_periodically_reports_enable')){
			/// DISABLED BY ADMIN
		  	return;
		 }
		 if (!class_exists('AffiliateNotificationReports')){
		 	 require_once UAP_PATH . 'classes/AffiliateNotificationReports.class.php';
		 }
		 $object = new AffiliateNotificationReports();
		 $data = $indeed_db->get_affiliates_for_reports();
		 if ($data){
		 	foreach ($data as $array){
				$object->report_referrals_message($array['affiliate_id'], $array['email'], $array['period']);
			}
		 }
	}

	public function do_delete_unverified_affiliates(){
		/*
		 * @param none
		 * @return none
		 */
		 global $wpdb, $indeed_db;
		 $settings = $indeed_db->return_settings_from_wp_option('email_verification');
		 if ($settings['uap_register_double_email_verification'] && (int)$settings['uap_double_email_delete_user_not_verified']>-1){
		 	 $time_limit = (int)$settings['uap_double_email_delete_user_not_verified'];
			 $time_limit = $time_limit * 24 * 60 * 60;
			 $table = $wpdb->base_prefix . "usermeta";
			 $query = "SELECT user_id FROM $table	WHERE meta_key='uap_verification_status' AND meta_value='-1';";
			 $data = $wpdb->get_results( $query );
			 if ($data){
				foreach ($data as $k=>$v){
					if (!empty($v->user_id)){
						$query = $wpdb->prepare( "SELECT user_registered FROM {$wpdb->base_prefix}users WHERE ID=%d;", $v->user_id );
						$time_data = $wpdb->get_row( $query );
						if (!empty($time_data->user_registered)){
							$time_to_delete = strtotime($time_data->user_registered) + $time_limit;
							if ( $time_to_delete < time() ){
								$affiliate_id = $indeed_db->get_affiliate_id_by_wpuid($v->user_id);
								$indeed_db->delete_affiliates($affiliate_id);
							}
						}
					}
				}
			 }
		 }
	}

	public function doResetRanksAction()
	{
			if ( get_option( 'uap_ranks_pro_enabled', 0 ) ){
					$object = new \Indeed\Uap\ResetRanks();
					$object->doAction()->doSchedule();
			}
	}

	/**
	 * Used in weekly reports notifications.
	 * @param none
	 * @return none
	 */
	public function weeklyReports()
	{
			global $indeed_db;
			if ( (int)get_option( 'uap_wes_enabled' ) === 0 ){
					return ;
			}
			$subject = esc_html__( '[Ultimate Affiliates Pro Your summary report for last week', 'uap') ." ". get_site_url();

			$start = time() - 7 * 24 * 60 * 60;// 7days
			$end = time();
			$dataForLastWeek 			= $indeed_db->get_stats_for_reports( 'last_week');
			$currency 						= uapCurrency();
			$newAffiliates 				= $dataForLastWeek['affiliates'];
			$clicks 							= $dataForLastWeek['visits'];
			$earnings 						= $dataForLastWeek['total_amount_referrals'];
			$referrals 						= $dataForLastWeek['referrals'];
			$conversionRate 			= $dataForLastWeek['conversions'];

			$message = "<p>" . esc_html__( 'Here is the summary report for the last week:', 'uap' ) . "</p>" .
									"<p><strong>" . esc_html__( 'New registered Affiliates:', 'uap' ) . "</strong> " . $newAffiliates . "</p>
									<p><strong>" . esc_html__( 'Total Clicks:', 'uap' ) . "</strong> " . $clicks . "</p>
									<p><strong>" . esc_html__( 'Total Earnings:', 'uap' ) . "</strong> " . $earnings . "</p>
									<p><strong>" . esc_html__( 'Total Referrals:', 'uap' ) . "</strong> " . $referrals . "</p>
									<p><strong>" . esc_html__( 'Conversion Rate:', 'uap' ) . "</strong> " . $conversionRate . "</p>";
			$adminEmail = get_option( 'uap_notification_email_addresses' );
			if ( $adminEmail === '' ){
					$adminEmail = get_option( 'admin_email' );
			}
			$headers[] = 'Content-Type: text/html; charset=UTF-8';
			$sent = wp_mail( $adminEmail, $subject, $message, $headers );
	}

}

endif;
