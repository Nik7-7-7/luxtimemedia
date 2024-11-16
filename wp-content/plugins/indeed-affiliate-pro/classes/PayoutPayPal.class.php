<?php
require_once UAP_PATH . 'classes/services/paypal/vendor/autoload.php';
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

if ( class_exists('PayoutPayPal') ){
	 return;
}

class PayoutPayPal
{
		private $apiContext								= null;
		private $is_sandbox 							= 1;
		private $senders 									= array();
		private $client_id								= '';
		private $client_secret						= '';
		private $errorDetails							= '';
		private $targetEmail							= '';

	/**
	 * @param none
	 * @return none
	 */
	public function __construct()
	{
		date_default_timezone_set(@date_default_timezone_get());
		$this->is_sandbox = get_option('uap_paypal_sandbox');
		if ($this->is_sandbox){
			$this->client_id = get_option('uap_paypal_sandbox_client_id');
			$this->client_secret = get_option('uap_paypal_sandbox_client_secret');
		} else {
			$this->client_id = get_option('uap_paypal_client_id');
			$this->client_secret = get_option('uap_paypal_client_secret');
		}
		$this->client_id = trim($this->client_id);
		$this->client_secret = trim($this->client_secret);
		$this->set_api_context();
	}

	/**
	 * @param string, string
	 * @return none
	 */
	private function set_api_context($client_id='', $client_secret='')
	{
		 if ( !$this->client_id ){
			 	if ( $this->is_sandbox ){
			 			$this->errorDetails = esc_html__( 'Sandbox Client ID is not set.', 'uap' );
				} else {
						$this->errorDetails = esc_html__( 'Client ID is not set.', 'uap' );
				}
		 	  return false;
		 }
		 if ( !$this->client_secret ){
			 if ( $this->is_sandbox ){
					 $this->errorDetails = esc_html__( 'Sandbox Client Secret is not set.', 'uap' );
			 } else {
					 $this->errorDetails = esc_html__( 'Client Secret is not set.', 'uap' );
			 }
			 return false;
		 }
		$this->apiContext = new \PayPal\Rest\ApiContext(
				new \PayPal\Auth\OAuthTokenCredential(
						$this->client_id,
						$this->client_secret
				)
		);
		$config = array(
				'log.LogEnabled' => false,
				'log.FileName' => UAP_PATH . 'paypal.log',
				'log.LogLevel' => 'INFO',
				'cache.enabled' => false,
		);
		if ($this->is_sandbox){
			$config['mode'] = 'sandbox';
		} else {
			$config['mode'] = 'live';
		}
		$this->apiContext->setConfig($config);
	}

	public function isAvailableAndActive()
	{
			if ( !$this->client_id || !$this->client_secret ){
			 		return false;
			}
			return true;
	}

	/**
	 * @param string, double, string
	 * @return none
	 */
	public function add_payment($email='', $amount=0, $currency='USD')
	{
		$new_sender = new \PayPal\Api\PayoutItem();
		$new_sender->setRecipientType('Email')
							->setNote('Affiliate Payment')
							->setReceiver($email)
							->setSenderItemId(uniqid())
							->setAmount(new \PayPal\Api\Currency('{
									"value":"' . $amount . '",
									"currency":"' . $currency . '"
							}'));
		$this->targetEmail = $email;
		$this->senders[] = $new_sender;
	}

	/**
	 * @param none
	 * @return string
	 */
	public function do_payout()
	{
		if ($this->senders && $this->apiContext){

			try {
				$payouts = new \PayPal\Api\Payout();
				$senderBatchHeader = new \PayPal\Api\PayoutSenderBatchHeader();
				$senderBatchHeader->setSenderBatchId(uniqid())
													->setEmailSubject("You have a payment");
				$payouts->setSenderBatchHeader($senderBatchHeader);
				foreach ($this->senders as $sender){
					$payouts->addItem($sender);
				}

				$output = $payouts->create(null, $this->apiContext);

				if (!empty($output) && !empty($output->batch_header) && !empty($output->batch_header->payout_batch_id)){
					return $output->batch_header->payout_batch_id;
				}
			} catch ( \Exception $e){
					$errorData = $e->getMessage();
					if ( $errorData ){
							$errorData = json_decode( $errorData, true );
					}
					if ( !empty( $errorData['error'] ) ){
							$this->setErrorDetails( $errorData['error'], $errorData['error_description'] );
					} else if ( isset( $errorData['message'] ) ) {
							$this->errorDetails = $errorData['message'];
					}
					return '';
			}
			return '';
		}
		return '';
	}

	/**
	* @param string
	* @return string
	*/
	public function get_status($payout_batch_id='')
	{
			$status = \PayPal\Api\Payout::get($payout_batch_id, $this->apiContext);
			if (!empty($status->items) && !empty($status->items[0]) && !empty($status->items[0]->transaction_status)){
				return $status->items[0]->transaction_status;
			}
			return '';
	}

	/**
	 * @param string
	 * @return none
	 */
	public function setErrorDetails( $errorCode='', $description='' )
	{
			if ( !$errorCode ){
					return;
			}
			switch ( $errorCode ){
					case 'ACCOUNT_RESTRICTED':
							$this->errorDetails = esc_html__( 'Access to your account has been restricted. Contact your account manager or our customer service team for assistance.', 'ihc' );
							break;
					case 'ACCOUNT_UNCONFIRMED_EMAIL':
							$this->errorDetails = esc_html__( 'You need to be a verified PayPal account holder to send payouts. You can verify your account by confirming your email and your bank account or credit card. Contact your account manager or our customer service team for assistance.', 'ihc' );
							break;
					case 'AUTHORIZATION_ERROR':
							$this->errorDetails = esc_html__( 'Your account is not authorized to use payouts. Contact your account manager or our customer service team for assistance.', 'ihc' );
							break;
					case 'BATCH_NOT_COMPLETED':
							$this->errorDetails = esc_html__( 'This payout batch is still being processed. Please try again later.', 'ihc' );
							break;
					case 'CLOSED_MARKET':
							$this->errorDetails = esc_html__( 'This account is not allowed to receive payouts from other countries. Try re-sending this payout to another account.', 'ihc' );
							break;
					case 'CURRENCY_COMPLIANCE':
							$this->errorDetails = esc_html__( 'Due to currency compliance regulations, you are not allowed to make this transaction.', 'ihc' );
							break;
					case 'CURRENCY_NOT_SUPPORTED_FOR_RECEIVER':
							$this->errorDetails = esc_html__( 'This currency cannot be accepted for this recipient’s account. You can re-send this payout with a different currency.', 'ihc' );
							break;
					case 'DUPLICATE_ITEM':
							$this->errorDetails = esc_html__( 'This transaction is duplicated in this batch. Please check the Ref_ID / Sender_Item_ID.', 'ihc' );
							break;
					case 'GAMER_FAILED_COUNTRY_OF_RESIDENCE_CHECK':
							$this->errorDetails = esc_html__( 'The recipient lives in a country that is not allowed to accept this payout.', 'ihc' );
							break;
					case 'GAMER_FAILED_FUNDING_SOURCE_CHECK':
							$this->errorDetails = esc_html__( 'The funding source that was selected for this payout is not allowed. Try again by using your PayPal balance instead.', 'ihc' );
							break;
					case 'GAMING_INVALID_PAYMENT_FLOW':
							$this->errorDetails = esc_html__( 'This payment flow is not allowed for gaming merchant accounts.', 'ihc' );
							break;
					case 'INSUFFICIENT_FUNDS':
							$this->errorDetails = esc_html__( "You have insufficient funds in your PayPal balance. You'll need to add funds to your account to complete the payout.", 'ihc' );
							break;
					case 'INTERNAL_ERROR':
							$this->errorDetails = esc_html__( 'An error occurred while processing this payout request. Please re-submit this payout as a new batch or file.', 'ihc' );
							break;
					case 'ITEM_ALREADY_CANCELLED':
							$this->errorDetails = esc_html__( 'This payout request has already been cancelled.', 'ihc' );
							break;
					case 'ITEM_CANCELLATION_FAILED':
							$this->errorDetails = esc_html__( 'An error occurred while processing this payout request. Try again in a few minutes.', 'ihc' );
							break;
					case 'ITEM_INCORRECT_STATUS':
							$this->errorDetails = esc_html__( 'You can only cancel items that are unclaimed.', 'ihc' );
							break;
					case 'MALFORMED_REQUEST_ERROR':
							$this->errorDetails = esc_html__( 'JSON request is malformed. Check your request format and try again.', 'ihc' );
							break;
					case 'NEGATIVE_BALANCE':
							$this->errorDetails = esc_html__( "You have insufficient funds in your PayPal balance. You'll need to add funds to your account to complete the payout.", 'ihc' );
							break;
					case 'NON_HOLDING_CURRENCY':
							$this->errorDetails = esc_html__( 'Your account does not have a PayPal balance in this currency. Try again with a currency that has funds in your PayPal account, or change your account settings to this currency.', 'ihc' );
							break;
					case 'PENDING_RECIPIENT_NON_HOLDING_CURRENCY_PAYMENT_PREFERENCE':
							$this->errorDetails = esc_html__( 'This payout is pending because the recipient has set their account preferences to review credits in this currency. The recipient has been notified. Check back later for the status of this payout.', 'ihc' );
							break;
					case 'POS_LIMIT_EXCEEDED':
							$this->errorDetails = esc_html__( 'You have exceeded the POS cumulative spending limit. Contact your account manager or our customer service team for assistance.', 'ihc' );
							break;
					case 'RATE_LIMIT_VALIDATION_FAILURES':
							$this->errorDetails = esc_html__( 'Your request has been blocked due to multiple failed attempts. Please try again later.', 'ihc' );
							break;
					case 'RECEIVER_ACCOUNT_LOCKED':
							$this->errorDetails = esc_html__( "We were not able to send a payout because the recipient’s account is inactive or restricted. Funds have been returned to your account.", 'ihc' );
							break;
					case 'RECEIVER_COUNTRY_NOT_ALLOWED':
							$this->errorDetails = esc_html__( "We can’t send this payout because the recipient lives in a country where payouts are not allowed.", 'ihc' );
							break;
					case 'RECEIVER_STATE_RESTRICTED':
							$this->errorDetails = esc_html__( "We can’t send this payout because the recipient lives in a state where payouts are not allowed.", 'ihc' );
							break;
					case 'RECEIVER_UNCONFIRMED':
							$this->errorDetails = esc_html__( "The recipient’s email or phone number is unconfirmed. Any payments made to this account will be marked as Unclaimed until the recipient confirms their account information. Funds will be returned to your account if they are not claimed within 30 days.", 'ihc' );
							break;
					case 'RECEIVER_UNREGISTERED':
							$this->errorDetails = esc_html__( 'The recipient for this payout does not have an account. A link to sign up for an account was sent to the recipient. However, if the recipient does not claim this payout within 30 days, the funds will be returned to your account.', 'ihc' );
							break;
					case 'RECEIVER_YOUTH_ACCOUNT':
							$this->errorDetails = esc_html__( 'We were not able to send a payout because the recipient has a youth account. Please check with the recipient for an alternate account to receive the payout.', 'ihc' );
							break;
					case 'RECEIVING_LIMIT_EXCEEDED':
							$this->errorDetails = esc_html__( 'The recipient cannot accept this payout, because it exceeds the amount they can receive at this time. Please resubmit your payout request for a different amount.', 'ihc' );
							break;
					case 'REFUSED_ACCESS_DENIED':
							$this->errorDetails = esc_html__( 'Your account is not allowed to send money. Check with your primary account holder to get permission to send money.', 'ihc' );
							break;
					case 'RECEIVER_REFUSED':
							$this->errorDetails = esc_html__( 'The recipient has refused this payout in this currency. Try resending in a different currency.', 'ihc' );
							break;
					case 'REGULATORY_BLOCKED':
							$this->errorDetails = esc_html__( 'This transaction is blocked due to regulatory compliance restrictions.', 'ihc' );
							break;
					case 'REGULATORY_PENDING':
							$this->errorDetails = esc_html__( 'This transaction is pending, while it is reviewed for compliance with government regulations.', 'ihc' );
							break;
					case 'REQUIRED_SCOPE_MISSING':
							$this->errorDetails = esc_html__( "The access token doesn't have the required scope. You'll need to use the access token with the correct scope to send a payout.", 'ihc' );
							break;
					case 'RISK_DECLINE':
							$this->errorDetails = esc_html__( 'This transaction was declined due to risk concerns.', 'ihc' );
							break;
					case 'SELF_PAY_NOT_ALLOWED':
							$this->errorDetails = esc_html__( "You can’t send a payout to yourself. Try sending it to a different account.", 'ihc' );
							break;
					case 'SENDER_ACCOUNT_LOCKED':
							$this->errorDetails = esc_html__( "You can’t send a payout now, because your account is locked or inactive. Contact your account manager or our customer service team for assistance.", 'ihc' );
							break;
					case 'SENDER_ACCOUNT_UNVERIFIED':
							$this->errorDetails = esc_html__( "To send a payout, you need to have a verified PayPal account. You can verify your account by confirming your bank account or credit card. Contact your account manager or our customer service team for assistance.", 'ihc' );
							break;
					case 'SENDER_STATE_RESTRICTED':
							$this->errorDetails = esc_html__( 'Your address is in a state where payouts are not allowed. Contact your account manager or our customer service team for assistance.', 'ihc' );
							break;
					case 'SPENDING_LIMIT_EXCEEDED':
							$this->errorDetails = esc_html__( "You’ve exceeded your spending limit. Contact your account manager or our customer service team for assistance.", 'ihc' );
							break;
					case 'TRANSACTION_DECLINED_BY_TRAVEL_RULE':
							$this->errorDetails = esc_html__( "Your payout request does not comply with travel rule regulations. To send this payout, you’ll need to update and verify your account information. Contact your account manager or our customer service team for assistance.", 'ihc' );
							break;
					case 'TRANSACTION_LIMIT_EXCEEDED':
							$this->errorDetails = esc_html__( 'This payout request has exceeded the limit for this type of transaction. The funds have been returned to your account.', 'ihc' );
							break;
					case 'UNDEFINED':
							$this->errorDetails = esc_html__( 'An error occurred while processing this payout request. Try again in a few minutes, or try resending as part of a new request or file.', 'ihc' );
							break;
					case 'UNVERIFIED_RECIPIENT_NOT_SUPPORTED':
							$this->errorDetails = esc_html__( 'This payout request was not completed because the recipient has not verified their account. Your account is only allowed to send payout to verified accounts.', 'ihc' );
							break;
					case 'USER_BUSINESS_ERROR':
							$this->errorDetails = esc_html__( 'An error occurred while processing this payout request. For batch processing, try again with a different sender_batch_ID. For single payout items, try again with email or payer ID as recipient type.', 'ihc' );
							break;
					case 'USER_COUNTRY_NOT_ALLOWED':
							$this->errorDetails = esc_html__( 'Your address is in a country where payouts are not allowed. Contact your account manager or our customer service team for assistance.', 'ihc' );
							break;
					case 'USER_FUNDING_SOURCE_INELIGIBLE':
							$this->errorDetails = esc_html__( 'The funding source for this payout is not allowed. Try again by using your PayPal balance instead.', 'ihc' );
							break;
					case 'ZERO_AMOUNT':
							$this->errorDetails = esc_html__( 'Please provide a valid payment amount.', 'ihc' );
							break;
					case 'APPROVER_DENIED':
							$this->errorDetails = esc_html__( 'Payout request rejected by the approver. Please check with your approver.', 'ihc' );
							break;
					case 'INVALID_EMAIL':
							$this->errorDetails = esc_html__( 'Email Address doesn’t exist. Try again with the correct Email Id.', 'ihc' );
							break;
					default:
					case 'invalid_client':
							$this->errorDetails = esc_html__('The Payment cannot be proceed for affiliate ', 'uap') . $this->targetEmail . '. PayPal error: ' . $description . " ($errorCode). " ;
							break;
			}
	}

	/**
	 * @param  none
	 * @return string
	 */
	public function getErrorDetails()
	{
			return $this->errorDetails;
	}


}
