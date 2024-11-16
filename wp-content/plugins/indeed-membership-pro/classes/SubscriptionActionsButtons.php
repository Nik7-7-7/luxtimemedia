<?php
namespace Indeed\Ihc;

class SubscriptionActionsButtons
{

    /**
     * @param int
     * @param int
     * @param int
     * @return bool
     */
    public static function showCancel( $uid=0, $lid=0, $subscriptionId=0, $checkThePaymentType=true )
    {
        if ( !$uid || !$lid ){
            return false;
        }

        // only if it's enabled on admin dashboard
        if ( get_option( 'ihc_show_cancel_link', 1 ) === '0' ){
            return false;
        }

        $stopShowingBttn = apply_filters( 'ihc_filter_public_stop_showing_cancel_bttn', false, $uid, $lid, $subscriptionId );
        if ( $stopShowingBttn === true ){
            return false;
        }

        // only for recurring subscriptions
        $accessType = \Indeed\Ihc\Db\Memberships::getOneMeta( $lid, 'access_type' );
        if ( $accessType !== 'regular_period' ){
            return false;
        }

        // only for active subscriptions
        $statusArr = \Indeed\Ihc\UserSubscriptions::getStatus( $uid, $lid, $subscriptionId );
        if ( !isset( $statusArr['status'] ) || ($statusArr['status'] != 1 && $statusArr['status'] != 5)  ){
            // if the status of subscription is not Active or Cancelation pending ( for paypal ) we exit
            return false;
        }

        // check the payment type
        $orderId = \Ihc_Db::getLastOrderIdByUserAndLevel( $uid, $lid );
        $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
        $paymentType = $orderMeta->get( $orderId, 'ihc_payment_type' );
        $excludedPaymentTypeFromCancel = [ 'braintree' ];
        if ( $checkThePaymentType && ( $paymentType === null || $paymentType === '' || in_array( $paymentType, $excludedPaymentTypeFromCancel ) ) ){
            return false;
        }

        $canDo = true;
        $transactionId = \Ihc_Db::getTransactionIdForUserSubscription( $uid, $lid, $orderId );
        if(!empty($paymentType)){
        switch ( $paymentType ){
            case 'paypal':
              $object = new \Indeed\Ihc\Gateways\PayPalStandard();
              $canDo = $object->canDoCancel( $uid, $lid, $transactionId );
              break;
            case 'twocheckout':
              $object = new \Indeed\Ihc\Gateways\TwoCheckout();
              $canDo = $object->canDoCancel( $uid, $lid, $transactionId );
              break;
            case 'authorize':
              $object = new \Indeed\Ihc\Gateways\Authorize();
        			$canDo = $object->canDoCancel( $transactionId );
              break;
            case 'pagseguro':
              $object = new \Indeed\Ihc\Gateways\Pagseguro();
              $canDo = $object->canDoCancel( $uid, $lid, $transactionId );
              break;
            case 'stripe_checkout_v2':
              $object = new \Indeed\Ihc\Gateways\StripeCheckout();
              $canDo = $object->canDoCancel( $uid, $lid, $transactionId );
              break;
            case 'paypal_express_checkout':
              $object = new \Indeed\Ihc\Gateways\PayPalExpressCheckout();
              $canDo = $object->canDoCancel( $uid, $lid, $transactionId );
              break;
            case 'mollie':
              $object = new \Indeed\Ihc\Gateways\Mollie();
              $canDo = $object->canDoCancel( $uid, $lid, $transactionId );
              break;
            case 'bank_transfer':
              $canDo = true;
              break;
            case 'stripe_connect':
              $object = new \Indeed\Ihc\Gateways\StripeConnect();
              $canDo = $object->canDoCancel( $uid, $lid, $subscriptionId );
              break;
            default:
              $paymentObject = apply_filters( 'ihc_payment_gateway_create_payment_object', false, $paymentType );
              if ( $paymentObject !== false && method_exists ( $paymentObject , 'canDoCancel' ) ){
                  $canDo = $paymentObject->canDoCancel( $uid, $lid, $transactionId );
              }
              break;
        }
      }else{
        $canDo = false;
      }
        if ( $canDo ){
            return true;
        }
        return false;

    }

    /**
     * @param int
     * @param int
     * @param int
     * @return bool
     */
    public static function showRenew( $uid=0, $lid=0, $subscriptionId=0 )
    {
        if ( !$uid || !$lid ){
            return false;
        }

        // only if it's enabled on admin dashboard
        if ( get_option( 'ihc_show_renew_link', 1 ) === '0' ){
            return false;
        }

        // only for expired subscriptions
        $statusArr = \Indeed\Ihc\UserSubscriptions::getStatus( $uid, $lid, $subscriptionId );
        if ( !isset( $statusArr['status'] ) || $statusArr['status'] != 2 ){
            return false;
        }

        return true;
    }

    /**
     * @param int
     * @param int
     * @param int
     * @return bool
     */
    public static function showRemove( $uid=0, $lid=0, $subscriptionId=0 )
    {
        if ( !$uid || !$lid ){
            return false;
        }

        // only if it's enabled on admin dashboard
        if ( get_option( 'ihc_show_delete_link', 1 ) === '0' ){
            return false;
        }

        $stopShowingBttn = apply_filters( 'ihc_filter_public_stop_showing_remove_bttn', false, $uid, $lid, $subscriptionId );
        if ( $stopShowingBttn === true ){
            return false;
        }

        // if we show the cancel button, we don't show anymore the remove button
        if ( self::showCancel( $uid, $lid, $subscriptionId, false ) ){
            return false;
        }

        return true;
    }

    /**
     * @param int
     * @param int
     * @param int
     * @return bool
     */
    public static function showFinishPayment( $uid=0, $lid=0, $subscriptionId=0 )
    {
        if ( !$uid || !$lid ){
            return false;
        }

        // only if it's enabled on admin dashboard
        if ( get_option( 'ihc_show_finish_payment', 1 ) === '0' ){
            return false;
        }

        $statusArr = \Indeed\Ihc\UserSubscriptions::getStatus( $uid, $lid, $subscriptionId );
        // only for on hold subscriptions
        if ( !isset( $statusArr['status'] ) || $statusArr['status'] != 3 ){
            return false;
        }

        // check the last order create time
        $orderData = \Ihc_Db::getLastOrderDataByUserAndLevel( $uid, $lid );
        $minimHours = get_option( 'ihc_subscription_table_finish_payment_after', 12 );
        $minTime = (int)$minimHours * 60 * 60;

        if ( !isset( $orderData['create_date'] ) || ( strtotime( $orderData['create_date'] ) + $minTime ) > indeed_get_unixtimestamp_with_timezone() ){
            return false;
        }

        return true;
    }

    /**
     * @param int
     * @param int
     * @param int
     * @return bool
     */
    public static function showPause( $uid=0, $lid=0, $subscriptionId=0 )
    {
        if ( !$uid || !$lid ){
            return false;
        }

        // only if it's enabled on admin dashboard
        if ( get_option( 'ihc_show_pause_resume_link', 1 ) === '0' ){
            return false;
        }

        // only for active subscriptions
        $statusArr = \Indeed\Ihc\UserSubscriptions::getStatus( $uid, $lid, $subscriptionId );
        if ( !isset( $statusArr['status'] ) || $statusArr['status'] != 1 ){
            return false;
        }

        // not available for recurring subscription
        $subscriptionType = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionId, 'access_type' );
        if ( $subscriptionType === false ){
            return false;
        }elseif($subscriptionType === 'regular_period'){
          $subscriptionPayment = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionId, 'payment_gateway' );

          $canDo = false;
          if( $subscriptionPayment ){
              switch ( $subscriptionPayment ){
                    case 'stripe_connect':
                      $object = new \Indeed\Ihc\Gateways\StripeConnect();
                      $canDo = $object->canDoPause( $uid, $lid,  $subscriptionId );
                      break;
                  default:
                    $paymentObject = apply_filters( 'ihc_payment_gateway_create_payment_object', false, $subscriptionPayment );
                    if ( $paymentObject !== false && method_exists ( $paymentObject , 'canDoPause' ) ){
                        $canDo = $paymentObject->canDoPause( $uid, $lid,  $subscriptionId );
                    }
                    break;
              }
          }
          if($canDo === false){
            return false;
          }
        }



        return true;
    }

    /**
     * @param int
     * @param int
     * @param int
     * @return bool
     */
    public static function showResume( $uid=0, $lid=0, $subscriptionId=0 )
    {
          if ( !$uid || !$lid ){
              return false;
          }

          // only if it's enabled on admin dashboard
          if ( get_option( 'ihc_show_pause_resume_link', 0 ) === '0' ){
              return false;
          }

          // only for paused subscriptions
          $statusArr = \Indeed\Ihc\UserSubscriptions::getStatus( $uid, $lid, $subscriptionId );
          if ( !isset( $statusArr['status'] ) || $statusArr['status'] != 4 ){
              return false;
          }
          // not available for recurring subscription
          $subscriptionType = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionId, 'access_type' );
          if ( $subscriptionType === false ){
              return false;
          }elseif($subscriptionType === 'regular_period'){
            $subscriptionPayment = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionId, 'payment_gateway' );

            $canDo = false;
            if( $subscriptionPayment ){
                switch ( $subscriptionPayment ){
                      case 'stripe_connect':
                        $object = new \Indeed\Ihc\Gateways\StripeConnect();
                        $canDo = $object->canDoResume( $uid, $lid, $subscriptionId );
                        break;
                    default:
                      $paymentObject = apply_filters( 'ihc_payment_gateway_create_payment_object', false, $subscriptionPayment );
                      if ( $paymentObject !== false && method_exists ( $paymentObject , 'canDoResume' ) ){
                          $canDo = $object->canDoResume( $uid, $lid, $subscriptionId );
                      }
                      break;
                }
            }
            if($canDo === false){
              return false;
            }
          }


          return true;
    }

    /**
     * @param int
     * @param int
     * @param int
     * @return bool
     */
    public static function showStripeChangeCard( $uid=0, $lid=0, $subscriptionId=0 )
    {
        if ( !$uid || !$lid ){
            return false;
        }
        $subscriptionId = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionId, 'ihc_stripe_subscription_id' );
        if ( $subscriptionId === false ){
            return false;
        }
        return true;
    }

    /**
     * @param int
     * @param int
     * @param int
     * @return bool
     */
    public static function showChangePlanBttn( $uid=0, $lid=0, $subscriptionId=0 )
    {
        if ( !$uid || !$lid ){
            return false;
        }
        // check if the module is enabled
        if ( !get_option('ihc_prorate_subscription_enabled', 0 ) ){
            return false;
        }
        // check if stripe connect is properly set
        if ( !ihc_check_payment_available( 'stripe_connect' ) ){
            return false;
        }

        // check if subscription plan page exists
        $subscriptionPage = get_option( 'ihc_subscription_plan_page', 0 );
        if ( (int)$subscriptionPage === 0 || (int)$subscriptionPage === -1 ){
            return false;
        }
        $subscriptionPage = get_permalink( $subscriptionPage );
        if ( $subscriptionPage === '' || $subscriptionPage === false ){
            return false;
        }

        $subscriptionType = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionId, 'access_type' );
        if ( $subscriptionType === 'regular_period' ){
            // check if the payment was made with stripe connect
            $orderId = \Ihc_Db::getLastOrderIdByUserAndLevel( $uid, $lid );
            if ( $orderId === null || $orderId === false ){
                return false;
            }
            $ordersMeta = new \Indeed\Ihc\Db\OrderMeta();
            $paymentType = $ordersMeta->get( $orderId, 'ihc_payment_type' );
            if ( $paymentType !== 'stripe_connect' ){
                return false;
            }
        }

        // check if current membership has a group
        $allMembershipsWithGroup = \Indeed\Ihc\Db\ProrateMembershipGroups::getAll();
        if ( $allMembershipsWithGroup === [] ){
            // get the last membership for this user
            return true;
        } else {
            $groupId = \Indeed\Ihc\Db\ProrateMembershipGroups::getGroupForLid( $lid );
            if ( $groupId === false ){
                return false;
            }
        }
        return true;
    }

}
