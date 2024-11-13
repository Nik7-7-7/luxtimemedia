<?php
/**
 * Simply Schedule Appointments External Calendar Api.
 *
 * @since   4.1.2
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments External Calendar Api.
 *
 * @since 4.1.2
 */
abstract class SSA_External_Calendar_Api extends SSA_External_Api {
	abstract function pull_availability_calendar( $calendar_id, $args = array() );
}
