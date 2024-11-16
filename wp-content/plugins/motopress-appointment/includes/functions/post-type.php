<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return MotoPress\Appointment\PostTypes\BookingPostType
 *
 * @since 1.2
 */
function mpa_booking() {
	return mpapp()->postTypes()->booking();
}

/**
 * @return MotoPress\Appointment\PostTypes\EmployeePostType
 *
 * @since 1.2
 */
function mpa_employee() {
	return mpapp()->postTypes()->employee();
}

/**
 * @return MotoPress\Appointment\PostTypes\LocationPostType
 *
 * @since 1.2
 */
function mpa_location() {
	return mpapp()->postTypes()->location();
}

/**
 * @return MotoPress\Appointment\PostTypes\ReservationPostType
 *
 * @since 1.2
 */
function mpa_reservation() {
	return mpapp()->postTypes()->reservation();
}

/**
 * @return MotoPress\Appointment\PostTypes\SchedulePostType
 *
 * @since 1.2
 */
function mpa_schedule() {
	return mpapp()->postTypes()->schedule();
}

/**
 * @return MotoPress\Appointment\PostTypes\ServicePostType
 *
 * @since 1.2
 */
function mpa_service() {
	return mpapp()->postTypes()->service();
}

/**
 * @return MotoPress\Appointment\PostTypes\ShortcodePostType
 *
 * @since 1.2
 */
function mpa_shortcode() {
	return mpapp()->postTypes()->shortcode();
}
