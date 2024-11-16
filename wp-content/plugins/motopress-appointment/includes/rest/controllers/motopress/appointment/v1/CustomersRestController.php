<?php

namespace MotoPress\Appointment\REST\Controllers\Motopress\Appointment\V1;

use MotoPress\Appointment\Entities\Customer;
use MotoPress\Appointment\Handlers\SecurityHandler;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.18.0
 */
class CustomersRestController extends AbstractRestController {

	public function register_routes() {

		// '/motopress/appointment/v1/customers/create'
		register_rest_route(
			$this->getNamespace(),
			'/customers/create',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'createCustomerAccount' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'name'  => array(
						'type' => 'string',
					),
					'email' => array(
						'type'   => 'string',
						'format' => 'email',
					),
					'phone' => array(
						'type' => 'string',
					),
				),
			)
		);
	}

	protected function createCustomerAccountUserData( $email, $username = '', $password = '', $args = array() ) {

		if ( empty( $email ) || ! is_email( $email ) ) {
			return new \WP_Error( 'invalid-email', __( 'Please provide a valid email address.', 'motopress-appointment' ) );
		}

		if ( email_exists( $email ) ) {
			// Get existing customer by email
			$user = get_user_by( 'email', $email );

			return $user->ID;
		}

		if ( empty( $username ) ) {
			$username = $this->createUsername( $email, $args );
			$username = sanitize_user( $username );
		}

		if ( empty( $username ) || ! validate_username( $username ) ) {
			return new \WP_Error( 'invalid-username', __( 'Please enter a valid account username.', 'motopress-appointment' ) );
		}

		if ( username_exists( $username ) ) {
			return new \WP_Error( 'username-exists', __( 'An account with this username already exists. Please choose another.', 'motopress-appointment' ) );
		}

		if ( empty( $password ) ) {
			$password = wp_generate_password();
		}

		if ( empty( $password ) ) {
			return new \WP_Error( 'missing-password', __( 'Please enter an account password.', 'motopress-appointment' ) );
		}

		return apply_filters(
			'mpa_new_customer_account_data',
			array_merge(
				$args,
				array(
					'user_login' => $username,
					'user_pass'  => $password,
					'user_email' => $email,
					'role'       => SecurityHandler::ROLE_APPOINTMENT_CUSTOMER,
				)
			)
		);
	}

	protected function createUsername( $email, $args = array(), $suffix = '' ) {
		$usernameParts = array();

		if ( isset( $args['first_name'] ) ) {
			$usernameParts[] = sanitize_user( $args['first_name'], true );
		}

		if ( isset( $args['last_name'] ) ) {
			$usernameParts[] = sanitize_user( $args['last_name'], true );
		}

		$usernameParts = array_filter( $usernameParts );

		if ( empty( $usernameParts ) ) {
			$emailParts    = explode( '@', $email );
			$emailUsername = $emailParts[0];

			if ( in_array(
				$emailUsername,
				array(
					'sales',
					'hello',
					'mail',
					'contact',
					'info',
				),
				true
			) ) {
				$emailUsername = $emailParts[1];
			}

			$usernameParts[] = sanitize_user( $emailUsername, true );
		}

		$username = strtolower( implode( '.', $usernameParts ) );

		if ( $suffix ) {
			$username .= $suffix;
		}

		$illegalLogins = (array) apply_filters( 'illegal_user_logins', array() );

		if ( in_array( strtolower( $username ), array_map( 'strtolower', $illegalLogins ), true ) ) {
			$newArgs = array();

			$newArgs['first_name'] = apply_filters(
				'mpa_generated_customer_username',
				'motopress_user_' . zeroise( wp_rand( 0, 9999 ), 4 ),
				$email,
				$args,
				$suffix
			);

			return $this->createUsername( $email, $newArgs, $suffix );
		}

		if ( username_exists( $username ) ) {
			$suffix = '-' . zeroise( wp_rand( 0, 9999 ), 4 );

			return $this->createUsername( $email, $args, $suffix );
		}

		return apply_filters( 'mpa_new_customer_username', $username, $email, $args, $suffix );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function createCustomerAccount( $request ) {
		$failMessage = esc_html__( 'An error has occurred. The customer account has not been created.', 'motopress-appointment' );

		$customerDetails = $request->get_params();

		if ( ! mpapp()->settings()->isAllowCustomerAccountCreation() ) {
			$failMessage = esc_html__( 'It is forbidden to create new accounts.', 'motopress-appointment' );

			return mpa_rest_failure_error( $failMessage );
		}

		if ( ! isset( $customerDetails['email'] ) || ! is_email( $customerDetails['email'] ) ) {
			$failMessage = esc_html__( 'Please provide a valid email address.', 'motopress-appointment' );

			return mpa_rest_failure_error( $failMessage );
		}

		$customer = mpapp()->repositories()->customer()->findByEmail( $customerDetails['email'] );

		if ( $customer && $customer->getUserId() ) {
			// Skip account creation for automatic account creation mode because the account was found.
			// this is necessary to prevent errors on the front-end.
			if ( mpapp()->settings()->isCustomerAccountCreateAutomatically() ) {
				return rest_ensure_response( array() );
			}

			$failMessage = sprintf(
				'%s %s',
				esc_html__( 'This email address is already registered.', 'motopress-appointment' ),
				esc_html__( 'Please choose another one.', 'motopress-appointment' )
			);

			return mpa_rest_failure_error( $failMessage );
		}

		if ( ! $customer && $customerDetails['phone'] ) {
			$customer = mpapp()->repositories()->customer()->findByPhone( $customerDetails['phone'] );
		}

		if ( ( $customer && $customer->getUserId() ) ||
		     ( $customer && ( $customer->getEmail() != $customerDetails['email'] ) )
		) {
			// Skip account creation for automatic account creation mode because the account was found.
			// this is necessary to prevent errors on the front-end.
			if ( mpapp()->settings()->isCustomerAccountCreateAutomatically() ) {
				return rest_ensure_response( array() );
			}

			$failMessage = sprintf(
				'%s %s',
				esc_html__( 'This phone is already registered.', 'motopress-appointment' ),
				esc_html__( 'Please choose another one.', 'motopress-appointment' )
			);

			return mpa_rest_failure_error( $failMessage );
		}

		if ( is_null( $customer ) ) {
			$customerData = array(
				'name'  => $customerDetails['name'],
				'email' => $customerDetails['email'],
				'phone' => $customerDetails['phone'],
			);

			try {
				$customer = mpapp()->repositories()->customer()->mapDataToEntity( $customerData );
			} catch ( \Exception $e ) {
				return mpa_rest_failure_error( $failMessage );
			}
		}

		if ( ! $customer ) {
			return mpa_rest_failure_error( $failMessage );
		}

		// If a WordPress user already exists with this email, assign the existing user to the customer.
		if ( email_exists( $customer->getEmail() ) ) {
			$userId = email_exists( $customer->getEmail() );
			$customer->setUserId( $userId );

			try {
				mpapp()->repositories()->customer()->save( $customer );
			} catch ( \Exception $e ) {
				return mpa_rest_failure_error( $failMessage );
			}

			return rest_ensure_response( array() );
		}

		$userdata = $this->createCustomerAccountUserData( $customer->getEmail() );
		$userId   = wp_insert_user( $userdata );

		if ( is_wp_error( $userId ) ) {
			return mpa_rest_failure_error( $failMessage );

		}

		$customer->setUserId( $userId );

		try {
			$saved = mpapp()->repositories()->customer()->save( $customer );
		} catch ( \Exception $e ) {
			$saved = false;
		}

		if ( ! $saved ) {
			return mpa_rest_failure_error( $failMessage );
		}

		/**
		 * @param $customer Customer
		 * @param $userdata array An array of user data about the created customer account.
		 */
		do_action( 'mpa_new_customer_account', $customer, $userdata );

		return rest_ensure_response( array() );
	}
}