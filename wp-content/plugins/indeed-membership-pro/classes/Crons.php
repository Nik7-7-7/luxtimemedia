<?php
namespace Indeed\Ihc;

class Crons
{
    /**
     * @param none
     * @return none
     */
    public function __construct()
    {
        // save the interval for crons

        // >= 12.1 stripe connect
        add_action( 'iump_stripe_connect_queue', [ $this, 'stripeConnectQueue'], 1, 1);

        // check for expired subscriptions. Used on downgrade level
        add_action( 'ihc_check_subscription_expired', [ $this, 'onSubscriptionExpired' ], 82 );

        // used in due payment notification
        add_action( 'ihc_check_subscription_payment_due', [ $this, 'onSubscriptionPaymentDue' ], 82 );

        // used in Card Expiry Reminder notification
        add_action( 'ihc_check_card_expire_time', [ $this, 'onCardExpiryReminder' ], 82 );

        // used in "user enter in grace period" notification
        add_action( 'ihc_check_subscription_enter_grace_period', [ $this, 'onSubscriptionEnterGracePeriod'], 82 );

        // used in "trial subscription expired" notification
        add_action( 'ihc_cron_check_subscription_trial_expired', [ $this, 'getSubscriptionTrialExpired' ], 82 );

        // used in user subscription before expire notification
        add_action( 'ihc_notifications_job', [ $this, 'beforeExpireSubscription' ], 82 );

        // used in email verification
        add_action( 'ihc_check_verify_email_status', [ $this, 'verifyEmailStatus' ], 82 );

        // used to clean security table
        add_action( 'ihc_clean_security_table', [ $this, 'cleanSecurityTable' ], 82 );

        // used in drip content notifications
        add_action( 'ihc_drip_content_notifications', [ $this, 'dripContentNotifications' ], 82 );

        // weekly reports
        add_action( 'ihc_weekly_reports', [ $this, 'weeklyReports' ], 82 );

        /// add new schedule type
        add_filter( 'cron_schedules', [ $this, 'addWeekly' ], 82 );

    }

    /**
     * @param none
     * @return none
     */
    public function registerCrons()
    {
        // check for expired subscriptions. Used on downgrade level
        if ( !wp_get_schedule( 'ihc_check_subscription_expired' ) ){
            wp_schedule_event( time(), 'daily', 'ihc_check_subscription_expired' );
        }

        // used in due payment notification
        if ( !wp_get_schedule( 'ihc_check_subscription_payment_due' ) ){
            wp_schedule_event( time(), 'daily', 'ihc_check_subscription_payment_due' );
        }

        // used in Card Expiry Reminder notification
        if ( !wp_get_schedule( 'ihc_check_card_expire_time' ) ){
            wp_schedule_event( time(), 'daily', 'ihc_check_card_expire_time' );
        }

        // used in "user enter in grace period" notification
        if ( !wp_get_schedule( 'ihc_check_subscription_enter_grace_period' ) ){
            wp_schedule_event( time(), 'twicedaily', 'ihc_check_subscription_enter_grace_period' );// run at every 12hours
        }

        // used in "trial subscription expired" notification
        if ( !wp_get_schedule( 'ihc_cron_check_subscription_trial_expired' ) ){
            wp_schedule_event( time(), 'twicedaily', 'ihc_cron_check_subscription_trial_expired' );
        }

        // used in user subscription before expire notification
        if (!wp_get_schedule( 'ihc_notifications_job')){
    			   wp_schedule_event( time(), 'daily', 'ihc_notifications_job' );
    		}

        // used in email verification
        if (!wp_get_schedule( 'ihc_check_verify_email_status')){
          wp_schedule_event( time(), 'daily', 'ihc_check_verify_email_status' );
        }

        // used to clean security table
        if (!wp_get_schedule('ihc_clean_security_table')){
          wp_schedule_event( time(), 'daily', 'ihc_clean_security_table' );
        }

        // used in drip content notifications
        if (!wp_get_schedule('ihc_drip_content_notifications')){
          wp_schedule_event( time(), 'daily', 'ihc_drip_content_notifications' );
        }

        // weekly email report
        if ( !wp_get_schedule('ihc_weekly_reports') ){
            if ( date("l") !== 'Monday' ){
                $whenToStart = strtotime("next monday");
            } else {
                $whenToStart = time();
            }
            wp_schedule_event( $whenToStart, 'weekly', 'ihc_weekly_reports' );
        }

    }

    /**
     * Used on downgrade level
     * Run every 24hours
     * Will fire the action : ihc_action_subscription_expired
     * @param none
     * @return none
     */
    public function onSubscriptionExpired()
    {
        $lastRunTime = get_option( 'ihc_check_subscription_expired-last_run_time' );
        $currentTime = indeed_get_unixtimestamp_with_timezone();
        if ( $lastRunTime === false ){
            $lastRunTime = $currentTime - 24 * 3600;// current time - one day
        }
        // update last time run
        update_option( 'ihc_check_subscription_expired-last_run_time', $currentTime );
        $timeDiff = 24 * 3600;// one day

        $memberships = \Indeed\Ihc\Db\Memberships::getAll();
        if ( !$memberships ){
            return;
        }

        foreach ( $memberships as $membershipData ){
            $expired = \Indeed\Ihc\UserSubscriptions::getMembersWithExpiredSubscription( $membershipData['id'], $lastRunTime, $timeDiff );
            if ( !$expired ){
                continue;
            }
            foreach ( $expired as $expiredSubscription ){
                do_action( 'ihc_action_subscription_expired', $expiredSubscription['user_id'], $expiredSubscription['level_id'], $expiredSubscription );
            }
        }
    }

    /**
     * Used in Due Payment Notification.
     * Run every 24hours
     * Will fire the action : ihc_action_subscription_will_expired_soon
     * @param none
     * @return none
     */
    public function onSubscriptionPaymentDue()
    {
        $lastRunTime = get_option( 'ihc_check_subscription_payment_due-last_run_time' );
        $currentTime = indeed_get_unixtimestamp_with_timezone();
        if ( $lastRunTime === false ){
            $lastRunTime = $currentTime - 24 * 3600;
        }
        // update last time run
        update_option( 'ihc_check_subscription_payment_due-last_run_time', $currentTime );

        $timeDiff = get_option( 'ihc_notification_payment_due_time_interval' );
        if ( $timeDiff === false ){
            $timeDiff = 1;
        }
        $timeDiff = $timeDiff * 24 * 60 * 60;
        $startTime = $lastRunTime + $timeDiff;
        $endTime = $currentTime + $timeDiff;

        $subscriptions = \Indeed\Ihc\UserSubscriptions::getMembersWithPaymentDue( $startTime, $endTime );
        if ( !$subscriptions ){
            return;
        }
        foreach ( $subscriptions as $subscription ){
            do_action( 'ihc_action_subscription_payment_due', $subscription['uid'], $subscription['lid'] );
        }
    }

    /**
     * Used in Card Expiry Reminder Notification.
     * Run every 24hours
     * @param none
     * @return none
     */
    public function onCardExpiryReminder()
    {
        /*
        $lastRunTime = get_option( 'ihc_check_card_expiry_time-last_run_time' );
        $dayOfMonth = get_option( 'ihc_notification_card_expiry_time_interval' );
        return;
        //Check any Card that will expire on the next Calendar Month.

        $currentTime = indeed_get_unixtimestamp_with_timezone();
        if ( $lastRunTime === false ){
            $lastRunTime = $currentTime - 24 * 3600;
        }
        // update last time run
        update_option( 'ihc_check_card_expiry_time-last_run_time', $currentTime );


        if ( $timeDiff === false ){
            $timeDiff = 1;
        }
        $timeDiff = $timeDiff * 24 * 60 * 60;
        $startTime = $lastRunTime + $timeDiff;
        $endTime = $currentTime + $timeDiff;
        */
        $dayOfMonth = get_option( 'ihc_notification_card_expiry_time_interval' );
        $currentDay = date( 'd' );
        if ( (int)$dayOfMonth !== (int)$currentDay ){
            return;
        }

        //Check for expired Cards
        $month = date( 'm', strtotime("next month") );
        $year = date( 'y', strtotime("next month") );
        $subscriptions = \Ihc_Db::stripeConnectGetCardsThatWillExpire( $month, $year );

        //Check for exp_year and exp_month and return uid & lid

        if ( !$subscriptions ){
            return;
        }
        foreach ( $subscriptions as $subscription ){
            do_action( 'ihc_action_card_expire_reminder', $subscription['uid'], $subscription['lid'] );
        }

    }
    /**
      * Used in Enter Grace Period Notification.
      * Run every 12hours
      * Will fire the action : ihc_action_subscription_enter_grace_period
     * @param none
     * @return none
     */
    public function onSubscriptionEnterGracePeriod()
    {
        $lastRunTime = get_option( 'ihc_check_subscription_enter_grace_period-last_run_time' );
        $currentTime = indeed_get_unixtimestamp_with_timezone();
        if ( $lastRunTime === false ){
            $lastRunTime = $currentTime - 12 * 3600;
        }
        // update last time run
        update_option( 'ihc_check_subscription_enter_grace_period-last_run_time', $currentTime );
        $startTime = $lastRunTime;


        $memberships = \Indeed\Ihc\Db\Memberships::getAll();
        if ( !$memberships ){
            return;
        }
        foreach ( $memberships as $membershipData ){
            $defaultGracePeriod = \Indeed\Ihc\Db\Memberships::getMembershipGracePeriod( $membershipData['id'] );
            $expired = \Indeed\Ihc\UserSubscriptions::getMembersThatEnterInGracePeriod( $membershipData['id'], $defaultGracePeriod, $startTime );
            if ( !$expired ){
                continue;
            }
            foreach ( $expired as $expiredSubscription ){
                do_action( 'ihc_action_subscription_enter_grace_period', $expiredSubscription['uid'], $expiredSubscription['lid'], $expiredSubscription['expire_time'] );
            }
        }
    }

    /**
     * Used in expire trial notification.
     * It will select all the users that have subscription with expired trial.
     * Will fire the action : ihc_action_subscription_trial_expired
     * @param none
     * @return none
     */
    public function getSubscriptionTrialExpired()
    {
        $lastRunTime = get_option( 'ihc_check_subscription_trial_expired-last_run_time' );
        $currentTime = indeed_get_unixtimestamp_with_timezone();
        if ( $lastRunTime === false ){
            $lastRunTime = $currentTime - 12 * 3600;
        }
        // update last time run
        update_option( 'ihc_check_subscription_trial_expired-last_run_time', $currentTime );

        $startTime = $lastRunTime;
        $endTime = $currentTime;

        /// getting all the users with trial levels that expired
        $subscriptions = \Indeed\Ihc\UserSubscriptions::getMemberWithExpiredTrial( $startTime, $endTime );
        if ( !$subscriptions ){
            return;
        }
        // fire the action
        foreach ( $subscriptions as $subscription ){
            do_action( 'ihc_action_subscription_trial_expired', $subscription['uid'], $subscription['lid'] );
        }
    }

    /**
     * Used in before and on expire subscription notification.
     * Will fire the filter : ihc_filter_notification_before_expire that it's called in NotificationTriggers
     * @param none
     * @return none
     */
    public function beforeExpireSubscription()
    {
      // last time when the cron run
      $lastRunTime = get_option( 'ihc_check_expired_subscriptions-last_run_time' );
      $currentTime = indeed_get_unixtimestamp_with_timezone();
      if ( $lastRunTime === false ){
          $lastRunTime = $currentTime - 24 * 3600;;
      }
      update_option( 'ihc_check_expired_subscriptions-last_run_time', $currentTime );

      $mail_sent_to_uid = array();

      /// FIRST BEFORE EXPIRE
      $first_before_expire = \Indeed\Ihc\Notifications::getOneByType('before_expire');
      $first_before_expire_admin = \Indeed\Ihc\Notifications::getOneByType('admin_before_user_expire_level');

      if ($first_before_expire || $first_before_expire_admin){
        $days = get_option("ihc_notification_before_time");

        if (!$days){
          $days = 5;
        }

        $lowerThan = $currentTime + ($days - 1) * 24 * 60 * 60;
        $graterThan = $lastRunTime + ($days - 1) * 24 * 60 * 60;
        $now = $lastRunTime;

        $u_ids = \Indeed\Ihc\UserSubscriptions::selectByExpireTime( $lowerThan, $graterThan, $now );
        if ($u_ids){
          foreach ($u_ids as $u_data){
            $sent = FALSE;
            $uid = $u_data->user_id;
            if ($first_before_expire){
              $sent = apply_filters( 'ihc_filter_notification_before_expire', $sent, $uid, $u_data->level_id, 'before_expire' );
            }

            if ($first_before_expire_admin){
              $sent = apply_filters( 'ihc_filter_notification_before_expire', $sent, $uid, $u_data->level_id, 'admin_before_user_expire_level' );
            }

            if ($sent){
              $mail_sent_to_uid[] = $u_data->user_id;
            }
          }
        }
      }

      /// SECOND BEFORE EXPIRE
      $second_before_expire = \Indeed\Ihc\Notifications::getOneByType('second_before_expire');
      $second_before_expire_admin = \Indeed\Ihc\Notifications::getOneByType('admin_second_before_user_expire_level');

      if ($second_before_expire || $second_before_expire_admin){
        $days = get_option("ihc_notification_before_time_second");
        if (!$days){
          $days = 3;
        }
        $lowerThan = $currentTime + ($days - 1) * 24 * 60 * 60;
        $graterThan = $lastRunTime + ($days - 1) * 24 * 60 * 60;
        $now = $lastRunTime;
        $u_ids = \Indeed\Ihc\UserSubscriptions::selectByExpireTime( $lowerThan, $graterThan, $now );
        if ($u_ids){
          foreach ($u_ids as $u_data){
            $sent = FALSE;
            $uid = $u_data->user_id;

            if (in_array($uid, $mail_sent_to_uid)){
              continue;
            }

            if ($second_before_expire){
              $sent = apply_filters( 'ihc_filter_notification_before_expire', $sent, $uid, $u_data->level_id, 'second_before_expire' );
            }

            if ($second_before_expire_admin){
              $sent = apply_filters( 'ihc_filter_notification_before_expire', $sent, $uid, $u_data->level_id, 'admin_second_before_user_expire_level' );
            }

            if ($sent){
              $mail_sent_to_uid[] = $uid;
            }
          }
        }
      }
      /// THIRD BEFORE EXPIRE
      $third_before_expire = \Indeed\Ihc\Notifications::getOneByType('third_before_expire');
      $third_before_expire_admin = \Indeed\Ihc\Notifications::getOneByType('admin_third_before_user_expire_level');

      if ($third_before_expire || $third_before_expire_admin){
        $days = get_option("ihc_notification_before_time_third");
        if (!$days){
          $days = 1;
        }
        $lowerThan = $currentTime + ($days - 1) * 24 * 60 * 60;
        $graterThan = $lastRunTime + ($days - 1) * 24 * 60 * 60;
        $now = $lastRunTime;
        $u_ids = \Indeed\Ihc\UserSubscriptions::selectByExpireTime( $lowerThan, $graterThan, $now );
        if ($u_ids){
          foreach ($u_ids as $u_data){
            $sent = FALSE;
            $uid = $u_data->user_id;
            if (in_array($uid, $mail_sent_to_uid)){
              continue;
            }
            if ($third_before_expire){
              $sent = apply_filters( 'ihc_filter_notification_before_expire', $sent, $uid, $u_data->level_id, 'third_before_expire' );
            }
            if ($third_before_expire_admin){
              $sent = apply_filters( 'ihc_filter_notification_before_expire', $sent, $uid, $u_data->level_id, 'admin_third_before_user_expire_level' );
            }
            if ($sent){
              $mail_sent_to_uid[] = $uid;
            }
          }
        }
      }

      //// LEVEL EXPIRED
      $expire = \Indeed\Ihc\Notifications::getOneByType('expire');
      $expire_to_admin = \Indeed\Ihc\Notifications::getOneByType('admin_user_expire_level');

      if ($expire || $expire_to_admin){
        $lowerThan = $lastRunTime - 24 * 60 * 60;
        $graterThan = $lastRunTime - 2 * 24 * 60 * 60;
        $u_ids = \Indeed\Ihc\UserSubscriptions::selectByExpireTime( $lowerThan, $graterThan );
        if ($u_ids){
          foreach ($u_ids as $u_data){
            $sent = FALSE;
            if ($expire){
              $sent = apply_filters( 'ihc_filter_notification_before_expire', $sent, $u_data->user_id, $u_data->level_id, 'expire' );
            }
            if ($expire_to_admin){
              $sent = apply_filters( 'ihc_filter_notification_before_expire', $sent, $u_data->user_id, $u_data->level_id, 'admin_user_expire_level' );
            }
            do_action('ihc_action_level_has_expired', $u_data->user_id, $u_data->level_id);
            // @description run when level has expired. @param user id (integer), level id (integer)
          }
        }
      }
    }

    /**
     * Used in email verification.
     * @param none
     * @return none
     */
    public function verifyEmailStatus()
    {
      global $wpdb;
      $time_limit = (int)get_option('ihc_double_email_delete_user_not_verified');
    	if ($time_limit>-1){
    		$time_limit = $time_limit * 24 * 60 * 60;
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "SELECT user_id FROM " . $wpdb->usermeta . "
    										WHERE meta_key='ihc_verification_status'
    										AND meta_value='-1';";
    		$data = $wpdb->get_results( $query );

    		if (!empty($data)){
    			if (!function_exists('wp_delete_user')){
    				require_once ABSPATH . 'wp-admin/includes/user.php';
    			}

    			foreach ($data as $k=>$v){
    				if (!empty($v->user_id)){
              $query = $wpdb->prepare( "SELECT user_registered FROM {$wpdb->users}
    							                         WHERE ID=%d ;", $v->user_id );
    					$time_data = $wpdb->get_row( $query );

    					if (!empty($time_data->user_registered)){
    						$time_to_delete = strtotime($time_data->user_registered)+$time_limit;
    						if ( $time_to_delete < indeed_get_unixtimestamp_with_timezone() ){
    							//delete user
    							wp_delete_user( $v->user_id );
    							\Indeed\Ihc\UserSubscriptions::deleteAllForUser( $v->user_id );
    							//send notification
    							do_action( 'ihc_delete_user_action', $v->user_id );
    						}
    					}
    				}
    			}
    		}
    	}
    }

    /**
     * Used to clean security table.
     * @param none
     * @return none
     */
    public function cleanSecurityTable()
    {
        global $wpdb;
      	$table = $wpdb->prefix . 'ihc_security_login';
      	$current_time = indeed_get_unixtimestamp_with_timezone();
      	$expire_hours = get_option('ihc_login_security_extended_lockout_time');

      	if ($expire_hours===FALSE){
      		$expire_hours = 24;
      	}

      	$expire = $expire_hours * 60 * 60;
        $query = $wpdb->prepare( "UPDATE {$wpdb->prefix}ihc_security_login
                                      SET attempts_count=0, locked=0
                                      WHERE log_time+%d<%d;", $expire, $current_time );
      	$wpdb->query( $query );
    }

    /**
     * Used in drip content notifications.
     * @param none
     * @return none
     */
    public function dripContentNotifications()
    {
        require_once IHC_PATH . 'classes/DripContentNotifications.class.php';
      	$object = new \DripContentNotifications();
    }

    /**
     * @param array
     * @return array
     */
    public function addWeekly( $schedules=[] )
    {
        $interval = 7 * 24 * 60 * 60;
        $schedules['weekly'] = array(
            'interval' => $interval,
            'display'  => esc_html__( 'Weekly', 'ihc' )
        );
        return $schedules;
    }

    /**
     * Used in weekly reports notifications.
     * @param none
     * @return none
     */
    public function weeklyReports()
    {
        if ( (int)get_option( 'ihc_reason_for_weekly_email_enabled' ) === 0 ){
            return ;
        }
        $subject = esc_html__( '[Ultimate Membership Pro Your summary report for last week', 'ihc') ." ". get_site_url();

        $start = time() - 7 * 24 * 60 * 60;// 7days
        $end = time();
        $subscriptionsCount = \Indeed\Ihc\UserSubscriptions::countInInterval( $start, $end );
        $ordersObject = new \Indeed\Ihc\Db\Orders();
        $amountTotal = $ordersObject->getTotalAmountInInterval( $start, $end );
        $ordersCount = $ordersObject->getCountInInterval( $start, $end );

        $currency = get_option( 'ihc_currency' );
        $amountTotal = ihc_format_price_and_currency( $currency, $amountTotal );

        $message = "<p>" . esc_html__( 'Here is the summary report for the last week:', 'ihc' ) . "</p>" .
                    "<p><strong>" . esc_html__( 'New Orders:', 'ihc' ) . "</strong> " . $ordersCount . "</p>
                    <p><strong>" . esc_html__( 'Total Revenue collected:', 'ihc' ) . "</strong> " . $amountTotal . "</p>
                    <p><strong>" . esc_html__( 'New Subscriptions:', 'ihc' ) . "</strong> " . $subscriptionsCount . "</p>";
        $adminEmail = get_option( 'ihc_notification_email_addresses' );
        if ( $adminEmail === '' ){
            $adminEmail = get_option( 'admin_email' );
        }
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $sent = wp_mail( $adminEmail, $subject, $message, $headers );
    }

    /**
     * Since version 12.1
     * @param string
     * @return none
     */
    public function stripeConnectQueue( $transactionId='' )
    {
    		$enabled = get_option('ihc_stripe_connect_status');
    		if ( empty( $enabled ) ){
    				return;
    		}

    		$postData = json_decode( get_option( $transactionId ), true );

    		if ( !$postData ){
    				return;
    		}
    		delete_option( $transactionId );
        $postData['via_cron'] = true; // put a flag in order to know this comes from cron call
    		$object = new \Indeed\Ihc\Gateways\StripeConnect();
    		$object->setwebhookResponse( $postData );
    		$object->webhookPayment();
    }

}
