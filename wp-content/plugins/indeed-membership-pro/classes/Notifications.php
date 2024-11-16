<?php
namespace Indeed\Ihc;

/*
How to send a notification:
$notification = new \Indeed\Ihc\Notifications();
$notification->setUid( $uid )
             ->setLid( $lid )
             ->setType( 'register' )
             ->setMessageVariables( [] )
             ->send();
*/
class Notifications
{
    /**
     * @var int
     */
    private $uid                = 0;
    /**
     * @var int
     */
    private $lid                = 0;
    /**
     * @var string
     */
    private $type               = '';
    /**
     * @var array
     */
    private $messageVariables   = [];
    /**
     * @var string
     */
    private $subject            = '';
    /**
     * @var string
     */
    private $message            = '';
    /**
     * @var array
     */
    private $adminCases         = [
                          'admin_user_register',
                          'admin_before_user_expire_level',
                          'admin_second_before_user_expire_level',
                          'admin_third_before_user_expire_level',
                          'admin_user_expire_level',
                          'admin_user_payment',
                          'admin_user_profile_update',
                          'ihc_cancel_subscription_notification-admin',
                          'ihc_delete_subscription_notification-admin',
                          'ihc_order_placed_notification-admin',
                          'ihc_new_subscription_assign_notification-admin',
                          'admin_user_enter_grace_period',
                          'admin_user_subscription_renew',
                          'admin_user_login',
                          'admin_before_subscription_payment_due',
                          'admin_user_subscription_first_time_activation',
                          'admin_user_subscription_trial_expired',
    ];


    public function __construct(){}

    public function getAllNotificationNames()
    {
        return [
          //----------Admin Notifications----------

          //Register Process
          'admin_user_register' => esc_html__('New Customer Registered', 'ihc'), // - Admin Notification

          //Subscriptions
          'ihc_new_subscription_assign_notification-admin' => esc_html__('New Subscription Assigned to Customer', 'ihc'),
          'admin_user_subscription_first_time_activation'		=> esc_html__( 'Activation Subscription - First Time', 'ihc' ),
          'admin_user_subscription_renew'			=> esc_html__( 'Activation Subscription - Renewed', 'ihc' ),
          'admin_user_subscription_trial_expired'			=> esc_html__( 'Customer Trial Subscription has expired', 'ihc' ),
          'admin_before_user_expire_level' => esc_html__('First Alert Before Subscription Expire', 'ihc'),
          'admin_second_before_user_expire_level' => esc_html__('Second Alert Before Subscription Expire', 'ihc'),
          'admin_third_before_user_expire_level' => esc_html__('Third Alert Before Subscription Expire', 'ihc'),
          'admin_user_enter_grace_period'			=> esc_html__( 'Customer enter in Grace Period', 'ihc' ),
          'admin_user_expire_level' => esc_html__('After Subscription Expired', 'ihc'),

          //Payments
          'ihc_order_placed_notification-admin' => esc_html__('New Order Created', 'ihc'),
          'admin_user_payment' => esc_html__('Payment confirmation received - Order Completed', 'ihc'),
          'admin_before_subscription_payment_due'				=> esc_html__( 'Before Subscription Payment due', 'ihc' ),

          //Customer Actions
          'admin_user_profile_update' => esc_html__('Customer has Updated his Profile information', 'ihc'),
          'admin_user_login'						=> esc_html__( 'Customer Login Alert', 'ihc' ),
          'ihc_cancel_subscription_notification-admin' => esc_html__('Customer has Cancelled Subscription', 'ihc'),
          'ihc_delete_subscription_notification-admin' => esc_html__('Customer Deleted Subscription', 'ihc'),


          //----------Users Notifications----------

          //Register Process
          'register' => esc_html__('New Active Account Registered', 'ihc'), //Register
          'review_request' => esc_html__('New Pending Account Registered- Approve Request', 'ihc'), //register with pending

          //Register Lite
          'register_lite_send_pass_to_user' => esc_html__('Send auto-generated Password to Customer', 'ihc'),

          //Double Email Verification
          'email_check' => esc_html__('Double E-mail Verification Request', 'ihc'),
          'email_check_success' => esc_html__('Double E-mail Verification Completed', 'ihc'),

          //Reset Password Process
          'reset_password_process' => esc_html__('Reset Password - Step 1: Confirmation Request', 'ihc'),
          'reset_password' => esc_html__('Reset Password - Step 2: Send Generated Password', 'ihc'),
          'change_password' => esc_html__('Reset Password - Step 3: Password Changed notification', 'ihc'),

          //Customer Account
          'approve_account' => esc_html__('Account have been Approved by Admin'),
          'delete_account' => esc_html__('Account has been Deleted', 'ihc'),

          //Custom Actions
          'user_update' => esc_html__('Customer has Updated his Profile information', 'ihc'),
          'ihc_cancel_subscription_notification-user' => esc_html__('Customer has Cancelled Subscription', 'ihc'),
          'ihc_delete_subscription_notification-user' => esc_html__('Customer Deleted Subscription', 'ihc'),

          //Subscriptions
          'ihc_new_subscription_assign_notification' => esc_html__('New Subscription Assigned to Customer', 'ihc'),
          'ihc_subscription_activated_notification' => esc_html__('Activation Subscription - First Time', 'ihc'),
          'user_subscription_renew'			=> esc_html__( 'Activation Subscription - Renewed', 'ihc' ),
          'user_subscription_trial_expired'			=> esc_html__( 'Customer Trial Subscription has expired', 'ihc' ),
          'before_expire' => esc_html__('First Alert Before Subscription Expire', 'ihc'),
          'second_before_expire' => esc_html__('Second Alert Before Subscription Expire', 'ihc'),
          'third_before_expire' => esc_html__('Third Alert Before Subscription Expire', 'ihc'),
          'user_enter_grace_period'			=> esc_html__( 'Customer enter in Grace Period', 'ihc' ),
          'expire' => esc_html__('After Subscription Expired', 'ihc'),

          //Payments
          'ihc_order_placed_notification-user' => esc_html__('New Order Created', 'ihc'),
          'payment' => esc_html__('Payment confirmation received - Order Completed', 'ihc'),
          'bank_transfer' => esc_html__('Bank Transfer Payment Details', 'ihc'),
          'user_before_subscription_payment_due'				=> esc_html__( 'Before Subscription Payment due', 'ihc' ),

          //Stripe dbx_connect
          'upcoming_card_expiry_reminder'				=> esc_html__( 'Upcoming Card Expiry Reminder', 'ihc' ),
          //Drip Content
          'drip_content-user' => esc_html__('When Post become Available', 'ihc'),
        ];
    }

    public function getAdminCases()
    {
        return $this->adminCases;
    }

    /**
     * @param int
     * @return object
     */
    public function setUid( $input=0 )
    {
        $this->uid = $input;
        return $this;
    }

    /**
     * @param int
     * @return object
     */
    public function setLid( $input=0 )
    {
      $this->lid = $input;
      return $this;
    }

    /**
     * @param int
     * @return object
     */
    public function setType( $input='' )
    {
        $this->type = $input;
        return $this;
    }

    /**
     * @param array
     * @return object
     */
    public function setMessageVariables( $input=[] )
    {
        $this->messageVariables = $input;
        return $this;
    }

    /**
     * @param string
     * @return object
     */
    public function setSubject( $input='' )
    {
        $this->subject = $input;
        return $this;
    }

    /**
     * @param string
     * @return object
     */
    public function setMessage( $input='' )
    {
        $this->message = $input;
        return $this;
    }

    /**
     * @param none
     * @return array
     */
    public function setNotificationData()
    {
          global $wpdb;
          if ( $this->lid > -1 ){
              // notification for a specific subscription
              $q = $wpdb->prepare("SELECT id,notification_type,level_id,subject,message,pushover_message,pushover_status,status FROM " . $wpdb->prefix . "ihc_notifications
                          WHERE 1=1
                          AND notification_type=%s
                          AND level_id=%d
                          ORDER BY id DESC LIMIT 1;", $this->type, $this->lid );
              $data = $wpdb->get_row($q);
              if ($data){
                  $subject = (isset($data->subject)) ? $data->subject : '';
                  $message = (isset($data->message)) ? $data->message : '';

                  $domain = 'ihc';

                  // user locale
                  $locale = get_user_locale( $this->uid );
                  if ( $locale !== '' && $locale !== false && strpos( $locale, '_' ) !== false ){
                  		$localeArray = explode( '_', $locale );
                  		$locale = isset( $localeArray[0] ) ? $localeArray[0] : $locale;
                  }

                  if ( $locale === '' || $locale === false || $locale === null ){
                      $locale = get_user_meta( $this->uid, 'ihc_locale_code', true );
                  }

                  $wmplName = $this->type . '_title_' . $this->lid;
                  $this->subject = apply_filters( 'wpml_translate_single_string', $subject, $domain, $wmplName, $locale );
                  $wmplName = $this->type . '_message_' . $this->lid;
                  $this->message = apply_filters( 'wpml_translate_single_string', $message, $domain, $wmplName, $locale );
              }
          }

          if ( $this->subject != '' && $this->message != '' ){
              return $this;
          }

          // general notification - no subscription
          $q = $wpdb->prepare( "SELECT id,notification_type,level_id,subject,message,pushover_message,pushover_status,status FROM " . $wpdb->prefix . "ihc_notifications
                      WHERE 1=1
                      AND notification_type=%s
                      AND level_id='-1'
                      ORDER BY id DESC LIMIT 1;", $this->type );
          $data = $wpdb->get_row($q);
          if ($data){
            $subject = (isset($data->subject)) ? $data->subject : '';
            $message = (isset($data->message)) ? $data->message : '';

              $domain = 'ihc';

              // user locale
              $locale = get_user_locale( $this->uid );
              if ( $locale !== '' && $locale !== false && strpos( $locale, '_' ) !== false ){
                  $localeArray = explode( '_', $locale );
                  $locale = isset( $localeArray[0] ) ? $localeArray[0] : $locale;
              }

              if ( $locale === '' || $locale === false || $locale === null ){
                  $locale = get_user_meta( $this->uid, 'ihc_locale_code', true );
              }

              $wmplName = $this->type . '_title_-1';
              $this->subject = apply_filters( 'wpml_translate_single_string', $subject, $domain, $wmplName, $locale );
              $wmplName = $this->type . '_message_-1';
              $this->message = apply_filters( 'wpml_translate_single_string', $message, $domain, $wmplName, $locale );
          }
    }

    /**
     * @param none
     * @return bool
     */
    public function send()
    {
          if ( !$this->uid || $this->type == '' ){
              return false;
          }

          // notification content
          if ( $this->subject == '' || $this->message == '' ){
              $this->setNotificationData();
          }

          if ( $this->message == '' ){
              return false;
          }

          $fromName = $this->getFromName();

          if ( in_array( $this->type, $this->adminCases ) ){
              // admin
              $userEmail = $this->getAdminEmail();
          } else {
              // member
              $userEmail = \Ihc_Db::user_get_email( $this->uid );
          }
          $fromEmail = $this->getFromEmail();

          $this->message = ihc_replace_constants( $this->message, $this->uid, $this->lid, $this->lid, $this->messageVariables );
          $this->subject = ihc_replace_constants( $this->subject, $this->uid, $this->lid, $this->lid, $this->messageVariables );
          $this->message = stripslashes( htmlspecialchars_decode( ihc_format_str_like_wp( $this->message ) ) );
          $this->message = apply_filters( 'ihc_send_notification_filter_message', $this->message, $this->uid, $this->lid, $this->type );

          $this->message = "<html><head></head><body>" . $this->message . "</body></html>";
          if ( $this->subject == '' && $this->message == '' && $userEmail == '' ){
              return false;
          }

          if ( !empty( $fromEmail ) && !empty( $fromName ) ){
            $headers[] = "From: $fromName <$fromEmail>";
          }
          $headers[] = 'Content-Type: text/html; charset=UTF-8';
          $sent = wp_mail( $userEmail, $this->subject, $this->message, $headers );

          if ( $sent ){
              \Indeed\Ihc\Db\NotificationLogs::save([
                            'uid'                       => $this->uid,
                            'lid'                       => $this->lid,
                            'message'                   => $this->message,
                            'subject'                   => $this->subject,
                            'notification_type'         => $this->type,
                            'email_address'             => $userEmail,
              ]);
          }

          $this->extraServices();

          return $sent;
    }

    /**
     * @param none
     * @return string
     */
    private function getFromEmail()
    {
        $fromEmail = get_option( 'ihc_notification_email_from' );
        if ( $fromEmail == '' ){
            $fromEmail = get_option( 'admin_email' );
        }
        return $fromEmail;
    }

    /**
     * @param none
     * @return string
     */
    private function getFromName()
    {
        $fromName = get_option( 'ihc_notification_name' );
        if ( $fromName == '' ){
            $fromName = get_option( 'blogname' );
        }
        return $fromName;
    }

    /**
     * @param none
     * @return string
     */
    private function getAdminEmail()
    {
        $adminEmail = get_option( 'ihc_notification_email_addresses' );
        if ( $adminEmail == '' ){
          $adminEmail = get_option( 'admin_email' );
        }
        return $adminEmail;
    }

    /**
     * @param none
     * @return none
     */
    private function extraServices()
    {
        if ( ihc_is_magic_feat_active( 'pushover' ) ){
            $this->pushover();
        }
    }

    /**
     * @param none
     * @return none
     */
    private function pushover()
    {
        $toAdmin = in_array( $this->type, $this->adminCases ) ? true : false;
        require_once IHC_PATH . 'classes/services/Ihc_Pushover.class.php';
        $pushover = new \Ihc_Pushover();
        $pushover->send_notification( $this->uid, $this->lid, $this->type, $toAdmin );
    }

    /**
     * @param string
     * @return array
     */
    public function getNotificationTemplate( $type='' )
    {
		//Templates contains full HTML content and styling which will sent out futher via Mail Server. Inline css is the only option in this case and can not be used css files
      $template = [
                    'subject'				=> '',
                    'content'				=> '',
                    'explanation' 		=> '',
      ];

       switch ($type){
         case 'register':
   $template['subject'] = '{blogname}: Welcome to {blogname}';
   $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
     <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
   <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
     <div>Hello {first_name} {last_name},</div>
     <div style="padding-top:30px"></div>
     <div>Thanks for registering on {blogname}. Your account is now active.</div>
     <div>To login please fill out your credentials on </div>
     <div style="padding-top:30px"></div>
     <div style="display: inline-block; text-align: center;"><a style="background: #90A09A; color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 3px; letter-spacing: 0.3px;" href="{login_page}">Login</a></div>
     <div style="padding-top:30px"></div>
     <div>Have a nice day!</div>
   <div style="padding-top:30px"></div>
   </div>
   <div style="background: #545454; color: #fff; padding: 20px 30px;">
   <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
   </div>
   </div>';
   $template['explanation'] = esc_html__( 'Notification is sent first time when member Register on website and account does not requires to be Approved.', 'ihc' );
           break;

         case 'review_request':
   $template['subject'] = '{blogname}: Welcome to {blogname}';
   $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
     <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
   <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
     <div>Hello {username},</div>
     <div style="padding-top:30px"></div>
     <div>Thanks for registering on {blogname}. Your account is waiting to be approved.</div>
     <div>Once your Account is approved you can login using your credentials on:
     <a style="color: #68aeff; text-decoration: none;" href="{login_page}">{login_page}</a></div>
     <div>Your Username: {username}</div>
     <div style="padding-top:30px"></div>
     <div>Have a nice day!</div>
   <div style="padding-top:30px"></div>
   </div>
   <div style="background: #545454; color: #fff; padding: 20px 30px;">
   <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
   </div>
   </div>
';
   $template['explanation'] = esc_html__( 'Notification is sent first time when member Register on website and account requires to be Approved.', 'ihc' );
           break;

         case 'payment':
           $template['subject'] = '{blogname}: Payment proceed';
           $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
             <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
           <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
             <div>Hello {first_name} {last_name},</div>
             <div style="padding-top:30px"></div>
             <div>You have proceed a new Payment into your account on {blogname}.</div>
             <div>Thanks for your payment!</div>
             <div style="padding-top:30px"></div>
             <div>Have a nice day!</div>
           <div style="padding-top:30px"></div>
           </div>
           <div style="background: #545454; color: #fff; padding: 20px 30px;">
           <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
           </div>
           </div>';
   $template['explanation'] = esc_html__( 'Every time when a Payment confirmation is received and Order becomes Completed', 'ihc' );
           break;

         case 'user_update':
           $template['subject'] = '{blogname}: Your Account has been Updated';
           $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
             <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
           <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
             <div>Hello {username},</div>
             <div style="padding-top:30px"></div>
             <div>Your Account has been Updated.</div>
             <div>To visit your Profile page follow the next link:
             <a style="color: #68aeff; text-decoration: none;" href="{account_page}">{account_page}</a></div>
             <div style="padding-top:30px"></div>
             <div>Have a nice day!</div>
           <div style="padding-top:30px"></div>
           </div>
           <div style="background: #545454; color: #fff; padding: 20px 30px;">
           <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
           </div>
           </div>';
   $template['explanation'] = esc_html__( 'Notification is sent everytime member update his Account details via Member Portal page', 'ihc' );
           break;

         case 'before_expire':
           $template['subject'] = '{blogname}: Your Subscription Expire';
           $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
             <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
           <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
             <div>Hello {first_name} {last_name},</div>
             <div style="padding-top:30px"></div>
             <div>Your Subscription {current_level} is going to expire on {current_level_expire_date}.</div>
             <div>To update your Subscriptions, please, visit your Profile page on:
             <a style="color: #68aeff; text-decoration: none;" href="{account_page}">{account_page}</a></div>
             <div><strong> Email:</strong> {user_email}</div>
             <div><strong> Membership:</strong> {level_name}</div>
             <div style="padding-top:30px"></div>
             <div>Have a nice day!</div>
           <div style="padding-top:30px"></div>
           </div>
           <div style="background: #545454; color: #fff; padding: 20px 30px;">
           <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
           </div>
           </div>';
   $template['explanation'] = esc_html__( 'Reminder notification sent before member membership is going to expire. You can choose how many days before notification should be triggered. This process is handled by a WP Cron running daily.', 'ihc' );
           break;
         case 'second_before_expire':
           $template['subject'] = '{blogname}: Your Subscription Expire';
           $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
             <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
           <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
             <div>Hello {first_name} {last_name},</div>
             <div style="padding-top:30px"></div>
             <div>Your Subscription {current_level} is going to expire on {current_level_expire_date}.</div>
             <div>To update your Subscriptions, please, visit your Profile page on:<a href="{account_page}">{account_page}</a></div>
             <div style="padding-top:30px"></div>
             <div>Have a nice day!</div>
           <div style="padding-top:30px"></div>
           </div>
           <div style="background: #545454; color: #fff; padding: 20px 30px;">
           <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
           </div>
           </div>';
   $template['explanation'] = esc_html__( 'Second Reminder notification sent before member membership is going to expire. You can choose how many days before notification should be triggered. This process is handled by a WP Cron running daily.', 'ihc' );
           break;
         case 'third_before_expire':
           $template['subject'] = '{blogname}: Your Subscription Expire';
           $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
             <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
           <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
             <div>Hello {first_name} {last_name},</div>
             <div style="padding-top:30px"></div>
             <div>Your Subscription {current_level} is going to expire on {current_level_expire_date}.</div>
             <div>To update your Subscriptions, please, visit your Profile page on:
             <a style="color: #68aeff; text-decoration: none;" href="{account_page}">{account_page}</a></div>
             <div style="padding-top:30px"></div>
             <div>Have a nice day!</div>
           <div style="padding-top:30px"></div>
           </div>
           <div style="background: #545454; color: #fff; padding: 20px 30px;">
           <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
           </div>
           </div>';
   $template['explanation'] = esc_html__( 'Third Reminder notification sent before member membership is going to expire. You can choose how many days before notification should be triggered. This process is handled by a WP Cron running daily.', 'ihc' );
           break;
         case 'expire':
           $template['subject'] = '{blogname}: Your Subscription has Expired';
           $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
             <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
           <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
             <div>Hello {first_name} {last_name},</div>
             <div style="padding-top:30px"></div>
             <div>Your Subscription {current_level} has expired on {current_level_expire_date}.</div>
             <div>To update your Subscriptions, please, visit your Profile page on:
             <a style="color: #68aeff; text-decoration: none;" href="{account_page}">{account_page}</a></div>
             <div style="padding-top:30px"></div>
             <div>Have a nice day!</div>
           <div style="padding-top:30px"></div>
           </div>
           <div style="background: #545454; color: #fff; padding: 20px 30px;">
           <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
           </div>
           </div>';
   $template['explanation'] = esc_html__( 'Notification is sent after member membership has expired. This process is handled by a WP Cron running daily.', 'ihc' );
           break;

         case 'email_check':
           $template['subject'] = '{blogname}: Email Verification';
           $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
             <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
           <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
             <div>Hello {first_name} {last_name},</div>
             <div style="padding-top:30px"></div>
             <div>You must confirm/validate your Email Account before logging in.</div>
             <div>Please click on the following link to successfully activate your account: <a style="color: #68aeff; text-decoration: none;" href="{verify_email_address_link}">click here</a> </div>
             <div>Have a nice day!</div>
             <div style="padding-top:30px"></div>
           </div>
           <div style="background: #545454; color: #fff; padding: 20px 30px;">
           <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
           </div>
           </div>';
   $template['explanation'] = esc_html__( 'When Double Email Verification setting is turned on, after member registration this first email is sent with a generated confirmation link inside that will allow member to confirm that his email is real.', 'ihc' );
           break;

         case 'email_check_success':
           $template['subject'] = '{blogname}: Email Verification Successfully';
           $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
             <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
           <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
             <div>Hello {first_name} {last_name},</div>
             <div style="padding-top:30px"></div>
             <div>Your account is now verified at {blogname}.</div>
             <div>Have a nice day!</div>
           <div style="padding-top:30px"></div>
           </div>
           <div style="background: #545454; color: #fff; padding: 20px 30px;">
           <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
           </div>
           </div>';
   $template['explanation'] = esc_html__( 'When Double Email Verification setting is turned on, after member confirmed his email address a successful confirmation notification is sent.', 'ihc' );
           break;

         case 'reset_password_process':
           $template['subject'] = '{blogname}: Reset Password request';
           $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
             <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
           <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
             <div>Hello {first_name} {last_name},</div>
             <div style="padding-top:30px"></div>
             <div>You or someone else has requested to change password for your account: {username}.</div>
             <div>To confirm this request click <a style="color: #68aeff; text-decoration: none;" href="{password_reset_link}">here</a></div>
             <div style="padding-top:30px"></div>
             <div>A new generated Password will be sent via Email next after the request was confirmed.</div>
             <div>If you did not request for a new password, please ignore this Email notification.</div>
             <div>Have a nice day!</div>
           <div style="padding-top:30px"></div>
           </div>
           <div style="background: #545454; color: #fff; padding: 20px 30px;">
           <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
           </div>
           </div>';
   $template['explanation'] = esc_html__( 'When member wants to reset his password, this notification is sent first time in order user to confirm this request. A generated link for request confirmation will be sent. This will guarantee that request was something by current member and not someone else. ', 'ihc' );
           break;

         case 'reset_password':
           $template['subject'] = '{blogname}: New Password generated';
           $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
             <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
           <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
             <div>Hello {first_name} {last_name},</div>
             <div style="padding-top:30px"></div>
             <div>You or someone else has requested to change password for your account: {username}.</div>
             <div>Your new Password is: <strong>{NEW_PASSWORD}</strong></div>
             <div style="padding-top:30px"></div>
             <div>To update your Password once you are logged from your Profile Page: <a style="color: #68aeff; text-decoration: none;">{account_page}</a></div>
             <div style="padding-top:30px"></div>
             <div>If you did not request for a new password, please ignore this Email notification.</div>
             <div>Have a nice day!</div>
           <div style="padding-top:30px"></div>
           </div>
           <div style="background: #545454; color: #fff; padding: 20px 30px;">
           <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
           </div>
           </div>';
   $template['explanation'] = esc_html__( 'After member confirms his reset passwrod request a random password will be generated for him and sent via this second Notification.', 'ihc' );
           break;

         case 'change_password':
           $template['subject'] = '{blogname}: Your Password has been changed';
           $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
             <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
           <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
             <div>Hello {first_name} {last_name},</div>
             <div style="padding-top:30px"></div>
             <div>Your Password has been changed.</div>
             <div>To login please fill out your credentials on: <a style="color: #68aeff; text-decoration: none;" href="{login_page}">{login_page}</a></div>
             <div>Your Username: {username}</div>
             <div>Have a nice day!</div>
           <div style="padding-top:30px"></div>
           </div>
           <div style="background: #545454; color: #fff; padding: 20px 30px;">
           <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
           </div>
           </div>';
   $template['explanation'] = esc_html__( 'After WordPress user password have been successfully changed this notification will be sent.', 'ihc' );
           break;

         case 'delete_account':
           $template['subject'] = '{blogname}: Your Account has been deleted';
           $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
             <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
           <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
             <div>Hello {username},</div>
             <div style="padding-top:30px"></div>
             <div>Your account has been deleted from {blogname}.</div>
             <div>Have a nice day!</div>
           <div style="padding-top:30px"></div>
           </div>
           <div style="background: #545454; color: #fff; padding: 20px 30px;">
           <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
           </div>
           </div>';
   $template['explanation'] = esc_html__( 'When member WordPress account have been deleted his is notified via email.', 'ihc' );
           break;

         case 'bank_transfer':
             $template['subject'] = '{blogname}: Payment Inform';
             $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
               <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
             <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
               <div>Hello {username},</div>
               <div style="padding-top:30px"></div>
               <div>Please proceed the bank transfer payment for: {currency}{amount}</div>
               <div><strong>Payment Details:</strong> Subscription {level_name} for {username} with Identification: {user_id}_{level_id}</div>
               <div><strong>Bank Details:</strong></div>
               <div><strong>IBAN</strong>:xxxxxxxxxxxxxxxxxxxx</div>
               <div>Bank NAME</div>
               <div>Have a nice day!</div>
             <div style="padding-top:30px"></div>
             </div>
             <div style="background: #545454; color: #fff; padding: 20px 30px;">
             <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
             </div>
             </div>';
   $template['explanation'] = esc_html__( 'If member choose to pay via Bank Transfer he will receives a notification with all details that needs to be followed in order to complete the payment.', 'ihc' );
           break;

         case 'approve_account':
             $template['subject'] = '{blogname}: Your Account has been activated';
             $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
               <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
             <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
               <div>Hello {username},</div>
               <div style="padding-top:30px"></div>
               <div>Your Account has been activated!</div>
               <div>Have a nice day!</div>
             <div style="padding-top:30px"></div>
             </div>
             <div style="background: #545454; color: #fff; padding: 20px 30px;">
             <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
             </div>
             </div>';
   $template['explanation'] = esc_html__( 'If new registered account needs to be approved, after Administrator approve member account he will be notified via Email.', 'ihc' );
           break;

         case 'admin_user_register':
           /// ADMIN - USER REGISTER
             $template['subject'] = '{blogname}: New Membership User registration';
             $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
               <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
             <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
               <div>Hello,</div>
               <div style="padding-top:30px"></div>
               <div>New Membership User registration on: <strong> {blogname} </strong></div>
               <div><strong> Username:</strong> {username}</div>
               <div><strong> Email:</strong> {user_email}</div>
               <div><strong> Membership:</strong> {level_name}</div>
               <div>Have a nice day!</div>
             <div style="padding-top:30px"></div>
             </div>
             <div style="background: #545454; color: #fff; padding: 20px 30px;">
             <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
             </div>
             </div>';
             $template['explanation'] = esc_html__( 'Notification is sent to Administrator first time when member Register on website.', 'ihc' );
           break;

         case 'admin_before_user_expire_level':
           /// ADMIN - Before Membership Expire
             $template['subject'] = '{blogname}: User Membership Expire';
             $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
               <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
             <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
               <div>Hello,</div>
               <div style="padding-top:30px"></div>
               <div>Subscription {current_level} for <strong> Username: {username}</strong> is going to expire on {current_level_expire_date}.</div>
               <div>Have a nice day!</div>
             <div style="padding-top:30px"></div>
             </div>
             <div style="background: #545454; color: #fff; padding: 20px 30px;">
             <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
             </div>
             </div>';
             $template['explanation'] = esc_html__( 'Reminder notification sent to Administator before member membership is going to expire. You can choose how many days before notification should be triggered. This process is handled by a WP Cron running daily.', 'ihc' );
           break;

         case 'admin_second_before_user_expire_level':
           /// ADMIN - Before Membership Expire
             $template['subject'] = '{blogname}: User Membership Expire';
             $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
               <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
             <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
               <div>Hello,</div>
               <div style="padding-top:30px"></div>
               <div>Subscription {current_level} for <strong> Username: {username}</strong> is going to expire on {current_level_expire_date}.</div>
               <div>Have a nice day!</div>
             <div style="padding-top:30px"></div>
             </div>
             <div style="background: #545454; color: #fff; padding: 20px 30px;">
             <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
             </div>
             </div>';
             $template['explanation'] = esc_html__( 'Second Reminder notification sent to Administator before member membership is going to expire. You can choose how many days before notification should be triggered. This process is handled by a WP Cron running daily.', 'ihc' );
           break;

         case 'admin_third_before_user_expire_level':
           /// ADMIN - Before Membership Expire
             $template['subject'] = '{blogname}: User Membership Expire';
             $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
               <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
             <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
               <div>Hello,</div>
               <div style="padding-top:30px"></div>
               <div>Subscription {current_level} for <strong> Username: {username}</strong> is going to expire on {current_level_expire_date}.</div>
               <div>Have a nice day!</div>
             <div style="padding-top:30px"></div>
             </div>
             <div style="background: #545454; color: #fff; padding: 20px 30px;">
             <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
             </div>
             </div>';
             $template['explanation'] = esc_html__( 'Third Reminder notification sent to Administator before member membership is going to expire. You can choose how many days before notification should be triggered. This process is handled by a WP Cron running daily.', 'ihc' );
           break;

         case 'admin_user_expire_level':
           /// ADMIN - After Membership Expired
             $template['subject'] = '{blogname}: User Membership Expired';
             $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
               <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
             <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
               <div>Hello,</div>
               <div style="padding-top:30px"></div>
               <div>Subscription {current_level} for<strong> Username: {username}</strong> has expired on {current_level_expire_date}.</div>
               <div style="padding-top:30px"></div>
               <div>Have a nice day!</div>
             <div style="padding-top:30px"></div>
             </div>
             <div style="background: #545454; color: #fff; padding: 20px 30px;">
             <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
             </div>
             </div>';
             $template['explanation'] = esc_html__( 'Notification is sent to Administrator after member membership has expired. This process is handled by a WP Cron running daily.', 'ihc' );
           break;

         case 'admin_user_payment':
           /// ADMIN - New Payment Completed
             $template['subject'] = '{blogname}: New Payment Completed';
             $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
               <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
             <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
               <div>Hello,</div>
               <div style="padding-top:30px"></div>
               <div><strong> User: {username}</strong> has completed a new payment.</div>
               <div>Have a nice day!</div>
             <div style="padding-top:30px"></div>
             </div>
             <div style="background: #545454; color: #fff; padding: 20px 30px;">
             <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
             </div>
             </div>';
             $template['explanation'] = esc_html__( 'Every time when a Payment confirmation is received and Order becomes Completed sent to Administrator', 'ihc' );
           break;
         case 'admin_user_profile_update':
           /// ADMIN - User Profile Update
             $template['subject'] = '{blogname}: User Update Profile';
             $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
               <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
             <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
               <div>Hello,</div>
               <div style="padding-top:30px"></div>
               <div><strong> User: {username}</strong> has updated his profile.</div>
               <div>Have a nice day!</div>
             <div style="padding-top:30px"></div>
             </div>
             <div style="background: #545454; color: #fff; padding: 20px 30px;">
             <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
             </div>
             </div>';
             $template['explanation'] = esc_html__( 'Notification is sent to Administrator everytime member update his Account details via Member Portal page ', 'ihc' );
           break;
         case 'register_lite_send_pass_to_user':
             $template['subject'] = '{blogname}: Your Password';
             $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
               <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
             <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
               <div>Hello {username},</div>
               <div style="padding-top:30px"></div>
               <div>Your password for {blogname} is {NEW_PASSWORD}</div>
               <div>Have a nice day!</div>
             <div style="padding-top:30px"></div>
             </div>
             <div style="background: #545454; color: #fff; padding: 20px 30px;">
             <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
             </div>
             </div>';
             $template['explanation'] = esc_html__( 'Notification is sent first time when member Register via Register Lite form or when password is not required during register step. A generated password is sent to member via Email.', 'ihc' );
           break;

         case 'ihc_cancel_subscription_notification-admin':
             $template['subject'] = '{blogname}: Subscription Canceled';
             $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
               <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
             <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
               <div>Hello,</div>
               <div style="padding-top:30px"></div>
               <div>{current_level} for {username} was canceled.</div>
               <div>Have a nice day!</div>
             <div style="padding-top:30px"></div>
             </div>
             <div style="background: #545454; color: #fff; padding: 20px 30px;">
             <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
             </div>
             </div>';
             $template['explanation'] = esc_html__( 'When Member cancels a Subscription Administrator is notified via Email.', 'ihc' );
           break;
         case 'ihc_delete_subscription_notification-admin':
             $template['subject'] = '{blogname}: Subscription Deleted';
             $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
               <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
             <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
               <div>Hello,</div>
               <div style="padding-top:30px"></div>
               <div>{current_level} for {username} was deleted.</div>
               <div>Have a nice day!</div>
             <div style="padding-top:30px"></div>
             </div>
             <div style="background: #545454; color: #fff; padding: 20px 30px;">
             <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
             </div>
             </div>';
             $template['explanation'] = esc_html__( 'When Member deletes a Subscription Administrator is notified via Email.', 'ihc' );
           break;
         case 'ihc_order_placed_notification-admin':
             $template['subject'] = '{blogname}: New Order placed';
             $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
               <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
             <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
               <div>Hello,</div>
               <div style="padding-top:30px"></div>
               <div>{username} has placed a new order.</div>
               <div>Have a nice day!</div>
             <div style="padding-top:30px"></div>
             </div>
             <div style="background: #545454; color: #fff; padding: 20px 30px;">
             <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
             </div>
             </div>';
             $template['explanation'] = esc_html__( 'When a new Order is placed into the system Administrator is notified via Email. Orders will initially have Pending status.', 'ihc' );
           break;
         case 'ihc_new_subscription_assign_notification-admin':
             $template['subject'] = '{blogname}: New Subscription assigned';
             $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
               <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
             <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
               <div>Hello,</div>
               <div style="padding-top:30px"></div>
               <div><strong>{username}</strong> subscribed on {current_level} Subscription.</div>
               <div>Have a nice day!</div>
             <div style="padding-top:30px"></div>
             </div>
             <div style="background: #545454; color: #fff; padding: 20px 30px;">
             <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
             </div>
             </div>';
             $template['explanation'] = esc_html__( 'Everytime when a new Subscription is assigned to member Administrator is notified. Subscription is set on Hold mode.', 'ihc' );
           break;
         case 'ihc_new_subscription_assign_notification':
               $template['subject'] = '{blogname}: New Subscription assigned to your Account';
               $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
                 <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
               <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
                 <div>Hello {username},</div>
                 <div style="padding-top:30px"></div>
                 <div>You have subscribed <strong>{current_level}</strong>.</div>
                 <div>Have a nice day!</div>
               <div style="padding-top:30px"></div>
               </div>
               <div style="background: #545454; color: #fff; padding: 20px 30px;">
               <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
               </div>
               </div>';
               $template['explanation'] = esc_html__( 'Everytime when a new Subscription is assigned Member is notified. Subscription is set on Hold mode.e', 'ihc' );
           break;
         case 'ihc_order_placed_notification-user':
             $template['subject'] = '{blogname}: New Order placed';
             $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
               <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
             <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
               <div>Hello {first_name} {last_name},</div>
               <div style="padding-top:30px"></div>
               <div>You just placed a new order on <strong> {blogname} </strong>.</div>
               <div>Have a nice day!</div>
             <div style="padding-top:30px"></div>
             </div>
             <div style="background: #545454; color: #fff; padding: 20px 30px;">
             <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
             </div>
             </div>';
             $template['explanation'] = esc_html__( 'When a new Order is placed into the system Member is notified via Email. Orders will initially have Pending status.', 'ihc' );
           break;
         case 'ihc_subscription_activated_notification':
             $template['subject'] = '{blogname}: Subscription Activated';
             $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
               <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
             <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
               <div>Hello {first_name} {last_name},</div>
               <div style="padding-top:30px"></div>
               <div>Your subscription on <strong> {blogname} </strong> just got activated.</div>
               <div>Have a nice day!</div>
             <div style="padding-top:30px"></div>
             </div>
             <div style="background: #545454; color: #fff; padding: 20px 30px;">
             <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
             </div>
             </div>';
             $template['explanation'] = esc_html__( 'Once a Subscription is Activated and receives a valid Expire Time, member is notified.', 'ihc' );
           break;
       case 'admin_user_subscription_first_time_activation':
           $template['subject'] = '{blogname}: Subscription Activated';
           $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
             <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
           <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
             <div>Hello,</div>
             <div style="padding-top:30px"></div>
             <div>Subscription for {username} has just got activated.</div>
             <div>Have a nice day!</div>
           <div style="padding-top:30px"></div>
           </div>
           <div style="background: #545454; color: #fff; padding: 20px 30px;">
           <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
           </div>
           </div>';
           $template['explanation'] = esc_html__( 'Administrator is notified when member Subscription is Activated first time.', 'ihc' );
             break;
         case 'ihc_delete_subscription_notification-user':
             $template['subject'] = '{blogname}: Subscription deleted';
             $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
               <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
             <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
               <div>Hello {username},</div>
               <div style="padding-top:30px"></div>
               <div>One of Your subscriptions on <strong> {blogname} </strong> was completely deleted.</div>
                 <div style="padding-top:30px"></div>
               <div>Have a nice day!</div>
             <div style="padding-top:30px"></div>
             </div>
             <div style="background: #545454; color: #fff; padding: 20px 30px;">
             <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
             </div>
             </div>';
             $template['explanation'] = esc_html__( 'When Member deletes a Subscription is notified via Email.', 'ihc' );
           break;
         case 'ihc_cancel_subscription_notification-user':
             $template['subject'] = '{blogname}: Subscription cancel';
             $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
               <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
             <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
               <div>Hello {username},</div>
               <div style="padding-top:30px"></div>
               <div>One of Your subscriptions on <strong> {blogname} </strong> was canceled.</div>
               <div>Have a nice day!</div>
             <div style="padding-top:30px"></div>
             </div>
             <div style="background: #545454; color: #fff; padding: 20px 30px;">
             <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
             </div>
             </div>';
             $template['explanation'] = esc_html__( 'When Member cancels a Subscription is notified via Email.', 'ihc' );
           break;
         case 'drip_content-user':
           $template['subject'] = '{blogname}: A new Post has become available';
             $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
               <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
             <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
               <div>Hello {username},,</div>
               <div style="padding-top:30px"></div>
               <div>A new Post has become available.</div>
               <div>Check this out: {POST_LINK}</div>
                 <div style="padding-top:30px"></div
               <div>Have a nice day!</div>
             <div style="padding-top:30px"></div>
             </div>
             <div style="background: #545454; color: #fff; padding: 20px 30px;">
             <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
             </div>
             </div>';
             $template['explanation'] = esc_html__( 'When Drip Content Notifications module is enabled, members will be notified everytime a post becomes available. This process is handled by a WP Cron running daily.', 'ihc' );
           break;
         case 'user_enter_grace_period':
           $template['subject'] = '{blogname}: Subscription enter in Grace Period';
           $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
             <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
           <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
             <div>Hello {first_name} {last_name},</div>
             <div style="padding-top:30px"></div>
             <div>Your <strong>{current_level}</strong> membership has just expired.</div>
             <div>But you can still access our website without any problem.</div>
             <div>If you want to renew/update your membership plan you can do that from Member Portal page.</div>
             <div style="padding-top:30px"></div>
             <div>Have a nice day!</div>
           <div style="padding-top:30px"></div>
           </div>
           <div style="background: #545454; color: #fff; padding: 20px 30px;">
           <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
           </div>
           </div>';
           $template['explanation'] = esc_html__( 'After a Subscription expired and if a Grace Period is available, Member is notified that his grace period started. This process is handled by a WP Cron running periodically.', 'ihc' );
           break;
         case 'admin_user_enter_grace_period':
             $template['subject'] = '{blogname}: Subscription enter in Grace Period for {username}';
             $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
               <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
             <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
               <div>Hello,</div>
               <div style="padding-top:30px"></div>
               <div>Customer <strong>{username}</strong> has entered in Grace Period for <strong>{current_level}</strong> membership.</div>
               <div>Have a nice day!</div>
             <div style="padding-top:30px"></div>
             </div>
             <div style="background: #545454; color: #fff; padding: 20px 30px;">
             <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
             </div>
             </div>';
             $template['explanation'] = esc_html__( 'After a Subscription expired and if a Grace Period is available, Administrator is notified that member grace period started. This process is handled by a WP Cron running periodically.', 'ihc' );
             break;
         case 'admin_user_subscription_renew':
           $template['subject'] = '{blogname}: Renewal Processed Successfully for {username}';
           $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
             <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
           <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
             <div>Hello,</div>
             <div style="padding-top:30px"></div>
             <div><strong>{current_level}</strong> subscription for <strong>{username}</strong> customer has been extended.</div>
             <div>Have a nice day!</div>
           <div style="padding-top:30px"></div>
           </div>
           <div style="background: #545454; color: #fff; padding: 20px 30px;">
           <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
           </div>
           </div>';
           $template['explanation'] = esc_html__( 'Administrator is notified when member Subscription is Renewed.', 'ihc' );
           break;
         case 'user_subscription_renew':
           $template['subject'] = '{blogname}: Renewal Processed Successfully!';
           $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
             <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
           <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
             <div>Hello {first_name} {last_name},</div>
             <div style="padding-top:30px"></div>
             <div>Your <strong>{current_level}</strong> subscription has been extended.</div>
             <div>This ensures you have continued access to valuable content.</div>
             <div>Have a nice day!</div>
           <div style="padding-top:30px"></div>
           </div>
           <div style="background: #545454; color: #fff; padding: 20px 30px;">
           <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
           </div>
           </div>';
           $template['explanation'] = esc_html__( 'Member is notified when his Subscription is Renewed.', 'ihc' );
           break;
         case 'admin_user_login':
           $template['subject'] = '{blogname}: User {username} Login alert';
           $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
             <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
           <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
             <div>Hello,</div>
             <div style="padding-top:30px"></div>
             <div>Customer <strong>{username}</strong> just logged into your Website.</div>
             <div>Have a nice day!</div>
           <div style="padding-top:30px"></div>
           </div>
           <div style="background: #545454; color: #fff; padding: 20px 30px;">
           <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
           </div>
           </div>';
           $template['explanation'] = esc_html__( 'Administrator is notified everytime a member logs into current Website.', 'ihc' );
           break;
         case 'admin_before_subscription_payment_due':
           $template['subject'] = '{blogname}: Payment due for {username} soon';
           $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
             <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
           <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
             <div>Hello,</div>
             <div style="padding-top:30px"></div>

             <div>Customer <strong>{username}</strong> will be automtically charged soon.</div>
             <div>Have a nice day!</div>
           <div style="padding-top:30px"></div>
           </div>
           <div style="background: #545454; color: #fff; padding: 20px 30px;">
           <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
           </div>
           </div>';
           $template['explanation'] = esc_html__( 'Before a member is gonna be charged, Administrator is notified via Email with several days before. This process is handled by a WP Cron running daily.', 'ihc' );
           break;
         case 'user_before_subscription_payment_due':
             $template['subject'] = '{blogname}: Your Renewal payment for {current_level}';
             $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
               <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
             <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
               <div>Hello {first_name} {last_name},</div>
               <div style="padding-top:30px"></div>
               <div>We’re emailing to let you know that your renewal payment for {current_level} will be automatically processed soon.</div>
               <div style="padding-top:30px"></div>
               <div>If you are no longer using {current_level} and wish to cancel your subscription, you may do so at anytime from your Member Portal page before your renewal is processed.</div>
               <div style="padding-top:30px"></div>
               <div>Have a nice day!</div>
             <div style="padding-top:30px"></div>
             </div>
             <div style="background: #545454; color: #fff; padding: 20px 30px;">
             <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
             </div>
             </div>
   ';
             $template['explanation'] = esc_html__( 'Before a he is gonna be charged, Member is notified via Email with several days before. This process is handled by a WP Cron running daily.', 'ihc' );
           break;
         case 'user_subscription_trial_expired':
           $template['subject'] = '{blogname}: Your Trial period for  {current_level} has Finished';
           $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
             <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
           <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
             <div>Hello {first_name} {last_name},</div>
             <div style="padding-top:30px"></div>
             <div>Trial period for <strong>{current_level}</strong> has finished.</div>
             <div>Have a nice day!</div>
           <div style="padding-top:30px"></div>
           </div>
           <div style="background: #545454; color: #fff; padding: 20px 30px;">
           <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
           </div>
           </div>';
           $template['explanation'] = esc_html__( 'If Member has any Subscription with Trial and his trial period finished he will be notified. This process is handled by a WP Cron running periodically.', 'ihc' );
           break;
         case 'admin_user_subscription_trial_expired':
           $template['subject'] = '{blogname}: Subscription Trial period for {username} has Finished';
           $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
             <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
           <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
             <div>Hello,</div>
             <div style="padding-top:30px"></div>
             <div>Customer <strong>{username}</strong> has finished Trial Period for <strong>{current_level}</strong> Subscription.</div>
             <div style="padding-top:30px"></div>
             <div>Have a nice day!</div>
           <div style="padding-top:30px"></div>
           </div>
           <div style="background: #545454; color: #fff; padding: 20px 30px;">
           <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
           </div>
           </div>';
           $template['explanation'] = esc_html__( 'If Member has any Subscription with Trial and his trial period finished ADministrator will be notified. This process is handled by a WP Cron running periodically.', 'ihc' );
           break;
         case 'upcoming_card_expiry_reminder':
             $template['subject'] = '{blogname}: Upcoming Card Expiry Reminder';
             $template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
               <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
             <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
               <div>Hello {first_name} {last_name},</div>
               <div style="padding-top:30px"></div>
               <div>Your card will expire soon.</div>
               <div style="padding-top:30px"></div>
               <div>Important! The card that you used to purchase Subscription is due to expire soon. If you don’t update your card details before this date, you may lose access to the services you have paid for.</div>
               <div style="padding-top:30px"></div>
               <div>Change your Card from your Account. If your card details are up-to-date, your service will not be interrupted.</div>
               <div>Have a nice day!</div>
             <div style="padding-top:30px"></div>
             </div>
             <div style="background: #545454; color: #fff; padding: 20px 30px;">
             <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
             </div>
             </div>
   ';
             $template['explanation'] = esc_html__( 'For Stripe Connect Payment Service only: Member is notified via Email with several days before an assigned card to his Subscription is gonna to expire. This process is handled by a WP Cron running daily.', 'ihc' );
           break;
       }
       $template = apply_filters( 'ihc_admin_filter_notification_template', $template, $type );
       return $template;
    }

    public function getNotificationRuntime()
    {
      $runTime = [
          //Register Process
          'admin_user_register'                               => 'Instant', // - Admin Notification

          //Subscriptions
          'ihc_new_subscription_assign_notification-admin'    => 'Instant',
          'admin_user_subscription_first_time_activation'		  => 'Instant',
          'admin_user_subscription_renew'			                => 'Instant',
          'admin_user_subscription_trial_expired'			        => 'Every 12 hours',
          'admin_before_user_expire_level'                    => 'Daily',
          'admin_second_before_user_expire_level'             => 'Daily',
          'admin_third_before_user_expire_level'              => 'Daily',
          'admin_user_enter_grace_period'			                => 'Every 12 hours',
          'admin_user_expire_level'                           => 'Daily',

          //Payments
          'ihc_order_placed_notification-admin'               => 'Instant',
          'admin_user_payment'                                => 'Instant',
          'admin_before_subscription_payment_due'				      => 'Daily',

          //Customer Actions
          'admin_user_profile_update'                         => 'Instant',
          'admin_user_login'						                      => 'Instant',
          'ihc_cancel_subscription_notification-admin'        => 'Instant',
          'ihc_delete_subscription_notification-admin'        => 'Instant',


          //----------Users Notifications----------

          //Register Process
          'register'                                          => 'Instant', //Register
          'review_request'                                    => 'Instant', //register with pending

          //Register Lite
          'register_lite_send_pass_to_user'                   => 'Instant',

          //Double Email Verification
          'email_check'                                       => 'Instant',
          'email_check_success'                               => 'Instant',

          //Reset Password Process
          'reset_password_process'                            => 'Instant',
          'reset_password'                                    => 'Instant',
          'change_password'                                   => 'Instant',

          //Customer Account
          'approve_account'                                   => 'Instant',
          'delete_account'                                    => 'Instant',

          //Custom Actions
          'user_update' => 'Instant',
          'ihc_cancel_subscription_notification-user'         => 'Instant',
          'ihc_delete_subscription_notification-user'         => 'Instant',

          //Subscriptions
          'ihc_new_subscription_assign_notification'          => 'Instant',
          'ihc_subscription_activated_notification'           => 'Instant',
          'user_subscription_renew'			                      => 'Instant',
          'user_subscription_trial_expired'			              => 'Every 12 hours',
          'before_expire'                                     => 'Daily',
          'second_before_expire'                              => 'Daily',
          'third_before_expire'                               => 'Daily',
          'user_enter_grace_period'			                      => 'Every 12 hours',
          'expire'                                            => 'Daily',

          //Payments
          'ihc_order_placed_notification-user'                => 'Instant',
          'payment'                                           => 'Instant',
          'bank_transfer'                                     => 'Instant',
          'user_before_subscription_payment_due'				      => 'Daily',

          //Stripe Connect
          'upcoming_card_expiry_reminder'				               => 'Daily',
          //Drip Content
          'drip_content-user'                                 => 'Daily',
        ];

        $runTime = apply_filters( 'ihc_filter_notifications_runtime_listing', $runTime );
        return $runTime;

    }

    public function getAllNotifications()
    {
        global $wpdb;
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "SELECT id,notification_type,level_id,subject,message,pushover_message,pushover_status,status FROM {$wpdb->prefix}ihc_notifications;";
        $data = $wpdb->get_results( $query );
        return $data;
    }

    public function getMany( $search='', $offset=0, $limit=0, $orderBy='id', $orderType='ASC')
    {
        global $wpdb;
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "SELECT id,notification_type,level_id,subject,message,pushover_message,pushover_status,status FROM {$wpdb->prefix}ihc_notifications WHERE 1=1 ";

        if ( $search !== '' ){
            $query .= $wpdb->prepare( " AND ( subject LIKE %s OR message LIKE %s ) ", '%' . $search . '%', '%' . $search . '%' );
        }
        if ( $orderBy !== '' && $orderType !== '' ){
            $query .= $wpdb->prepare( " ORDER BY %1s %1s ", $orderBy, $orderType);
        }
        if ( $limit > 0 ){
            $query .= $wpdb->prepare( " LIMIT %d OFFSET %d; ", $limit, $offset );
        }
        $data = $wpdb->get_results( $query );
        return $data;
    }

    public function countWithFilter( $search='')
    {
        global $wpdb;
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "SELECT COUNT(id) FROM {$wpdb->prefix}ihc_notifications WHERE 1=1 ";

        if ( $search !== '' ){
            $query .= $wpdb->prepare( " AND ( subject LIKE %s OR message LIKE %s ) ", '%' . $search . '%', '%' . $search . '%' );
        }
        $data = $wpdb->get_var( $query );
        return $data;
    }

    public function deleteOne( $id=0 )
    {
        global $wpdb;
        $q = $wpdb->prepare("DELETE FROM {$wpdb->prefix}ihc_notifications WHERE id=%d ", $id);
        $wpdb->query($q);
    }

    public function save( $post_data=array() )
    {
        global $wpdb;

        if(!isset($post_data['level_id'])){
           $post_data['level_id'] = -1;
        }

        if (isset($post_data['notification_id'])){
        //update
        $q = $wpdb->prepare("UPDATE {$wpdb->prefix}ihc_notifications
                SET notification_type=%s,
                level_id=%s,
                subject=%s,
                message=%s,
                pushover_message=%s,
                pushover_status=%s
                WHERE id=%d
        ", $post_data['notification_type'], $post_data['level_id'], stripslashes_deep($post_data['subject']),
        stripslashes_deep($post_data['message']), $post_data['pushover_message'], $post_data['pushover_status'],
        $post_data['notification_id']);
        $wpdb->query($q);
        } else {
        //create
        $q = $wpdb->prepare("INSERT INTO {$wpdb->prefix}ihc_notifications
                                VALUES(null, %s, %d, %s, %s, %s, %s, '1')",
                                $post_data['notification_type'], $post_data['level_id'], stripslashes_deep($post_data['subject']), stripslashes_deep($post_data['message']),
                                $post_data['pushover_message'], $post_data['pushover_status']
        );
        $wpdb->query($q);
        }
        do_action( 'ihc_save_notification_action', $post_data );
    }

    /**
     * @param string
     * @return array
     */
    public static function getOneByType( $type='' )
    {
        global $wpdb;
        if ( $type == '' ){
            return [];
        }
        $query = $wpdb->prepare( "SELECT id,notification_type,level_id,subject,message,pushover_message,pushover_status,status
                                                      FROM {$wpdb->prefix}ihc_notifications
                                                      WHERE
                                                      notification_type=%s
                                                      ORDER BY id DESC LIMIT 1;", $type );
        $data = $wpdb->get_row( $query );
        if ( !$data ){
            return [];
        }
        return (array)$data;
    }

    public function sendTestNotification( $notificationId=0, $email="" )
    {
        global $wpdb;
        if ( $notificationId === 0 || $email === '' ){
            return;
        }
        $q = $wpdb->prepare( "SELECT id,notification_type,level_id,subject,message,pushover_message,pushover_status,status FROM " . $wpdb->prefix . "ihc_notifications
                    WHERE 1=1
                    AND id=%s;", $notificationId );
        $data = $wpdb->get_row($q);

        if ( $data ){
          $subject = (isset($data->subject)) ? $data->subject : '';
          $message = (isset($data->message)) ? $data->message : '';
        }
        $message = stripslashes( htmlspecialchars_decode( ihc_format_str_like_wp( $message ) ) );

        $message = "<html><head></head><body>" . $message . "</body></html>";
        if ( $subject == '' && $message == '' ){
            return false;
        }

        $fromName = $this->getFromName();
        $fromEmail = $this->getFromEmail();

        if ( !empty( $fromEmail ) && !empty( $fromName ) ){
          $headers[] = "From: $fromName <$fromEmail>";
        }
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $sent = wp_mail( $email, $subject, $message, $headers );
        return $sent;
    }

    /**
     * @param int
     * @param int
     * @return bool
     */
    public function updateStatus( $id=0, $status=0 )
    {
        global $wpdb;
        $query = $wpdb->prepare( "UPDATE {$wpdb->prefix}ihc_notifications SET status=%d WHERE id=%d;", $status, $id );
        return $wpdb->query( $query );
    }

}
