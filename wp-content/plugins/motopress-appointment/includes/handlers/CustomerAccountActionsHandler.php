<?php

namespace MotoPress\Appointment\Handlers;

/**
 * @since 1.18.0
 */
class CustomerAccountActionsHandler {

	public function __construct() {
		add_action( 'deleted_user', array( $this, 'deleteCustomerAccount' ), 10, 1 );
	}

	/**
	 * Listening to the WordPress user delete action.
	 * If a customer account exists for this user, then delete the customer account.
	 *
	 * @access protected
	 *
	 * @param int $userId
	 *
	 * @return void
	 */
	public function deleteCustomerAccount( $userId ) {
		$customer = mpapp()->repositories()->customer()->findByUserId( $userId );

		if ( ! $customer ) {
			return;
		}

		$customer->setUserId( 0 );
		try {
			mpapp()->repositories()->customer()->save( $customer );
		} catch ( \Exception $e ) {
		}
	}
}