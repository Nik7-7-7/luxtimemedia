<?php
namespace Indeed\Ihc;

class NotificationTriggers
{
    /**
     * @param none
     * @return none
     */
    public function __construct()
    {
        // triggers
        add_action( 'ihc_delete_user_action', [ $this, 'deleteAccount' ], 1, 1 );
        add_action( 'ihc_action_approve_user_email', [ $this, 'approveUserEmail' ], 1, 1 );
        add_action( 'ihc_bank_transfer_charge', [ $this, 'bankTransferCharge'] , 1, 1 );
        add_action( 'ihc_payment_completed', [ $this, 'paymentCompleted' ], 1, 4 );
        add_action( 'ihc_action_after_order_placed', [ $this, 'newSubscription'], 1, 3 );// using ihc_action_after_order_placed hook since version 12.0. prev was ihc_new_subscription_action with 2 params instead of 3
        add_action( 'ihc_action_after_order_placed', [ $this, 'orderPlaced' ], 1, 3 );
        add_action( 'ihc_action_after_subscription_delete', [ $this, 'deleteSubscription' ], 1, 2);
        add_action( 'ihc_action_after_cancel_subscription', [ $this, 'cancelSubscription' ], 1, 2 );
        add_action( 'ihc_action_after_subscription_activated', [ $this, 'afterSubscriptionActivation' ], 1, 4 );
        add_filter( 'ihc_filter_reset_password_process', [ $this, 'resetPasswordProcess' ], 1, 3 );
        add_filter( 'ihc_filter_reset_password', [ $this, 'resetPassword' ], 1, 3 );
        add_action( 'ihc_action_user_activation_email_check_success', [ $this, 'emailCheckSuccess' ], 1, 1 );
        add_action( 'ihc_action_double_email_verification', [ $this, 'doubleEmailVerification' ], 1, 3 );
        add_action( 'ihc_action_approve_user_account', [ $this, 'approveUserAccount' ], 1, 1 );
        //add_filter( 'send_password_change_email', [ $this, 'sendPasswordChangeEmail'], 99, 2);
        add_filter( 'ihc_action_password_has_been_reset', [ $this, 'sendPasswordChangeEmail'], 99, 1);
        add_filter( 'ihc_register_lite_action', [ $this, 'registerLitePassword' ], 99, 2 );
        add_action( 'ihc_action_create_user_review_request', [ $this, 'reviewRequest' ], 1, 2 );
        add_action( 'ihc_action_create_user_register', [ $this, 'register' ], 1, 2 );
        add_action( 'ihc_action_update_user', [ $this, 'updateUser' ], 1, 1 );
        add_filter( 'ihc_filter_notification_before_expire', [ $this, 'beforeSubscriptionExpire' ], 1, 4 );

        // grace period
        add_action( 'ihc_action_subscription_enter_grace_period', [ $this, 'userEnterGracePeriod'], 99, 3 );
        // renew subscription
        add_action( 'ihc_action_after_subscription_activated', [ $this, 'renewSubscription' ], 99, 4 );
        // on trial ends
        add_action( 'ihc_action_subscription_trial_expired', [ $this, 'onTrialExpire' ], 999, 2 );
        // user login
        add_action( 'ihc_do_action_on_login', [ $this, 'onLogin' ], 999, 1 );
        // Before Subscription Payment due
        add_action( 'ihc_action_subscription_payment_due', [ $this, 'beforeSubscriptionPaymentDue'], 999, 2 );

        // Upcoming Card Expiry Reminders
        add_action( 'ihc_action_card_expire_reminder', [ $this, 'beforeCardExpire'], 999, 2 );
    }

    /**
     * @param int
     * @return none
     */
    public function deleteAccount( $id=0 )
    {
        $notification = new \Indeed\Ihc\Notifications();
        $notification->setUid( $id )
                     ->setLid( '' )
                     ->setType( 'delete_account' )
                     ->setMessageVariables( [] )
                     ->send();
    }

    /**
     * @param int
     * @return none
     */
    public function approveUserEmail( $uid=0 )
    {
        $notification = new \Indeed\Ihc\Notifications();
        $notification->setUid( $uid )
                     ->setLid( '' )
                     ->setType( 'email_check_success' )
                     ->setMessageVariables( [] )
                     ->send();
    }

    /**
     * @param int
     * @return none
     */
    public function bankTransferCharge( $args=[] )
    {
        if (isset($args['order_id'])){
          $args['amount'] = \Ihc_Db::getOrderAmount($args['order_id']);
          // since 12.2
          if ( $args['amount'] !== false && $args['amount'] !== null && $args['amount'] !== 0 ){
              $args['amount'] = ihc_format_price_and_currency( '', $args['amount'] );
          }
        }
        $notification = new \Indeed\Ihc\Notifications();
        $notification->setUid( $args['uid'] )
                     ->setLid( $args['lid'] )
                     ->setType( 'bank_transfer' )
                     ->setMessageVariables( $args )
                     ->send();
    }

    /**
     * @param int
     * @param int
     * @param array
     * @param int
     * @return none
     */
    public function paymentCompleted( $uid=0, $lid=0, $levelData=[], $orderId=0 )
    {
          $args = [];

          // since 12.5
          /*
          if ( !empty( $orderId ) ){
             $args['amount'] = \Ihc_Db::getOrderAmount( $orderId );
             if ( $args['amount'] !== false && $args['amount'] !== null && $args['amount'] !== 0 ){
                 $args['amount'] = ihc_format_price_and_currency( '', $args['amount'] );
             }
          }
          */
          // end of 12.5

          $notification = new \Indeed\Ihc\Notifications();
          $notification->setUid( $uid )
                       ->setLid( $lid )
                       ->setType( 'payment' )
                       ->setMessageVariables( $args )
                       ->send();
          $notification = new \Indeed\Ihc\Notifications();
          $notification->setUid( $uid )
                       ->setLid( $lid )
                       ->setType( 'admin_user_payment' )
                       ->setMessageVariables( $args )
                       ->send();
    }

    /**
     * @param int
     * @param int
     * @return none
     */
    public function newSubscription( $uid=0, $lid=0, $orderId=0 )
    {
         $args = [];
         if (!empty($orderId)){
            $args['amount'] = \Ihc_Db::getOrderAmount($orderId);
            // since 12.2
            if ( $args['amount'] !== false && $args['amount'] !== null && $args['amount'] !== 0 ){
                $args['amount'] = ihc_format_price_and_currency( '', $args['amount'] );
            }
         }
         $notification = new \Indeed\Ihc\Notifications();
         $notification->setUid( $uid )
                       ->setLid( $lid )
                       ->setType( 'ihc_new_subscription_assign_notification-admin' )
                       ->setMessageVariables( $args )
                       ->send();
         $notification = new \Indeed\Ihc\Notifications();
         $notification->setUid( $uid )
                      ->setLid( $lid )
                      ->setType( 'ihc_new_subscription_assign_notification' )
                      ->setMessageVariables( $args )
                      ->send();
    }

    /**
     * @param int
     * @param int
     * @return none
     */
    public function orderPlaced( $uid=0, $lid=0, $orderId=0 )
    {
        $args = [];
        if (!empty($orderId)){
            $args['amount'] = \Ihc_Db::getOrderAmount($orderId);
            // since 12.2
            if ( $args['amount'] !== false && $args['amount'] !== null && $args['amount'] !== 0 ){
                $args['amount'] = ihc_format_price_and_currency( '', $args['amount'] );
            }
        }
        $notification = new \Indeed\Ihc\Notifications();
        $notification->setUid( $uid )
                      ->setLid( $lid )
                      ->setType( 'ihc_order_placed_notification-user' )
                      ->setMessageVariables( $args )
                      ->send();
        $notification = new \Indeed\Ihc\Notifications();
        $notification->setUid( $uid )
                     ->setLid( $lid )
                     ->setType( 'ihc_order_placed_notification-admin' )
                     ->setMessageVariables( $args )
                     ->send();
    }

    /**
     * @param int
     * @param int
     * @return none
     */
    public function deleteSubscription($uid=0, $lid=0)
    {
        $notification = new \Indeed\Ihc\Notifications();
        $notification->setUid( $uid )
                     ->setLid( $lid )
                     ->setType( 'ihc_delete_subscription_notification-user' )
                     ->setMessageVariables( [] )
                     ->send();
        $notification = new \Indeed\Ihc\Notifications();
        $notification->setUid( $uid )
                     ->setLid( $lid )
                     ->setType( 'ihc_delete_subscription_notification-admin' )
                     ->setMessageVariables( [] )
                     ->send();
    }

    /**
     * @param int
     * @param int
     * @return none
     */
    public function cancelSubscription( $uid=0, $lid=0 )
    {
       $notification = new \Indeed\Ihc\Notifications();
       $notification->setUid( $uid )
                    ->setLid( $lid )
                    ->setType( 'ihc_cancel_subscription_notification-user' )
                    ->setMessageVariables( [] )
                    ->send();
       $notification = new \Indeed\Ihc\Notifications();
       $notification->setUid( $uid )
                    ->setLid( $lid )
                    ->setType( 'ihc_cancel_subscription_notification-admin' )
                    ->setMessageVariables( [] )
                    ->send();
    }

    /**
     * @param int
     * @param int
     * @return none
     */
    public function afterSubscriptionActivation( $uid=0, $lid=0, $firstTime=false, $args=[] )
    {
        if ( !$firstTime ){
            return false;
        }
        $notification = new \Indeed\Ihc\Notifications();
        $notification->setUid( $uid )
                     ->setLid( $lid )
                     ->setType( 'ihc_subscription_activated_notification' )
                     ->setMessageVariables( $args )
                     ->send();
        $notification = new \Indeed\Ihc\Notifications();
        $notification->setUid( $uid )
                     ->setLid( $lid )
                     ->setType( 'admin_user_subscription_first_time_activation' )
                     ->setMessageVariables( $args )
                     ->send();
    }

    /**
     * @param int
     * @param int
     * @param array
     * @return bool
     */
    public function resetPasswordProcess( $sent=false, $uid=0, $args=[] )
    {
        $notification = new \Indeed\Ihc\Notifications();
        $sent = $notification->setUid( $uid )
                             ->setLid( '' )
                             ->setType( 'reset_password_process' )
                             ->setMessageVariables( $args )
                             ->send();
        return $sent;
    }

    /**
     * @param int
     * @param int
     * @param array
     * @return bool
     */
    public function resetPassword( $sent=false, $uid=0, $args=[] )
    {
        $notification = new \Indeed\Ihc\Notifications();
        $sent = $notification->setUid( $uid )
                             ->setLid( '' )
                             ->setType( 'reset_password' )
                             ->setMessageVariables( $args )
                             ->send();
        return $sent;
    }

    /**
     * @param int
     * @return none
     */
    public function emailCheckSuccess( $uid=0 )
    {
       $notification = new \Indeed\Ihc\Notifications();
       $notification->setUid( $uid )
                           ->setLid( '' )
                           ->setType( 'email_check_success' )
                           ->setMessageVariables( [] )
                           ->send();
    }

    /**
     * @param int
     * @param int
     * @param array
     * @return none
     */
    public function doubleEmailVerification( $uid=0, $lid=0, $args=[] )
    {
        $notification = new \Indeed\Ihc\Notifications();
        $notification->setUid( $uid )
                     ->setLid( $lid )
                     ->setType( 'email_check' )
                     ->setMessageVariables( $args )
                     ->send();
    }

    /**
     * @param int
     * @return none
     */
    public function approveUserAccount( $uid=0 )
    {
        $notification = new \Indeed\Ihc\Notifications();
        $notification->setUid( $uid )
                     ->setLid( '' )
                     ->setType( 'approve_account' )
                     ->setMessageVariables( [] )
                     ->send();
    }

    /**
     * @param bool
     * @param array
     * @return none
     */
    public function sendPasswordChangeEmail( $uid=0 )
    {
        if ( $uid === 0 ){
            return false;
        }
        $notification = new \Indeed\Ihc\Notifications();
        $sentMail = $notification->setUid( $uid )
                                ->setLid( '' )
                                ->setType( 'change_password' )
                                ->setMessageVariables( [] )
                                ->send();
      /*
      if ( isset($userData['ID']) && $return ){
        $notification = new \Indeed\Ihc\Notifications();
        $sentMail = $notification->setUid( $userData['ID'] )
                                ->setLid( '' )
                                ->setType( 'change_password' )
                                ->setMessageVariables( [] )
                                ->send();
        if ( $sentMail ){
          return false;
        }
      }
      return $return;
      */
    }

    /**
     * @param int
     * @param array
     * @return none
     */
    public function registerLitePassword( $uid=0, $args=[] )
    {
      $notification = new \Indeed\Ihc\Notifications();
      $sentMail = $notification->setUid( $uid )
                               ->setLid( '' )
                               ->setType( 'register_lite_send_pass_to_user' )
                               ->setMessageVariables( $args )
                               ->send();
    }

    /**
     * @param int
     * @param array
     * @return none
     */
    public function reviewRequest( $uid=0, $lid=0 )
    {
        $notification = new \Indeed\Ihc\Notifications();
        $sentMail = $notification->setUid( $uid )
                                 ->setLid( $lid )
                                 ->setType( 'review_request' )
                                 ->setMessageVariables( [] )
                                 ->send();

        $notification = new \Indeed\Ihc\Notifications();
        $sentMail = $notification->setUid( $uid )
                                 ->setLid( $lid )
                                 ->setType( 'admin_user_register' )
                                 ->setMessageVariables( [] )
                                 ->send();
    }

    /**
     * @param int
     * @param array
     * @return none
     */
    public function register( $uid=0, $lid=0 )
    {
        $notification = new \Indeed\Ihc\Notifications();
        $sentMail = $notification->setUid( $uid )
                                 ->setLid( $lid )
                                 ->setType( 'register' )
                                 ->setMessageVariables( [] )
                                 ->send();
        $notification = new \Indeed\Ihc\Notifications();
        $sentMail = $notification->setUid( $uid )
                                 ->setLid( $lid )
                                 ->setType( 'admin_user_register' )
                                 ->setMessageVariables( [] )
                                 ->send();
    }

    /**
     * @param int
     * @return none
     */
    public function updateUser( $uid=0 )
    {
        $notification = new \Indeed\Ihc\Notifications();
        $sentMail = $notification->setUid( $uid )
                                 ->setLid( '' )
                                 ->setType( 'user_update' )
                                 ->setMessageVariables( [] )
                                 ->send();
        $notification = new \Indeed\Ihc\Notifications();
        $sentMail = $notification->setUid( $uid )
                                 ->setLid( '' )
                                 ->setType( 'admin_user_profile_update' )
                                 ->setMessageVariables( [] )
                                 ->send();
    }

    /**
     * @param bool
     * @param int
     * @param int
     * @param string
     * @return none
     */
    public function beforeSubscriptionExpire( $sent=false, $uid=0, $lid=0, $type='' )
    {
        $notification = new \Indeed\Ihc\Notifications();
        $sent = $notification->setUid( $uid )
                             ->setLid( $lid )
                             ->setType( $type )
                             ->setMessageVariables( [] )
                             ->send();
        return $sent;
    }

    /**
     * @param int
     * @param int
     * @return none
     */
    public function userEnterGracePeriod( $uid=0, $lid=0, $subscriptionData=[] )
    {
        $notification = new \Indeed\Ihc\Notifications();
        $sent = $notification->setUid( $uid )
                             ->setLid( $lid )
                             ->setType( 'admin_user_enter_grace_period' )
                             ->setMessageVariables( [] )
                             ->send();
        $notification = new \Indeed\Ihc\Notifications();
        $sent = $notification->setUid( $uid )
                             ->setLid( $lid )
                             ->setType( 'user_enter_grace_period' )
                             ->setMessageVariables( [] )
                             ->send();
    }


    /**
     * @param int
     * @param int
     * @param array
     * @param string
     * @return none
     */
    public function renewSubscription( $uid=0, $lid=0, $firstTime=false, $args=[] )
    {
        if ( $firstTime ){
            return false;
        }
        $notification = new \Indeed\Ihc\Notifications();
        $sent = $notification->setUid( $uid )
                             ->setLid( $lid )
                             ->setType( 'admin_user_subscription_renew' )
                             ->setMessageVariables( [] )
                             ->send();
        $notification = new \Indeed\Ihc\Notifications();
        $sent = $notification->setUid( $uid )
                             ->setLid( $lid )
                             ->setType( 'user_subscription_renew' )
                             ->setMessageVariables( [] )
                             ->send();
    }

    /**
     * @param int
     * @param int
     * @return none
     */
    public function onTrialExpire( $uid=0, $lid=0 )
    {
        $notification = new \Indeed\Ihc\Notifications();
        $sent = $notification->setUid( $uid )
                             ->setLid( $lid )
                             ->setType( 'admin_user_subscription_trial_expired' )
                             ->setMessageVariables( [] )
                             ->send();
        $notification = new \Indeed\Ihc\Notifications();
        $sent = $notification->setUid( $uid )
                             ->setLid( $lid )
                             ->setType( 'user_subscription_trial_expired' )
                             ->setMessageVariables( [] )
                             ->send();
    }

    /**
     * @param int
     * @return string
     */
    public function onLogin( $uid=0 )
    {
        $notification = new \Indeed\Ihc\Notifications();
        $sent = $notification->setUid( $uid )
                             ->setLid( '' )
                             ->setType( 'admin_user_login' )
                             ->setMessageVariables( [] )
                             ->send();
        return $sent;
    }

    /**
     * @param int
     * @param int
     * @return string
     */
    public function beforeSubscriptionPaymentDue( $uid=0, $lid=0 )
    {
        $notification = new \Indeed\Ihc\Notifications();
        $sent = $notification->setUid( $uid )
                             ->setLid( $lid )
                             ->setType( 'admin_before_subscription_payment_due' )
                             ->setMessageVariables( [] )
                             ->send();
        $notification = new \Indeed\Ihc\Notifications();
        $sent = $notification->setUid( $uid )
                             ->setLid( $lid )
                             ->setType( 'user_before_subscription_payment_due' )
                             ->setMessageVariables( [] )
                             ->send();
    }

    /**
     * @param int
     * @param int
     * @return string
     */
    public function beforeCardExpire( $uid=0, $lid=0 )
    {
        $notification = new \Indeed\Ihc\Notifications();
        $sent = $notification->setUid( $uid )
                             ->setLid( $lid )
                             ->setType( 'upcoming_card_expiry_reminder' )
                             ->setMessageVariables( [] )
                             ->send();

    }

}
