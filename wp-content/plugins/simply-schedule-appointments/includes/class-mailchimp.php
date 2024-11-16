<?php
/**
 * Simply Schedule Appointments Mailchimp.
 *
 * @since   1.0.1
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Mailchimp.
 *
 * @since 1.0.1
 */
class SSA_Mailchimp {
	/**
	 * Parent plugin class.
	 *
	 * @since 1.0.1
	 *
	 * @var   Simply_Schedule_Appointments
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @since  1.0.1
	 *
	 * @param  Simply_Schedule_Appointments $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		include $this->plugin->dir( 'includes/lib/mailchimp-api/src/MailChimp.php' );
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  1.0.1
	 */
	public function hooks() {
		add_action( 'ssa/appointment/after_insert', array( $this, 'sync_appointment_to_mailchimp' ), 100, 2 );
		add_action( 'ssa/appointment/after_update', array( $this, 'sync_appointment_to_mailchimp' ), 100, 2 );
	}

	public function sync_appointment_to_mailchimp( $appointment_id, $data ) {
		try {		
			if ( empty( $data['mailchimp_list_id'] ) ) {
				return false;
			}
			
			if ( strlen( (string) $data['mailchimp_list_id'] ) > 5 ) {
				// this is a stored mailchimp list id, we don't want to sync it again
				return false;
			}

			$appointment = new SSA_Appointment_Object( $appointment_id );
			if ( empty( $appointment->data['customer_information']['Email'] ) ) {
				return false;
			}

			$appointment_type = $appointment->get_appointment_type();
			if ( empty( $appointment_type->data['mailchimp']['optin_list'] ) ) {
				return false;
			}

			$this->add_update_subscriber_to_list( array(
				'email_address' => $appointment->data['customer_information']['Email'],
				'name' => $appointment->data['customer_information']['Name'],
				'status' => 'subscribed',
				'customer_information' => $appointment->data['customer_information'],
				'appointment_type_id' => $appointment_type->id,
			), $appointment_type->data['mailchimp']['optin_list'] );

			$appointment_update_data = array(
				'mailchimp_list_id' => $appointment_type->data['mailchimp']['optin_list'],
			);
			$response = $this->plugin->appointment_model->update( $appointment_id, $appointment_update_data );

			return true;
		} catch ( Exception $e ) {
			return false;
		}
	}

	public function get_api_key() {
		$settings = $this->plugin->mailchimp_settings->get();
		if ( empty( $settings['api_key'] ) ) {
			return new WP_Error( 'ssa_mailchimp_no_api_key', __( 'You need to enter your MailChimp API key in the Simply Schedule Appointments settings', 'simply-schedule-appointments' ) );
		}

		return trim( $settings['api_key'] );
	}

	public function mailchimp_api( $command ) {
		$api_key = $this->get_api_key();
		if ( is_a( $api_key, 'WP_Error' ) ) {
			return $api_key;
		}

		$MailChimp = new \SSADrewM\MailChimp\MailChimp( $api_key );
		try {		
			$result = $MailChimp->get( $command );
			if ( empty( $result ) ) {
				return new WP_Error( 500, __( 'Invalid API response from MailChimp', 'simply-schedule-appointments' ) );
			}
			if ( !empty( $result['status'] ) && $result['status'] == '401' ) {
				return new WP_Error( $result['status'], $result['title'].' - '.$result['detail'], $result );
			}
		} catch (Exception $e) {
			return new WP_Error( 500, __( 'Invalid API response from MailChimp', 'simply-schedule-appointments' ) );
		}

		return $result;
	}

	public function get_mailchimp_api() {
		$api_key = $this->get_api_key();
		if ( is_a( $api_key, 'WP_Error' ) ) {
			return $api_key;
		}

		$MailChimp = new \SSADrewM\MailChimp\MailChimp( $api_key );
		return $MailChimp;
	}

	public function mailchimp_request( $http_verb, $method, $args = array(), $timeout = 10 ) {
		$MailChimp = $this->get_mailchimp_api();
		$result = $MailChimp->$http_verb( $method, $args, $timeout );
		return $result;
	}


	public function is_authorized() {
		$settings = $this->plugin->mailchimp_settings->get();
		if ( !empty( $settings['account_details'] ) ) {
			return true;
		}

		return false;
	}

	public function authorize() {
		try {
			$account_details = $this->fetch_account_details();

			if ( is_a( $account_details, 'WP_Error' ) ) {
				$error = $account_details;
				$error_code = $error->get_error_code();
				if ( 400 <= $error_code && $error_code < 500  ) {
					$this->deauthorize();
				}

				return $error;
			}
		} catch ( Exception $e ) {
			$error = new WP_Error( 'invalid', $e->getMessage() );
			return $error;
		}


		$settings = $this->plugin->mailchimp_settings->get();
		return $settings;
	}

	public function deauthorize() {
		$settings = $this->plugin->mailchimp_settings->reset_to_defaults();

		return $settings;
	}

	public function get_list_by_id( $list_id ) {
		$settings = $this->plugin->mailchimp_settings->get();
		if ( empty( $settings['lists'] ) ) {
			throw new SSA_Mailchimp_Exception( __( 'No MailChimp lists found', 'simply-schedule-appointments' ), 'ssa_mailchimp_no_lists' );
		}

		foreach ($settings['lists'] as $key => $list) {
			if ( $list['id'] == $list_id ) {
				return $list;
			}
		}

		throw new SSA_Mailchimp_Exception( __( 'MailChimp list not found', 'simply-schedule-appointments' ), 'ssa_mailchimp_list_not_found' );
	}

	public function add_update_subscriber_to_list( $subscriber_data, $list_id ) {
		if ( ! $this->is_authorized() ) {
			return false;
		}

		$list = $this->get_list_by_id( $list_id );
		$subscriber = $this->get_subscriber_from_list( $subscriber_data, $list_id );

		// Add new users
		if ( empty( $subscriber['id'] ) ) {
			$subscriber = $this->add_subscriber_to_list( $subscriber_data, $list_id );
			return $subscriber;
		}

		// Don't touch unsubscribed users
		if ( !empty( $subscriber['status'] ) ) {
			if ( $subscriber['status'] == 'unsubscribed' ) {
				throw new SSA_Mailchimp_Exception( __( 'The user is marked as unsubscribed in MailChimp', 'simply-schedule-appointments' ), 'ssa_mailchimp_user_is_unsubscribed' );
			}
		}

		$subscriber = $this->update_subscriber_on_list( $subscriber_data, $list_id );

		return $subscriber;
	}

	public function get_subscriber_from_list( $subscriber, $list_id ) {
		$list = $this->get_list_by_id( $list_id );

		$request = '/lists/' . $list['id'] . '/members/' . md5( $subscriber['email_address'] );
		$response = $this->mailchimp_request( 'get', $request );
		if ( !empty( $response['id'] ) && $response['id'] == md5( $subscriber['email_address'] ) ) {
			return $response;
		}

		return false;
	}

	public function prepare_subscriber_data_for_api_request( $subscriber_data ) {
		if ( empty( $subscriber_data['name'] ) ) {
			return $subscriber_data;
		}

		$customer_information = array();
		if ( ! empty( $subscriber_data['customer_information'] ) ) {
			$customer_information = $subscriber_data['customer_information'];
			unset( $subscriber_data['customer_information'] );
		}

		$appointment_type_id = null;
		if ( ! empty( $subscriber_data['appointment_type_id'] ) ) {
			$appointment_type_id = $subscriber_data['appointment_type_id'];
			unset( $subscriber_data['appointment_type_id'] );
		}

		$subscriber_data['merge_fields']['FNAME'] = $subscriber_data['name'];
		$subscriber_data['merge_fields']['NAME'] = $subscriber_data['name'];
		unset( $subscriber_data['name'] );

		$subscriber_data = apply_filters( 'ssa_mailchimp_subscriber_data', $subscriber_data, $customer_information, $appointment_type_id );

		return $subscriber_data;
	}

	public function add_subscriber_to_list( $subscriber_data, $list_id ) {
		$list = $this->get_list_by_id( $list_id );
		$subscriber_data = $this->prepare_subscriber_data_for_api_request( $subscriber_data );

		$request = '/lists/' . $list['id'] . '/members/';
		$response = $this->mailchimp_request( 'post', $request, $subscriber_data );

		return $response;
	}

	public function update_subscriber_on_list( $subscriber_data, $list_id ) {
		$list = $this->get_list_by_id( $list_id );
		$subscriber_data = $this->prepare_subscriber_data_for_api_request( $subscriber_data );

		$request = '/lists/' . $list['id'] . '/members/' . md5( $subscriber_data['email_address'] );
		$response = $this->mailchimp_request( 'patch', $request, $subscriber_data );

		return $response;
	}

	public function fetch_account_details() {
		$settings = $this->plugin->mailchimp_settings->get();
		$response = $this->mailchimp_api( '/' );

		if ( is_a( $response, 'WP_Error' ) ) {
			return $response;
		}

		unset( $response['_links'] );

		$settings['account_details'] = $response;
		$this->plugin->mailchimp_settings->update( $settings );

		return $response;
	}

	public function fetch_lists() {
		$settings = $this->plugin->mailchimp_settings->get();
		$lists = $this->mailchimp_api( '/lists' );
		if ( is_a( $lists, 'WP_Error' ) ) {
			return $lists;
		}

		if ( !empty( $lists['lists']['0']['_links'] ) ) {
			foreach ($lists['lists'] as $list_key => $list) {
				unset( $lists['lists'][$list_key]['_links'] );
			}
		}
		unset( $lists['_links'] );

		foreach ($lists['lists'] as $list_key => $list) {
			$merge_fields = $this->mailchimp_api( '/lists/'.$list['id'].'/merge-fields' );
			if ( is_a( $merge_fields, 'WP_Error' ) ) {
				continue;
			}

			foreach ($merge_fields['merge_fields'] as $merge_field_key => $merge_field) {
				unset( $merge_fields['merge_fields'][$merge_field_key]['_links'] );
			}
			$lists['lists'][$list_key]['merge_fields'] = $merge_fields['merge_fields'];
		}

		$settings['lists'] = $lists['lists'];
		foreach ($settings['lists'] as $settings_list_key => $settings_list) {
			unset( $settings['lists'][$settings_list_key]['contact'] );
			unset( $settings['lists'][$settings_list_key]['stats'] );
			unset( $settings['lists'][$settings_list_key]['permission_reminder'] );
			unset( $settings['lists'][$settings_list_key]['beamer_address'] );
		}

		$this->plugin->mailchimp_settings->update( $settings );

		return $lists;
	}

	public function debug() {
		$this->authorize();
		echo '<pre>'.print_r($this->plugin->mailchimp_settings->get(), true).'</pre>'; // phpcs:ignore
	}
}
