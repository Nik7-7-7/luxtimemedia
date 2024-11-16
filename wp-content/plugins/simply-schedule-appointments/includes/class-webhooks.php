<?php
/**
 * Simply Schedule Appointments Webhooks.
 *
 * @since   1.9.3
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Webhooks.
 *
 * @since 1.9.3
 */
class SSA_Webhooks {
	/**
	 * Parent plugin class.
	 *
	 * @since 1.9.3
	 *
	 * @var   Simply_Schedule_Appointments
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @since  1.9.3
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
	 * @since  1.9.3
	 */
	public function hooks() {
		add_action( 'ssa/appointment/booked', array( $this, 'queue_appointment_booked_webhooks' ), 1000, 4 );
		add_action( 'ssa_fire_appointment_booked_webhooks', array( $this, 'fire_appointment_booked_webhooks' ), 10, 2 );

		add_action( 'ssa/appointment/edited', array( $this, 'queue_appointment_edited_webhooks' ), 1000, 4 );
		add_action( 'ssa_fire_appointment_edited_webhooks', array( $this, 'fire_appointment_edited_webhooks' ), 10, 2 );

		add_action( 'ssa/appointment/canceled', array( $this, 'queue_appointment_canceled_webhooks' ), 1000, 4 );
		add_action( 'ssa_fire_appointment_canceled_webhooks', array( $this, 'fire_appointment_canceled_webhooks' ), 10, 2 );
		
		add_action( 'ssa/appointment/rescheduled', array( $this, 'queue_appointment_rescheduled_webhooks' ), 1000, 4 );
		add_action( 'ssa_fire_appointment_rescheduled_webhooks', array( $this, 'fire_appointment_rescheduled_webhooks' ), 10, 2 );
	}

	public function should_fire_webhook( $action, $appointment_id, $webhook_settings ) {
		if ( empty( $webhook_settings['triggers'][0] ) ) {
			return false;
		}
		if ( ! in_array( $action, $webhook_settings['triggers'] ) ) {
			return false;
		}
		// It's a valid trigger, now let's query and validate the appointment type
		if ( empty( $webhook_settings['appointment_types'] ) ) {
			// Set to trigger on all appointment types
			return true;
		}

		// Let's check if the appointment type is one of the allowed ones
		$appointment_object = new SSA_Appointment_Object( $appointment_id );
		if ( in_array( $appointment_object->get_appointment_type()->id, $webhook_settings['appointment_types'] ) ) {
			return true;
		}
		
		// We've reached this in error, default to not sending the webhook
		return false;
	}

	public function fail_async_action( $async_action, $error_code = 500, $error_message = '', $context = array() ) {
		$response = array(
			'status_code' => $error_code,
			'error_message' => $error_message,
			'context' => $context,
		);

		ssa_complete_action( $async_action['id'], $response );
	}

	public function fire_webhook( $action, $payload, $webhook_settings ) {
			if ( empty( $webhook_settings['urls'] ) ) {
				return;
			}

			$payload['signature'] = array(
				'token' => $webhook_settings['token'],
				'site_name' => get_bloginfo( 'name' ),
				'home_url' => home_url(),
				'site_url' => site_url(),
				'network_site_url' => network_site_url(),
			);

			foreach ($webhook_settings['urls'] as $key => $url) {
				if ( $json_mode = true ) {
					// We're always going to use JSON for now
					$response = wp_remote_post( $url, array(
						'headers' => array(
							'Content-Type' => 'application/json; charset=utf-8',
						),
						'body' => json_encode( $payload ),
					) );
				} else {				
					// there's no setting for this yet, but in case we need to do Form data POST webhooks
					$response = wp_remote_post( $url, array(
						'body' => $payload,
					) );
				}

			}

		$response = array(
			'status_code' => wp_remote_retrieve_response_code( $response ),
			'body' => wp_remote_retrieve_body( $response ),
		);

		return $response;
	}


	public function queue_appointment_booked_webhooks( $appointment_id, $data, $data_before = array(), $response = null ) {
		$webhooks = $this->plugin->webhooks_settings->get_webhooks();
		if ( empty( $webhooks ) ) {
			return;
		}

		$appointment = new SSA_Appointment_Object( $appointment_id );
		$payload = $appointment->get_webhook_payload( 'appointment_booked' );

		// Add 3 seconds delay to webhooks date_queued to allow the web_meeting_url to return
		$date_queued_datetime = ssa_datetime()->add( new DateInterval( 'PT5S' ) );
		$date_queued_string = $date_queued_datetime->format( 'Y-m-d H:i:s' );
		$meta = array();
		$meta['date_queued'] = $date_queued_string;
		
		ssa_queue_action( 'appointment_booked', 'ssa_fire_appointment_booked_webhooks', 10, $payload, 'appointment', $appointment_id, 'webhooks', $meta );
	}

	public function fire_appointment_booked_webhooks( $payload, $async_action ) {
		$webhooks = $this->plugin->webhooks_settings->get_webhooks();
		$responses = array();
		if ( empty( $webhooks ) ) {
			$this->fail_async_action( $async_action, 500, 'No webhooks in settings', array( 'webhook_settings' => $webhook_settings ) );
			return;
		}

		$appointment_id = $payload['appointment']['id'];

		// Refresh payload
		$appointment = new SSA_Appointment_Object( $appointment_id );
		$payload = $appointment->get_webhook_payload( 'appointment_booked' );

		foreach ( $webhooks as $webhook_key => $webhook_settings ) {
			if ( ! $this->should_fire_webhook( 'appointment_booked', $appointment_id, $webhook_settings ) ) {
				$responses[] = array(
					'action' => 'appointment_booked',
					'skipped' => true,
					'webhook_settings' => $webhook_settings,
				);				
				continue;
			}

			$responses[] = array(
				'action' => 'appointment_booked',
				'webhook_settings' => $webhook_settings,
				'payload' => $payload,
				'response' => $this->fire_webhook( 'appointment_booked', $payload, $webhook_settings ),
			);

		}

		ssa_complete_action( $async_action['id'], $responses );
		return true;
	}










	public function queue_appointment_edited_webhooks( $appointment_id, $data_after, $data_before, $response ) {
		$webhooks = $this->plugin->webhooks_settings->get_webhooks();
		if ( empty( $webhooks ) ) {
			return;
		}
		
		$appointment = new SSA_Appointment_Object( $appointment_id );
		$payload = $appointment->get_webhook_payload( 'appointment_edited' );
		$payload['data_before'] = $appointment->format_webhook_payload( array( 'appointment' => $data_before ,) );
		
		ssa_queue_action( 'appointment_edited', 'ssa_fire_appointment_edited_webhooks', 10, $payload, 'appointment', $appointment_id, 'webhooks' );
	}
	public function fire_appointment_edited_webhooks( $payload, $async_action ) {
		$webhooks = $this->plugin->webhooks_settings->get_webhooks();
		$responses = array();
		if ( empty( $webhooks ) ) {
			$this->fail_async_action( $async_action, 500, 'No webhooks in settings', array( 'webhook_settings' => $webhook_settings ) );
			return;
		}

		$appointment_id = $payload['appointment']['id'];
		foreach ( $webhooks as $webhook_key => $webhook_settings ) {
			if ( ! $this->should_fire_webhook( 'appointment_edited', $appointment_id, $webhook_settings ) ) {
				$responses[] = array(
					'action' => 'appointment_edited',
					'skipped' => true,
					'webhook_settings' => $webhook_settings,
				);				
				continue;
			}

			$responses[] = array(
				'action' => 'appointment_edited',
				'webhook_settings' => $webhook_settings,
				'payload' => $payload,
				'response' => $this->fire_webhook( 'appointment_edited', $payload, $webhook_settings ),
			);

		}

		ssa_complete_action( $async_action['id'], $responses );
		return true;
	}

	public function queue_appointment_canceled_webhooks( $appointment_id, $data_after, $data_before, $response ) {
		$webhooks = $this->plugin->webhooks_settings->get_webhooks();
		if ( empty( $webhooks ) ) {
			return;
		}

		$appointment = new SSA_Appointment_Object( $appointment_id );
		$payload = $appointment->get_webhook_payload( 'appointment_canceled' );
		
		ssa_queue_action( 'appointment_canceled', 'ssa_fire_appointment_canceled_webhooks', 10, $payload, 'appointment', $appointment_id, 'webhooks' );
	}
	public function fire_appointment_canceled_webhooks( $payload, $async_action ) {
		$webhooks = $this->plugin->webhooks_settings->get_webhooks();
		$responses = array();
		if ( empty( $webhooks ) ) {
			$this->fail_async_action( $async_action, 500, 'No webhooks in settings', array( 'webhook_settings' => $webhook_settings ) );
			return;
		}

		$appointment_id = $payload['appointment']['id'];
		foreach ( $webhooks as $webhook_key => $webhook_settings ) {
			if ( ! $this->should_fire_webhook( 'appointment_canceled', $appointment_id, $webhook_settings ) ) {
				$responses[] = array(
					'action' => 'appointment_canceled',
					'skipped' => true,
					'webhook_settings' => $webhook_settings,
				);				
				continue;
			}

			$responses[] = array(
				'action' => 'appointment_canceled',
				'webhook_settings' => $webhook_settings,
				'payload' => $payload,
				'response' => $this->fire_webhook( 'appointment_canceled', $payload, $webhook_settings ),
			);

		}

		ssa_complete_action( $async_action['id'], $responses );
		return true;
	}

	public function queue_appointment_rescheduled_webhooks( $appointment_id, $data_after, $data_before, $response ) {
		$webhooks = $this->plugin->webhooks_settings->get_webhooks();
		if ( empty( $webhooks ) ) {
			return;
		}

		$appointment = new SSA_Appointment_Object( $appointment_id );
		$payload = $appointment->get_webhook_payload( 'appointment_rescheduled' );
		
		$payload['rescheduled_from_start_date'] = $data_before['start_date'];
		
		ssa_queue_action( 'appointment_rescheduled', 'ssa_fire_appointment_rescheduled_webhooks', 10, $payload, 'appointment', $appointment_id, 'webhooks' );
	}
	public function fire_appointment_rescheduled_webhooks( $payload, $async_action ) {
		$webhooks = $this->plugin->webhooks_settings->get_webhooks();
		$responses = array();
		if ( empty( $webhooks ) ) {
			$this->fail_async_action( $async_action, 500, 'No webhooks in settings', array( 'webhook_settings' => $webhook_settings ) );
			return;
		}

		$appointment_id = $payload['appointment']['id'];
		foreach ( $webhooks as $webhook_key => $webhook_settings ) {
			if ( ! $this->should_fire_webhook( 'appointment_rescheduled', $appointment_id, $webhook_settings ) ) {
				$responses[] = array(
					'action' => 'appointment_rescheduled',
					'skipped' => true,
					'webhook_settings' => $webhook_settings,
				);				
				continue;
			}

			$responses[] = array(
				'action' => 'appointment_rescheduled',
				'webhook_settings' => $webhook_settings,
				'payload' => $payload,
				'response' => $this->fire_webhook( 'appointment_rescheduled', $payload, $webhook_settings ),
			);

		}

		ssa_complete_action( $async_action['id'], $responses );
		return true;
	}

}
