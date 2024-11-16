<?php
/**
 * Simply Schedule Appointments Sms.
 *
 * @since   2.6.7
 * @package Simply_Schedule_Appointments
 */

// Use the REST API Client to make requests to the Twilio REST API
use Twilio\Rest\Client;

/**
 * Simply Schedule Appointments Sms.
 *
 * @since 2.6.7
 */
class SSA_Sms {

	/**
	 * Parent plugin class.
	 *
	 * @since 2.6.7
	 *
	 * @var   Simply_Schedule_Appointments
	 */
	protected $plugin   = null;
	public $parent_slug = 'notifications';

	/**
	 * Constructor.
	 *
	 * @since  2.6.7
	 *
	 * @param  Simply_Schedule_Appointments $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();

	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  2.6.7
	 */
	public function hooks() {
		add_filter( 'ssa/templates/get_template_vars', array( $this, 'add_appointment_template_vars' ), 10, 2 );
	}

	public function add_appointment_template_vars( $vars, $template ) {
		if ( empty( $vars['appointment_id'] ) ) {
			return $vars;
		}

		$settings           = $this->plugin->settings->get();
		$appointment_object = new SSA_Appointment_Object( (int) $vars['appointment_id'] );

		$vars['admin_phone']    = $settings['global']['admin_phone'];
		$vars['customer_phone'] = $this->plugin->customer_information->get_phone_number_for_appointment( $appointment_object );

		return $vars;
	}

	public function has_sms_for_appointment_type_id( $appointment_type_id ) {
		// check if SMS is enabled
		if ( ! $this->plugin->settings_installed->is_enabled( 'sms' ) ) {
			return false;
		}
		
		// Confirm customer information fields include a phone number field
		$phone_number = $this->plugin->customer_information->get_phone_number_field_for_appointment_type_id( $appointment_type_id );
		if ( false === $phone_number ) {
			return false;
		}
		
		// Confirm appointment type has SMS triggered on the booked hook, sent to the customer
		$notifications = $this->plugin->notifications_settings->get_notifications();
		foreach ($notifications as $key => $notification) {
			// skip inactive notifications
			if ( isset( $notification['active'] ) && empty( $notification['active'] ) ) {
				continue;
			}
			
			if ( isset( $notification['type'] ) && 'sms' !== $notification['type'] ) {
				continue;
			}
			
			if ( ! empty( $notification['appointment_types'] ) && is_array( $notification['appointment_types'] ) && ! in_array( $appointment_type_id, $notification['appointment_types'] ) ) {
				continue;
			}
			
			if ( ! empty( $notification['sms_to'] ) && is_array( $notification['sms_to'] ) ) {
				$recipients_no_whitespace = array_map( function ( $recipient ) {
					// remove all white space
					return str_replace(' ', '', $recipient );
				} , $notification['sms_to'] );
				if( in_array( "{{customer_phone}}" , $recipients_no_whitespace ) ){
					return true;
				}
			}
		}
		
		return false;
	}

	public function format_number_for_twilio( $number ) {
		$number = str_replace( array( ' ', '(', ')' ), '', $number );
		return $number;
	}

	public function disconnect() {
		$settings = $this->plugin->sms_settings->reset_to_defaults();

		return $settings;
	}

	public function deauthorize() {
		$settings                    = $this->plugin->sms_settings->get();
		$settings['authorized_date'] = '';
		$settings                    = $this->plugin->sms_settings->update( $settings );

		return $settings;
	}

	public function authorize() {
		$settings = $this->plugin->sms_settings->get();
		if ( ! $this->is_test_mode() ) {
			$phone_numbers = $this->get_incoming_phone_numbers();
			if ( is_a( $phone_numbers, 'WP_Error' ) ) {
				return $phone_numbers;
			}
		}

		$settings['authorized_date'] = gmdate( 'Y-m-d H:i:s' );
		if ( empty( $settings['twilio_send_from_phone'] ) ) {
			// if ( ! empty( $phone_numbers['0']['number'] ) ) {
			// $settings['twilio_send_from_phone'] = $phone_numbers['0']['number'];
			// }
		}

		$settings = $this->plugin->sms_settings->update( $settings );

		return true;
	}

	public function get_twilio_credentials() {
		$twilio_credentials = array(
			'account_sid' => '',
			'auth_token'  => '',
		);

		$settings = $this->plugin->sms_settings->get();
		if ( $this->is_test_mode() ) {
			$twilio_credentials['account_sid'] = $settings['twilio_test_account_sid'];
			$twilio_credentials['auth_token']  = $settings['twilio_test_auth_token'];
		} else {
			$twilio_credentials['account_sid'] = $settings['twilio_account_sid'];
			$twilio_credentials['auth_token']  = $settings['twilio_auth_token'];
		}

		return $twilio_credentials;
	}

	public function get_incoming_phone_numbers() {
		$settings = $this->plugin->sms_settings->get();

		$twilio_credentials = $this->get_twilio_credentials();
		$twilio             = new Client( $twilio_credentials['account_sid'], $twilio_credentials['auth_token'] );

		try {
			$incomingPhoneNumbers = $twilio->incomingPhoneNumbers->read( array(), 20 );
		} catch ( Exception $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}

		$phone_numbers = array();
		foreach ( $incomingPhoneNumbers as $incomingPhoneNumber ) {
			if ( empty( $incomingPhoneNumber->capabilities['sms'] ) ) {
				continue;
			}

			$phone_numbers[] = array(
				'title'        => $incomingPhoneNumber->friendlyName,
				'number'       => $incomingPhoneNumber->phoneNumber,
				'capabilities' => $incomingPhoneNumber->capabilities,
			);
		}

		return $phone_numbers;
	}

	public function is_authorized() {
		$settings = $this->plugin->sms_settings->get();
		if ( ! empty( $settings['authorized_date'] ) ) {
			return true;
		}

		return false;
	}

	public function is_test_mode() {
		$settings = $this->plugin->sms_settings->get();
		if ( ! empty( $settings['twilio_test_mode_enabled'] ) ) {
			return true;
		}

		return false;
	}

	public function send_test( $to_number ) {
		$to_number = $this->format_number_for_twilio( $to_number );
		$settings  = $this->plugin->sms_settings->get();

		$twilio_credentials = $this->get_twilio_credentials();
		$twilio             = new Client( $twilio_credentials['account_sid'], $twilio_credentials['auth_token'] );
		$twilio->messages->create(
			$to_number,
			array(
				'from' => $settings['twilio_send_from_phone'],
				'body' => __( 'TEST: Simply Schedule Appointments is running on your site!', 'simply-schedule-appointments' ),
			)
		);

	}

	public function deliver_notification( $atts ) {
		$atts     = shortcode_atts(
			 array(
				 'to_number'          => '',
				 'notification'       => array(),
				 'notification_vars'  => array(),
				 'appointment_object' => null,
				 'subject'            => '',
				 'message'            => '',
			 ),
			$atts
			);
		$settings = $this->plugin->sms_settings->get();

		$to_number = $this->format_number_for_twilio( $atts['to_number'] );
		if ( empty( $to_number ) ) {
			return false;
		}

		$twilio_credentials = $this->get_twilio_credentials();
		$twilio             = new Client( $twilio_credentials['account_sid'], $twilio_credentials['auth_token'] );

		if ( $this->is_test_mode() ) {
			$from_number = '+15005550006';
		} else {
			$from_number = $settings['twilio_send_from_phone'];
			$from_number = $this->format_number_for_twilio( $from_number );
		}

		$body = $atts['message'];
		$body = str_replace(
			array( '</p><p>', '<br />', '&nbsp;', '&#038;' ),
			array( "</p>\n<p>", "\n", ' ', '&' ),
			$body
		);
		$body = str_replace( '<>', 'LTGT_ALLOWED_TAG', $body );
		$body = str_replace( '< ', 'LT_ALLOWED_TAG', $body );
		$body = str_replace( ' >', 'RT_ALLOWED_TAG', $body );
		$body = wp_strip_all_tags( $body );
		$body = str_replace( 'LTGT_ALLOWED_TAG', '<>', $body );
		$body = str_replace( 'LT_ALLOWED_TAG', '< ', $body );
		$body = str_replace( 'RT_ALLOWED_TAG', ' >', $body );
		$body = trim( $body );
		try {
			$twilio->messages->create(
				$to_number,
				array(
					'from' => $from_number,
					'body' => $body,
				)
			);
		} catch ( Exception $e ) {

		}
	}

	public function is_activated() {
		$settings = $this->plugin->sms_settings->get();

		if ( empty( $settings['twilio_send_from_phone'] ) ) {
			return false;
		}

		if ( $this->is_test_mode() ) {
			if ( empty( $settings['twilio_test_account_sid'] ) || empty( $settings['twilio_test_auth_token'] ) ) {
				return false;
			}
		} else {
			if ( empty( $settings['twilio_account_sid'] ) || empty( $settings['twilio_auth_token'] ) ) {
				return false;
			}
		}
		return true;
		
	}
}
