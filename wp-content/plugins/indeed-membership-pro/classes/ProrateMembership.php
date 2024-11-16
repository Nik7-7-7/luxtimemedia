<?php
namespace Indeed\Ihc;
/**
 * @since version 10.3
 */
class ProrateMembership
{
    /**
     * @var bool
     */
    private $resetInterval                    = false;
    /**
     * @var int
     */
    private $oldLid                           = 0;
    /**
     * @var int
     */
    private $newLid                           = 0;
    /**
     * @var int
     */
    private $timePercentageLeft               = 0;
    /**
     * @var int
     */
    private $oldLevelDaysLeft                 = 0;
    /**
     * @var int
     */
    private $oldSubscriptionId                = 0;
    /**
     * @var int
     */
    private $prorateFrom                      = 0;
    /**
     * @var int
     */
    private $groupId                          = null;
    /**
     * @var string
     */
    private $prorateType                      = 1;
    /**
     * @var array
     */
    private $levelData                        = [];
    /**
     * @var int or null
     */
    private $uid                              = null;
    /**
     * @var array
     */
    private $oldSubscriptionData              = [];


    /**
     * @param none
     * @return none
     */
    public function __construct()
    {
        if ( !get_option( 'ihc_prorate_subscription_enabled', 0 ) ){
            return;
        }

        // settings
        $this->resetInterval = get_option( 'ihc_prorate_subscription_reset_billing_period', 0 );

        // filter om checkout
        add_filter( 'ihc_filter_prepare_payment_level_data', [ $this, 'modifyLevelData' ], 1, 3 );

        // filter on subscription plans listing
        add_filter( 'ihc_public_subscription_plan_list_levels', [ $this, 'filterOnSubscriptionPlan'], 999, 1 );

        // remove old level
        add_action( 'ihc_payment_completed', [ $this, 'removeOldLevel' ], 999, 3 );

        // payment output data
        add_filter( 'ihc_filter_after_prepare_payment', [ $this, 'filterOnPreparePaymentOutputData' ], 999, 1 );
    }


    /**
      * @param array
      * @return array
      */
    public function filterOnSubscriptionPlan( $levels=[] )
    {
        if ( !isset( $_GET['membership'] ) ){
            return $levels;
        }

        // no groups, return all memberships
        if ( \Indeed\Ihc\Db\ProrateMembershipGroups::getAll() === [] ){
            return $levels;
        }

        $currentMembership = sanitize_text_field( $_GET['membership'] );
        $groupId = \Indeed\Ihc\Db\ProrateMembershipGroups::getGroupForLid( $currentMembership );
        foreach ( $levels as $id => $levelData ){
            $levelsGroupId = \Indeed\Ihc\Db\ProrateMembershipGroups::getGroupForLid( $levelData['id'] );
            if ( (int)$levelsGroupId !== (int)$groupId ){
                unset( $levels[$id] );
            }
        }
        return $levels;
    }

    /**
     * @param array
     * @param array
     * @param string
     * @return array
     */
    public function modifyLevelData( $levelData=[], $inputData=[], $paymentType='' )
    {
        if ( $paymentType === '' || $paymentType !== 'stripe_connect' ){
            // available only in stripe connect
            return $levelData;
        }
        if ( empty( $inputData['uid'] ) ){
            // not available for register
            return $levelData;
        }
        $this->uid = $inputData['uid'];

        // new membership
        $this->newLid = $levelData['id'];

        // old memberships
        $allMembershipsWithGroup = \Indeed\Ihc\Db\ProrateMembershipGroups::getAllMembershipsWithGroup();
        if ( $allMembershipsWithGroup === [] ){
            // get the last membership for this user
            $this->oldLid = \Indeed\Ihc\UserSubscriptions::getLastForUid( $this->uid, $this->newLid );
        } else {
            $this->oldLid = \Indeed\Ihc\UserSubscriptions::getLastForUid( $this->uid, $this->newLid, $allMembershipsWithGroup );
        }

        if ( $this->oldLid === false ){
            // no membership for this user. out
            return $levelData;
        }

        // check the group
        if ( !$this->checkTheGroups() ){
            return $levelData;
        }

        // prorate type
        $this->prorateType = $this->getProrateType();

        // set the current membership type
        $newMembershipType = isset( $levelData['access_type'] ) ? $levelData['access_type'] : '';

        $this->oldSubscriptionData = \Indeed\Ihc\UserSubscriptions::getOne( $this->uid, $this->oldLid );
        $this->oldSubscriptionId = isset( $this->oldSubscriptionData['id'] ) ? $this->oldSubscriptionData['id'] : false;
        // set the old membership type
        $oldMembershipType = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $this->oldSubscriptionId, 'access_type' );

        // ------ calculate old amount
        $oldAmount = $this->getOldAmountForMembership();
        $levelData['prorate_from_label'] = \Indeed\Ihc\Db\Memberships::getMembershipLabel( $this->oldLid );
        $levelData['prorate_type'] = $this->prorateType;
        if ( $oldAmount === false ){
            // it's a free level
            $levelData['prorate_from'] = $this->oldLid;
            $levelData['prorate_expire_time'] = $this->calculateExpireTimeForNewLevel( $levelData );
            if ( \Indeed\Ihc\Db\Memberships::isRecurring( $this->oldLid) ){
                // if the old membership is recurring, we must cancel it
                $levelData['prorate_old_subscription_id'] = $this->oldSubscriptionId;
            }
            $this->levelData = $levelData;
            return $levelData;
        }

        // ------ calculate old amount left
        $currentTime = indeed_get_unixtimestamp_with_timezone();
        $oldLevelExpireTime = strtotime( $this->oldSubscriptionData['expire_time'] );
        $oldLevelUpdateTime = strtotime( $this->oldSubscriptionData['update_time'] );
        $oldLevelTimeLeft = $oldLevelExpireTime - $currentTime;
        $this->calculateTimePercentageLeft( $oldLevelExpireTime, $oldLevelUpdateTime, $currentTime , $oldMembershipType );
        $this->calculateDaysLeftFromOldSubscription( $oldLevelExpireTime, $currentTime );

        $oldAmountLeft = $this->calculateOldAmountLeft( $oldAmount );

        /// ------- calculate new amount
        if ( \Indeed\Ihc\Db\Memberships::isTrial( $this->newLid ) ){
            $newAmount = $levelData['access_trial_price'] === false || $levelData['access_trial_price'] === '' ? $levelData['price'] : $levelData['access_trial_price'];
        } else {
            $newAmount = $levelData['price'];
        }

        // if the old amount is 0, return
        if ( $oldAmount == 0 ){
            $levelData['prorate_from'] = $this->oldLid;
            $levelData['prorate_old_subscription_id'] = $this->oldSubscriptionId;
            $levelData['prorate_expire_time'] = $this->calculateExpireTimeForNewLevel( $levelData );
            $this->levelData = $levelData;
            return $levelData;
        }

        // if the amount left from old subscription is 0, return
        if ( $oldAmountLeft == 0 ){
            $levelData['prorate_from'] = $this->oldLid;
            $levelData['prorate_expire_time'] = $this->calculateExpireTimeForNewLevel( $levelData );
            if ( \Indeed\Ihc\Db\Memberships::isRecurring( $this->oldLid) ){
                // if the old membership is recurring, we must cancel it
                $levelData['prorate_old_subscription_id'] = $this->oldSubscriptionId;
            }
            $this->levelData = $levelData;
            return $levelData;
        }

        // if the amount for the new subscription it's 0, return
        if ( $newAmount == 0 ){
            $levelData['prorate_from'] = $this->oldLid;
            $levelData['prorate_expire_time'] = $this->calculateExpireTimeForNewLevel( $levelData );
            if ( \Indeed\Ihc\Db\Memberships::isRecurring( $this->oldLid) ){
                // if the old membership is recurring, we must cancel it
                $levelData['prorate_old_subscription_id'] = $this->oldSubscriptionId;
            }
            $this->levelData = $levelData;
            return $levelData;
        }

        $attr = [
                  'old_amount'            => (float)$oldAmount,
                  'old_amount_left'       => $oldAmountLeft,
                  'new_amount'            => (float)$newAmount,
        ];

        switch ( $oldMembershipType ){
            case 'unlimited':
            case 'limited':
            case 'date_interval':
              // Lifetime
              switch ( $newMembershipType ){
                  case 'unlimited':
                  case 'date_interval':
                  case 'limited':
                    // lifetime to date_interval/limited
                    return $this->subscriptionToOneTime( $attr, $levelData );
                    break;
                  case 'regular_period':
                    // lifetime to recurring
                    return $this->oneTimeToRecurring( $attr, $levelData );
                    break;
              }
              break;
            case 'regular_period':
              // Recurring Subscription
              switch ( $newMembershipType ){
                  case 'unlimited':
                  case 'date_interval':
                  case 'limited':
                    // recurring to lifetime
                    return $this->subscriptionToOneTime( $attr, $levelData );
                    break;
                  case 'regular_period':
                    // recurring to recurring
                    return $this->recurringToRecurring( $attr, $levelData );
                    break;
              }
              break;
        }
        return $levelData;

    }

    /**
     * @param none
     * @return string
     */
    private function getProrateType()
    {
        if ( $this->groupId === null && !\Indeed\Ihc\Db\ProrateMembershipGroups::getAll() ){
            $memberships = \Indeed\Ihc\Db\Memberships::getAll();
            foreach ( $memberships as $levelData ){
                if ( (int)$levelData['id'] === (int)$this->oldLid ){
                    return 1;// upgrade
                } else if ( (int)$levelData['id'] === (int)$this->newLid ){
                    return 0;// downgrade
                }
            }
        } else {
            $groupData = \Indeed\Ihc\Db\ProrateMembershipGroups::getOne( $this->groupId );

            foreach ( $groupData['memberships'] as $levelId ){
                if ( (int)$levelId === (int)$this->oldLid ){
                    return 1;// upgrade
                } else if ( (int)$levelId === (int)$this->newLid ){
                    return 0;// downgrade
                }
            }
        }
        return 1;// upgrade
    }

    /**
     * @param none
     * @return bool
     */
    private function checkTheGroups()
    {
        // check the membership groups
        if ( \Indeed\Ihc\Db\ProrateMembershipGroups::getAll() !== [] ){
            $oldMembershipGroupId = \Indeed\Ihc\Db\ProrateMembershipGroups::getGroupForLid( $this->oldLid );
            if ( $oldMembershipGroupId === false ){
                return false;
            }
            $newMembershipGroupId = \Indeed\Ihc\Db\ProrateMembershipGroups::getGroupForLid( $this->newLid );
            if ( $newMembershipGroupId === false ){
                return false;
            }
            if ( (int)$oldMembershipGroupId !== (int)$newMembershipGroupId ){
                return false;
            } else {
                $this->groupId = $oldMembershipGroupId;
            }
        }
        return true;
    }

    /**
     * @param none
     * @return float
     */
    public function getOldAmountForMembership()
    {
        $orderId = \Ihc_Db::getLastOrderIdByUserAndLevel( $this->uid, $this->oldLid );
        if ( $orderId === null || $orderId === false ){
            // if we didn't find the old order, return
            return false;
        }
        $orderObject = new \Indeed\Ihc\Db\Orders();
        $orderData = $orderObject->setId( $orderId )->fetch()->get();
        $amountValue = isset( $orderData->amount_value ) ? $orderData->amount_value : '';

        $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
        $firstAmount = $orderMeta->get( $orderId, 'first_amount' );
        if (isset($firstAmount) && (float)$firstAmount === (float)$amountValue ){
        	$firstCharge = true;
        }
        $taxes = $orderMeta->get( $orderId, 'taxes_amount' );
        if ( $taxes == null ){
        	$taxes = $orderMeta->get( $orderId, 'tax_value' );
        }
        if ( $taxes == null ){
        	if (isset($firstChage) && $firstChage == true){
        		$taxes = $orderMeta->get( $orderId, 'first_amount_taxes' );
        	} else {
        		$taxes = $orderMeta->get( $orderId, 'taxes' );
        	}
        }

        if ( isset( $firstCharge ) && $firstCharge === true && isset( $firstAmount ) ){
          	$netAmount = $firstAmount;
          	if ( $taxes != false ){
          		$netAmount = $netAmount - $taxes;
          	}
            return $netAmount;
        } else {
          	$value = $orderMeta->get( $orderId, 'base_price' );
          	if ( $value !== null ){
          		return $value;
          	} elseif ( $taxes != false ){
          		return $amountValue - $taxes;
          	} else {
          		return $amountValue;
          	}
        }
        return false;
    }

    /**
     * @param int
     * @param int
     * @param int
     * @return int
     */
    private function calculateTimePercentageLeft( $oldLevelExpireTime=0, $oldLevelUpdateTime=0, $currentTime=0, $oldMembershipType='' )
    {
        if ( $oldMembershipType==='unlimited'){
            // 100% for lifetime
            $this->timePercentageLeft = 100;
            return;
        }
        if ( $oldLevelExpireTime > $oldLevelUpdateTime ){
            $divide = ((int)$oldLevelExpireTime - (int)$oldLevelUpdateTime);
            $timeRemaining = ((int)$oldLevelExpireTime - (int)$currentTime);
            if ( $divide < 0 || $timeRemaining < 0 ){
                $this->timePercentageLeft = 0;
                return;
            }
            $this->timePercentageLeft = $timeRemaining * 100 / $divide;
            $this->timePercentageLeft = round($this->timePercentageLeft,1);
        } else {
            $this->timePercentageLeft = 0;
        }
    }

    /**
     * @param float
     * @return float
     */
    private function calculateOldAmountLeft( $amount=0 )
    {
        if ( $amount <= 0 ){
            $oldAmountLeft = 0;
            return $oldAmountLeft;
        }
        if ( $this->timePercentageLeft <= 0 ){
            return $this->roundNumber( $amount );
        }
        $oldAmountLeft = $this->timePercentageLeft * $amount / 100;
        if ( $oldAmountLeft < 0 ){
            $oldAmountLeft = 0;
        }
        return $this->roundNumber( $oldAmountLeft );
    }

    /**
     * @param float
     * @param float
     * @return float
     */
    private function calculateDiffOldNewAmount( $oldAmountLeft=0, $newAmount=0 )
    {
        $newAmount = $newAmount - $oldAmountLeft;
        if ( $newAmount < 0 ){
            $newAmount = 0;
        }
        return $this->roundNumber( $newAmount );
    }

    /**
     * @param float
     * @return float
     */
    private function roundNumber( $number=0 )
    {
        $numberOfDecimals = get_option( 'ihc_num_of_decimals', 2 );
        return round( $number, $numberOfDecimals );
    }

    /**
     * @param int
     * @param int
     * @return none
     */
    private function calculateDaysLeftFromOldSubscription( $expireTime=0, $currentTime=0 )
    {
        $time = $expireTime - $currentTime;
        if ( $time <= 0 ){
            return 0;
        }
        $days = $time / (24 * 60 * 60);
        // put here round
        $this->oldLevelDaysLeft = (int)$days;
    }

    /**
     * @param array
     * @return int
     */
    private function calculateExpireTimeForNewLevel( $levelData=[] )
    {
        /// extra check if it's couple of cycles
        if ( \Indeed\Ihc\Db\Memberships::isTrial( $levelData['id'] ) ){
            if ( $levelData['access_trial_type'] == 1 && !empty( $levelData['access_trial_time_value'] ) ){
                // certain time value
                $intervalType = $levelData['access_trial_time_type'];
                $intervalValue = $levelData['access_trial_time_value'];
            } else {
                // couple of cycles
                $intervalType = $levelData['access_trial_time_type'];
                $intervalValue = $levelData['access_regular_time_value'] * $levelData['access_trial_couple_cycles'];
            }
        } else {
          $intervalType = $levelData['access_regular_time_type'];
          $intervalValue = $levelData['access_regular_time_value'];
        }

        return $this->getTimestampFromTypeAndValue( $intervalType, $intervalValue );
    }

    /**
     * @param string
     * @param int
     * @return int
     */
    private function getTimestampFromTypeAndValue( $intervalType='', $intervalValue=0 )
    {
        switch ( $intervalType ) {
          case 'D':
            $intervalType = 'day';
            break;
          case 'W':
            $intervalType = 'week';
            break;
          case 'M':
            $intervalType = 'month';
            break;
          case 'Y':
            $intervalType = 'year';
            break;
        }
        return strtotime('+' . $intervalValue . ' ' . $intervalType, indeed_get_unixtimestamp_with_timezone() );
    }

    /**
     * @param array
     * @param array
     * @return array
     */
    private function subscriptionToOneTime( $attr=[], $levelData=[] )
    {
        $levelData['price'] = $this->calculateDiffOldNewAmount( $attr['old_amount_left'], $attr['new_amount'] );
        $levelData['prorate_from'] = $this->oldLid;
        $levelData['prorate_initial_amount'] = $attr['new_amount'];
        $levelData['prorate_old_amount_left'] = $attr['old_amount_left'];
        $levelData['prorate_new_amount'] = $levelData['price'];// output price
        $levelData['prorate_old_days_left'] = $this->oldLevelDaysLeft;

        if ( \Indeed\Ihc\Db\Memberships::isRecurring( $this->oldLid) ){
            // if the old membership is recurring, we must cancel it
            $levelData['prorate_old_subscription_id'] = $this->oldSubscriptionId;
        }
        // since version 12.2
        $levelData['prorate_expire_time'] = strtotime( '+' . $this->oldLevelDaysLeft . 'day', indeed_get_unixtimestamp_with_timezone() );

        $this->levelData = $levelData;
        return $levelData;
    }

    /**
     * @param array
     * @param array
     * @return array
     */
    private function oneTimeToRecurring( $attr=[], $levelData=[] )
    {
        // Reset the subscription interval
        $levelData['access_trial_price'] = $this->calculateDiffOldNewAmount( $attr['old_amount_left'], $attr['new_amount'] );
        if ( !\Indeed\Ihc\Db\Memberships::isTrial( $this->newLid ) ){
          $levelData['access_trial_type'] = 2; // couple of cycles
          $levelData['access_trial_couple_cycles'] = 1; // set the trial to one cycle
          $levelData['prorate_force_trial'] = 1;
        }
        $levelData['prorate_from'] = $this->oldLid;
        $levelData['prorate_expire_time'] = $this->getTimestampFromTypeAndValue( $levelData['access_regular_time_type'], $levelData['access_regular_time_value'] );
        $levelData['prorate_initial_amount'] = $attr['new_amount'];
        $levelData['prorate_old_amount_left'] = $attr['old_amount_left'];
        $levelData['prorate_new_amount'] = $levelData['access_trial_price'];// output price
        $levelData['prorate_old_days_left'] = $this->oldLevelDaysLeft;
        $this->levelData = $levelData;
        return $levelData;
    }

    /**
     * @param array
     * @param array
     * @return array
     */
    private function recurringToRecurring( $attr=[], $levelData=[] )
    {
        $newExpireTimestamp = $this->calculateExpireTimeForNewLevel( $levelData );
        if ( $this->resetInterval ){
            /// with reset interval
            // one full cycle trial
            if ( \Indeed\Ihc\Db\Memberships::isTrial( $this->newLid ) ){
                // new membership already has a trial, so we don't modify that
                $levelData['access_trial_price'] = $this->calculateDiffOldNewAmount( $attr['old_amount_left'], $attr['new_amount'] );


                $levelData['prorate_old_subscription_id'] = $this->oldSubscriptionId;

                $levelData['prorate_from'] = $this->oldLid;
                $levelData['prorate_expire_time'] = $newExpireTimestamp;
                $levelData['prorate_initial_amount'] = $attr['new_amount'];
                $levelData['prorate_old_amount_left'] = $attr['old_amount_left'];
                $levelData['prorate_new_amount'] = $levelData['access_trial_price'];// output price
                $levelData['prorate_old_days_left'] = $this->oldLevelDaysLeft;

                $this->levelData = $levelData;
                return $levelData;
            } else {
                // no trial, so we put a trial with a full cycle
                $levelData['access_trial_price'] = $this->calculateDiffOldNewAmount( $attr['old_amount_left'], $attr['new_amount'] );
                $levelData['access_trial_type'] = 2; // couple of cycles
                $levelData['access_trial_couple_cycles'] = 1; // set the trial to one cycle

                $levelData['prorate_reset_interval'] = 1;
                $levelData['prorate_old_subscription_id'] = $this->oldSubscriptionId;

                $levelData['prorate_from'] = $this->oldLid;
                $levelData['prorate_expire_time'] = $newExpireTimestamp;
                $levelData['prorate_initial_amount'] = $attr['new_amount'];
                $levelData['prorate_old_amount_left'] = $attr['old_amount_left'];
                $levelData['prorate_new_amount'] = $levelData['access_trial_price'];// output price
                $levelData['prorate_old_days_left'] = $this->oldLevelDaysLeft;
                $levelData['prorate_force_trial'] = 1;

                $this->levelData = $levelData;
                return $levelData;
            }
        } else {
             /// no reset interval - trial with endtime of old membership

            $newEpireTimestampLeft = $newExpireTimestamp - indeed_get_unixtimestamp_with_timezone();
            if ( $newEpireTimestampLeft <= 0 ){
                $newExpireInDays = 0;
            } else {
                $newExpireInDays = round( ( $newExpireTimestamp - indeed_get_unixtimestamp_with_timezone() ) / (24 * 60 * 60) );
            }

            // the amount to pay will be a proportion
            if ( $this->oldLevelDaysLeft <= 0 || $newExpireInDays <=0 ){
                $trialAmountFull = $this->roundNumber( $attr['new_amount'] );
            } else {
                $trialAmountFull = ( $attr['new_amount'] * $this->oldLevelDaysLeft ) / $newExpireInDays;
                $trialAmountFull = $this->roundNumber( $trialAmountFull );
            }
            $amountToPay = $this->calculateDiffOldNewAmount( $attr['old_amount_left'], $trialAmountFull );

            $levelData['access_trial_price'] = $amountToPay;
            $levelData['access_trial_type'] = 1; // certain period
            $levelData['access_trial_time_value'] = $this->oldLevelDaysLeft;
            $levelData['access_trial_time_type'] = 'D';

            $levelData['prorate_force_trial'] = 1;
            $levelData['prorate_reset_interval'] = 1;
            $levelData['prorate_old_subscription_id'] = $this->oldSubscriptionId;

            $levelData['prorate_from'] = $this->oldLid;
            $levelData['prorate_expire_time'] = strtotime( '+' . $this->oldLevelDaysLeft . 'day', indeed_get_unixtimestamp_with_timezone() );
            $levelData['prorate_initial_amount'] = $attr['new_amount'];
            $levelData['prorate_old_amount_left'] = $attr['old_amount_left'];
            $levelData['prorate_new_amount'] = $levelData['access_trial_price'];// output price
            $levelData['prorate_old_days_left'] = $this->oldLevelDaysLeft;
            $this->levelData = $levelData;
            return $levelData;
        }
        return $levelData;
    }

    /**
     * @param int
     * @param int
     * @param array
     * @return none
     */
    public function removeOldLevel( $uid=0, $lid=0, $levelData=[] )
    {
        // maybe prorate
        if ( isset( $levelData['prorate_from'] ) && $levelData['prorate_from'] !== '' ){
            $stripeConnect = new \Indeed\Ihc\Gateways\StripeConnect();
            if ( isset( $levelData['prorate_old_subscription_id'] ) && $levelData['prorate_old_subscription_id'] !== ''
                  && $stripeConnect->canDoCancel( $uid, $levelData['prorate_from'], $levelData['prorate_old_subscription_id'] )
            ){
                // cancel subscription on stripe via api
                $subscriptionId = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $levelData['prorate_old_subscription_id'], 'ihc_stripe_subscription_id' );
                $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
                $orderId = $orderMeta->getIdFromMetaNameMetaValue( 'subscription_id', $subscriptionId );
                $transactionId = $orderMeta->get( $orderId, 'transaction_id' );

                $stripeConnect->cancel( $uid, $levelData['prorate_from'], $transactionId );
            }
            // cancel the old subscription into our db, this will trigger some filters
            $stripeConnect->webhookDoCancel( $uid, $levelData['prorate_from'] );
            // and then delete the subscription
            \Indeed\Ihc\UserSubscriptions::deleteOne( $uid, $levelData['prorate_from'] );
        }
    }

    /**
     * @param array
     * @return array
     */
    public function filterOnPreparePaymentOutputData( $paymentOutputData=[] )
    {
        if ( $this->levelData === [] || !isset( $this->levelData['prorate_from'] ) ){
            return $paymentOutputData;
        }
        foreach ( $this->levelData as $key => $value ){
            if ( strpos( $key, 'prorate' ) !== false ){
                $paymentOutputData[ $key ] = $value;
            }
        }
        return $paymentOutputData;
    }

}
