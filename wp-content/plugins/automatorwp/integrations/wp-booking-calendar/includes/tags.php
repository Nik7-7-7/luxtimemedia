<?php

/**
 * Tags
 *
 * @package     AutomatorWP\Integrations\BookingCalendar\Tags
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Booking tags
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_wp_booking_calendar_get_booking_tags() {

    return array(
        'booking_id' => array(
            'label'     => __( 'Booking ID', 'automatorwp' ),
            'type'      => 'text',
            'preview'   => 'The booking ID',
        ),
        'name' => array(
            'label'     => __( 'Customer name', 'automatorwp' ),
            'type'      => 'text',
            'preview'   => 'The customer name',
        ),
        'lastname' => array(
            'label'     => __( 'Customer last name', 'automatorwp' ),
            'type'      => 'text',
            'preview'   => 'The customer last name',
        ),
        'email' => array(
            'label'     => __( 'Customer email', 'automatorwp' ),
            'type'      => 'text',
            'preview'   => 'The customer email',
        ),
        'phone' => array(
            'label'     => __( 'Customer phone number', 'automatorwp' ),
            'type'      => 'text',
            'preview'   => 'The customer phone number',
        ),
        'dates' => array(
            'label'     => __( 'Booking date(s)', 'automatorwp' ),
            'type'      => 'text',
            'preview'   => 'The booking date(s)',
        ),
        'details' => array(
            'label'     => __( 'Booking details', 'automatorwp' ),
            'type'      => 'text',
            'preview'   => 'The booking details',
        ),
        'cost' => array(
            'label'     => __( 'Booking cost', 'automatorwp' ),
            'type'      => 'text',
            'preview'   => 'The booking cost',
        ),
    );

}

/**
 * Custom trigger tag replacement
 *
 * @since 1.0.0
 *
 * @param string    $replacement    The tag replacement
 * @param string    $tag_name       The tag name (without "{}")
 * @param stdClass  $trigger        The trigger object
 * @param int       $user_id        The user ID
 * @param string    $content        The content to parse
 * @param stdClass  $log            The last trigger log object
 *
 * @return string
 */
function automatorwp_wp_booking_calendar_get_trigger_booking_tag_replacement( $replacement, $tag_name, $trigger, $user_id, $content, $log ) {

    $trigger_args = automatorwp_get_trigger( $trigger->type );

    // Skip if trigger is not from this integration
    if( $trigger_args['integration'] !== 'wp_booking_calendar' ) {
        return $replacement;
    }

    $booking_id = (int) automatorwp_get_log_meta( $log->id, 'booking_id', true );   

    // Get booking details using wpbc_db_get_booking_details function
    $booking_details = wpbc_db_get_booking_details( $booking_id );

    // If booking details not found, return the original replacement
    if ( ! $booking_details ) {
        return $replacement;
    }

    // Extracts the form data from the bookings' details
    $formdata = $booking_details->form;

    // Creates an array containing all the bookings' params
    $booking_data = wpbc_get_booking_different_params_arr( $booking_id, $formdata );

    // Assign values to tags based on the tag name
    switch( $tag_name ) {
        case 'booking_id':
            $replacement = $booking_data['id'];
            break;
        case 'name':
            $replacement = $booking_data['name'];
            break;
        case 'lastname':
            $replacement = $booking_data['secondname'];
            break;
        case 'email':
            $replacement = $booking_data['email'];
            break;     
        case 'phone':
            $replacement = $booking_data['phone'];
            break;   
        case 'dates':
            $replacement = $booking_data['dates'];
            break;   
        case 'details':
            $replacement = $booking_data['details'];
            break;   
        case 'cost':
            $replacement = $booking_data['db_cost'];
            break;   
    }

    return $replacement;

}
add_filter( 'automatorwp_get_trigger_tag_replacement', 'automatorwp_wp_booking_calendar_get_trigger_booking_tag_replacement', 10, 6 );