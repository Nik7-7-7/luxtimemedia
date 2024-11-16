<?php
namespace Indeed\Ihc\Payments;
/**
 * @since version 9.3
 * This class works with 'payment workflow' option set as 'New Integration'.
 */
class CancelSubscription
{
	/**
	 * @var int
	 */
    private $uid                  = 0;
	/**
	 * @var int
	 */
    private $lid                  = 0;

	/**
	 * @param none
	 * @return none
	 */
	public function __construct(){}

	/**
	 * @param int
	 * @return object
	 */
	public function setUid( $uid=0 )
	{
		$this->uid = $uid;
		return $this;
	}

	/**
	 * @param int
	 * @return object
	 */
	public function setLid( $lid=0 )
	{
		$this->lid = $lid;
		return $this;
	}

	/**
	 * @param none
	 * @return bool
	 */
	public function proceed()
	{
		if ( !$this->uid || !$this->lid ){
			return false;
		}

		// getting information about subscription
		$transactionId = $this->getTransactionIdFromMetaOrders();

		if ( $transactionId === false || $transactionId == '' ){
			$transactionId = $this->getTransactionIdFromPayments();
		}

		if ( $transactionId === false || $transactionId == '' ){
			return false;
		}
		$paymentGatewayType = $this->getPaymentGatewayTypeFromOrder();
		if ( $paymentGatewayType === false || $paymentGatewayType == '' ){
			$paymentGatewayType = $this->getPaymentGatewayFromPayments();
		}

		if ( $paymentGatewayType === false || $paymentGatewayType == '' ){
			return false;
		}

		switch ( $paymentGatewayType ){
    		case 'paypal':
  				// paypal standard can be canceled after a redirect, so we cancel first time in out db and then redirect to paypal
          \Indeed\Ihc\UserSubscriptions::updateStatus( $this->uid, $this->lid, 5 );// mark status as Cancellation pending
  				$object = new \Indeed\Ihc\Gateways\PayPalStandard();
  				$object->cancelSubscription( $this->uid, $this->lid, $transactionId );// cancel( $this->uid, $this->lid );
    			break;
    		case 'twocheckout':
          $object = new \Indeed\Ihc\Gateways\TwoCheckout();
				  $unsubscribe = $object->cancelSubscription( $this->uid, $this->lid, $transactionId );
          //after we cancel the subscription in payment service, we must modify the status in our db
          if ( $unsubscribe ){
              $this->modifyStatusInDb();
          }
    			break;
    		case 'authorize':
          $object = new \Indeed\Ihc\Gateways\Authorize();
          $unsubscribe = $object->cancel( $transactionId );
  				//after we cancel the subscription in payment service, we must modify the status in our db
  				$this->modifyStatusInDb();
    			break;
			  case 'pagseguro':
          // old implementation
				  $object = new \Indeed\Ihc\Gateways\Pagseguro();
				  $unsubscribe = $object->cancelSubscription( $transactionId );
				  //after we cancel the subscription in payment service, we must modify the status in our db
				  $this->modifyStatusInDb();
				  break;
			  case 'stripe_checkout_v2':
				  $object = new \Indeed\Ihc\Gateways\StripeCheckout();
				  $unsubscribe = $object->cancelSubscription( $this->uid, $this->lid, $transactionId );
          if ( $unsubscribe ){
              $this->modifyStatusInDb();
          }
				  break;
			  case 'paypal_express_checkout':
				  $object = new \Indeed\Ihc\Gateways\PayPalExpressCheckout();
				  $object->cancelSubscription( $this->uid, $this->lid, $transactionId );
          \Indeed\Ihc\UserSubscriptions::updateStatus( $this->uid, $this->lid, 5 );// mark status as Cancellation pending
				  break;
        case 'mollie':
          $object = new \Indeed\Ihc\Gateways\Mollie();
          $unsubscribe = $object->cancelSubscription( $this->uid, $this->lid, $transactionId );
          if ( $unsubscribe ){
            //after we cancel the subscription in payment service, we must modify the status in our db
            $this->modifyStatusInDb();
          }
          break;
        case 'bank_transfer':
          $this->modifyStatusInDb();
          break;
        case 'stripe_connect':
  			  $object = new \Indeed\Ihc\Gateways\StripeConnect();
  			  $unsubscribe = $object->cancelSubscription( $this->uid, $this->lid, $transactionId );
          if ( $unsubscribe ){
              $this->modifyStatusInDb();
          }
  			  break;
			  default:
				  $paymentObject = apply_filters( 'ihc_payment_gateway_create_payment_object', false, $paymentGatewayType );

				  if ( $paymentObject ){
					  $paymentObject->cancelSubscription(  $this->uid, $this->lid, $transactionId );
					  //after we cancel the subscription in payment service, we must modify the status in our db
					  $this->modifyStatusInDb();
				  }
				  break;
    	}
	}

	/**
	 * @param none
	 * @return mixed
	 */
	private function getTransactionIdFromMetaOrders()
	{
		global $wpdb;
		$query = $wpdb->prepare( "SELECT a.meta_value as transaction_id, b.create_date
										FROM {$wpdb->prefix}ihc_orders_meta a
										INNER JOIN {$wpdb->prefix}ihc_orders b ON a.order_id=b.id
										WHERE
										a.meta_key='transaction_id'
										AND
										b.uid=%d
                    AND
										b.lid=%d
										ORDER BY b.create_date
										DESC
										LIMIT 1
										", $this->uid, $this->lid );
		$data = $wpdb->get_row( $query );
		return ( isset( $data->transaction_id ) && $data->transaction_id !== null ) ? $data->transaction_id : false;
	}

	/**
	 * @param none
	 * @return mixed
	 */
	private function getTransactionIdFromPayments()
	{
      global $wpdb;
      $query = $wpdb->prepare( "SELECT txn_id, payment_data FROM {$wpdb->prefix}indeed_members_payments WHERE u_id=%d ORDER BY paydate DESC;", $this->uid);
  		$results = $wpdb->get_results( $query );
  		if ( !$results ){
  			return false;
  		}
  		foreach ( $results as $result ){
  			$resultData = json_decode( $result->payment_data, true );
  			if ( isset( $resultData['lid'] ) && $resultData['lid'] == $this->lid ){
  				  return $result->txn_id;
  			} else if ( isset( $resultData['level'] ) && $resultData['level'] == $this->lid ){
            return $result->txn_id;
        }
  		}
	}

	/**
	 * @param none
	 * @return mixed
	 */
	private function getPaymentGatewayTypeFromOrder()
	{
		global $wpdb;
		$query = $wpdb->prepare( "SELECT a.meta_value as ihc_payment_type, b.create_date
										FROM {$wpdb->prefix}ihc_orders_meta a
										INNER JOIN {$wpdb->prefix}ihc_orders b ON a.order_id=b.id
										WHERE
										a.meta_key='ihc_payment_type'
										AND
										b.uid=%d
                    AND
										b.lid=%d
										ORDER BY b.create_date
										DESC
										LIMIT 1
										", $this->uid, $this->lid );
		$data = $wpdb->get_row( $query );
		return isset( $data->ihc_payment_type ) ? $data->ihc_payment_type : false;
	}

	/**
	 * @param none
	 * @return mixed
	 */
	private function getPaymentGatewayFromPayments()
	{
		  global $wpdb;
      $query = $wpdb->prepare( "SELECT payment_data FROM {$wpdb->prefix}indeed_members_payments WHERE u_id=%d ORDER BY paydate DESC LIMIT 1;", $this->uid );
  		$data = $wpdb->get_var( $query );
  		if ( !$data ){
  			return false;
  		}
      $result = json_decode( $data, true );
		  return isset( $result['ihc_payment_type'] ) ? $result['ihc_payment_type'] : false;
	}

	/**
	 * @param none
	 * @return none
	 */
    private function modifyStatusInDb()
    {
        \Indeed\Ihc\UserSubscriptions::updateStatus( $this->uid, $this->lid, 0 );
        do_action('ihc_action_after_cancel_subscription', $this->uid, $this->lid);
        // @description run after cancel subscription. @param user id (integer), level id (integer)
    }
}
