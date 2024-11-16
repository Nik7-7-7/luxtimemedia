<?php
namespace Indeed\Ihc;

class UserSubscriptionsEvents
{
    /**
     * @param none
     * @return none
     */
    public function __construct()
    {
        add_action( 'ihc_before_new_subscription_action', [ $this, 'newSubscriptionBefore' ], 999, 3 );
        add_action( 'ihc_new_subscription_action', [ $this, 'newSubscription' ], 999, 3 );
        add_action( 'ihc_new_subscription_action', [ $this, 'newSubscriptionSaveMetas' ], 999, 3 );

        add_action( 'ihc_action_before_subscription_activated', [ $this, 'subscriptionActivatedBefore' ], 996, 4 );
        add_action( 'ihc_action_after_subscription_activated', [ $this, 'subscriptionActivatedAfter' ], 997, 4 );
        add_action( 'ihc_action_after_subscription_first_time_activated', [ $this, 'subscriptionActivatedFirstTime' ], 998, 3 );
        add_action( 'ihc_action_after_subscription_renew_activated', [ $this, 'subscriptionActivatedRenew' ], 999, 4 );

        add_action( 'ihc_action_before_delete_user_subscription', [ $this, 'deleteUserSubscriptionBefore' ], 999, 2 );
        add_action( 'ihc_action_before_delete_all_user_subscription', [ $this, 'deleteAllUserSubscriptionsBefore' ], 999, 1 );

        add_action( 'ihc_action_subscription_expired', [ $this, 'expriredSubscription' ], 999, 2 );

        add_action( 'ihc_action_after_cancel_subscription', [ $this, 'canceledSubscription' ], 999, 2 );

        add_action( 'ihc_action_after_subscription_delete', [ $this, 'deleteUserSubscriptionAfter' ], 999, 2 );

        add_action( 'ihc_action_after_subscription_first_time_activated', [ $this, 'saveUserSubscriptionMetaTrial' ], 999, 3 );
    }

    /**
     * @param int
     * @param int
     * @param array
     * @return none
     */
    public function newSubscription( $uid=0, $lid=0, $args=[] )
    {

    }

    /**
     * Fires when a subscription is added on a member.
     * @param int
     * @param int
     * @param array
     * @return none
     */
    public function newSubscriptionSaveMetas( $uid=0, $lid=0, $args=[] )
    {
        if ( empty( $args['subscription_id'] ) ){
            return false;
        }
        $membershipData = \Indeed\Ihc\Db\Memberships::getOne( $lid );

        if ( !$membershipData ){
            return false;
        }

        foreach ( $membershipData as $key => $membershipMetaData ){
            \Indeed\Ihc\Db\UserSubscriptionsMeta::save( $args['subscription_id'], $key, $membershipMetaData );
        }
        if ( $membershipData['access_type'] ==	'regular_period' ){
            \Indeed\Ihc\Db\UserSubscriptionsMeta::save( $args['subscription_id'], 'recurring_cycles_count', 0 );
        }

        // grace time
        $gracePeriod = \Indeed\Ihc\Db\Memberships::getMembershipGracePeriod( $lid );
        if ( !$gracePeriod ){
            return;
        }
        \Indeed\Ihc\Db\UserSubscriptionsMeta::save( $args['subscription_id'], 'grace_period', $gracePeriod );
    }

    /**
     * @param int
     * @param int
     * @param array
     * @return none
     */
    public function newSubscriptionBefore( $uid=0, $lid=0, $args=[] )
    {

    }

    /**
     * @param int
     * @param int
     * @param bool
     * @param array
     * @return none
     */
    public function subscriptionActivatedBefore( $uid=0, $lid=0, $firstTime=false, $args=[] )
    {

    }

    /**
     * @param int
     * @param int
     * @param bool
     * @param array
     * @return none
     */
    public function subscriptionActivatedAfter( $uid=0, $lid=0, $firstTime=false, $args=[] )
    {


        /// give a gift
       	if (ihc_is_magic_feat_active('gifts')){
       		 require_once IHC_PATH . 'classes/Ihc_Gifts.class.php';
       		 $gift_object = new \Ihc_Gifts($uid, $lid);
       	}

        // if it's an recurring subscription, we increment the meta.
        $subscriptionId = \Indeed\Ihc\UserSubscriptions::getIdForUserSubscription( $uid, $lid );
        if ( $subscriptionId ){
            $billingType = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionId, 'access_type' );
            if ( $billingType ==	'regular_period' ){
                $current = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionId, 'recurring_cycles_count' );
        				$current = (int)$current;
                $current++;
                // save recurring count
                \Indeed\Ihc\Db\UserSubscriptionsMeta::save( $subscriptionId, 'recurring_cycles_count', $current );

                $limit = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionId, 'billing_limit_num' );
                if ( $current < $limit ){
                    // save payment due time
                    \Indeed\Ihc\Db\UserSubscriptionsMeta::save( $subscriptionId, 'payment_due_time', $args['expire_time'] );
                }
            }
        }

    }

    /**
     * @param int
     * @param int
     * @param bool
     * @param array
     * @return none
     */
    public function subscriptionActivatedFirstTime( $uid=0, $lid=0, $args=[] )
    {
        // save payment gateway as subscription meta
        $subscriptionId = \Indeed\Ihc\UserSubscriptions::getIdForUserSubscription( $uid, $lid );
        if ( isset( $args['payment_gateway']) && $subscriptionId ){
            \Indeed\Ihc\Db\UserSubscriptionsMeta::save( $subscriptionId, 'payment_gateway', $args['payment_gateway'] );
        }

        // save if it's manual activation
        if ( isset( $args['manual'] ) && $subscriptionId ){
            \Indeed\Ihc\Db\UserSubscriptionsMeta::save( $subscriptionId, 'manual_activation', true );
        }

        // switch role based on Registrtation settings
        ihc_switch_role_for_user( $uid );

        //change the WP Role based on Membership Settings
        $levelData = \Indeed\Ihc\Db\Memberships::getOne( $lid );
        if ( isset( $levelData['custom_role_level'] ) && $levelData['custom_role_level']!=-1 && $levelData['custom_role_level']){

          $userObject = new \WP_User( $uid );

  				if ( $userObject->roles ){
  						foreach ( $userObject->roles as $key => $value ){
  								if ( $value == 'administrator' ){
  										return FALSE;
  								}
  						}
  				}

  				$userObject->set_role( $levelData['custom_role_level'] );
        }

    }

    /**
     * @param int
     * @param int
     * @param bool
     * @param array
     * @return none
     */
    public function subscriptionActivatedRenew( $uid=0, $lid=0, $args=[] )
    {

    }

    /**
     * @param int
     * @param int
     * @return none
     */
    public function deleteUserSubscriptionBefore( $uid=0, $lid=0 )
    {

    }

    /**
     * @param int
     * @param int
     * @return none
     */
    public function deleteUserSubscriptionAfter( $uid=0, $lid=0 )
    {

    }

    /**
     * @param int
     * @return none
     */
    public function deleteAllUserSubscriptionsBefore( $uid=0 )
    {

    }

    /**
     * @param int
     * @return none
     */
    public function deleteAllUserSubscriptionsAfter( $uid=0 )
    {

    }

    /**
     * When a subscription has expired we check maybe we must put another subscription in that place.
     * @param int
     * @param int
     * @return none
     */
    public function expriredSubscription( $uid=0, $lid=0 )
    {
        $level_data = \Indeed\Ihc\Db\Memberships::getOne( $lid );

        //After Expire action
        if (isset($level_data['afterexpire_action']) && $level_data['afterexpire_action'] == 1){
            $success = 	\Indeed\Ihc\UserSubscriptions::deleteOne( $uid, $lid );
            if ($success){
          			//return TRUE;
          		}
        }

        //Aftter Expire downgrade
        if (isset($level_data['afterexpire_level']) && $level_data['afterexpire_level']!=-1){
          $assigned = \Indeed\Ihc\UserSubscriptions::getIdForUserSubscription( $uid, $level_data['afterexpire_level'] );
          if(!$assigned){
      		    \Indeed\Ihc\UserSubscriptions::assign( $uid, $level_data['afterexpire_level'] );
      		    $success = \Indeed\Ihc\UserSubscriptions::makeComplete( $uid, $level_data['afterexpire_level']);
      		    if ($success){
      			           //return TRUE;
      		    }
          }
      	}
    }


        /**
         * When a subscription has expired we check maybe we must put another subscription in that place.
         * @param int
         * @param int
         * @return none
         */
        public function canceledSubscription( $uid=0, $lid=0 )
        {
            $level_data = \Indeed\Ihc\Db\Memberships::getOne( $lid );

            //After Cancel action
            if (isset($level_data['aftercancel_action']) && $level_data['aftercancel_action'] == 1){
                $success = 	\Indeed\Ihc\UserSubscriptions::deleteOne( $uid, $lid );
                if ($success){
              			//return TRUE;
              		}
            }

            //Aftter CAncel downgrade
            if (isset($level_data['aftercancel_action']) && $level_data['aftercancel_action'] == 2 && isset($level_data['aftercancel_level']) && $level_data['aftercancel_level']!=-1){
              $remove = 	\Indeed\Ihc\UserSubscriptions::deleteOne( $uid, $lid );
              $assigned = \Indeed\Ihc\UserSubscriptions::getIdForUserSubscription( $uid, $level_data['aftercancel_level'] );
              if(!$assigned){
          		    \Indeed\Ihc\UserSubscriptions::assign( $uid, $level_data['aftercancel_level'] );
          		    $success = \Indeed\Ihc\UserSubscriptions::makeComplete( $uid, $level_data['aftercancel_level']);
          		    if ($success){
          			           //return TRUE;
          		    }
              }
          	}
        }

      public function saveUserSubscriptionMetaTrial( $uid=0, $lid=0, $args=[] )
      {
          if ( empty( $args['is_trial'] ) ){
              return false;
          }
          $subscriptionId = \Indeed\Ihc\UserSubscriptions::getIdForUserSubscription( $uid, $lid );
          if ( empty( $subscriptionId ) ){
              return false;
          }
          \Indeed\Ihc\Db\UserSubscriptionsMeta::save( $subscriptionId, 'is_trial', 1 );
          if ( !isset( $args['expire_time'] ) ){
              return false;
          }
          \Indeed\Ihc\Db\UserSubscriptionsMeta::save( $subscriptionId, 'expire_trial_time', $args['expire_time']  );
      }
}
