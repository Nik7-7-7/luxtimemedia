<?php
/**
 * Simply Schedule Appointments Google Calendar Logger.
 *
 * @since   0.6.0
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Google Calendar Logger.
 *
 * @since 0.6.0
 */
class SSA_Google_Calendar_Logger extends Google_Logger_Abstract {

	public function __construct( $client ) {
		parent::__construct( $client );

		$this->dateFormat = 'Y-m-d H:i:s';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function write( $message ) {
		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			error_log( '[SSA] ' . $message );
		}
	}

}