<?php

/**
 * Functions
 *
 * @package     AutomatorWP\BookingCalendar\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * Options callback for select2 fields assigned to bookings
 *
 * @since 1.0.0
 *
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_wp_booking_calendar_options_cb_booking( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any booking', 'automatorwp' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach( $value as $booking_id ) {

            // Skip option none
            if( $booking_id === $none_value ) {
                continue;
            }

            $options[$booking_id] = $booking_id;
        }
    }

    return $options;

}